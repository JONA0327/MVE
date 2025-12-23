<?php

namespace App\Services\Vucem;

use SoapClient;
use SoapHeader;
use Exception;
use SoapFault;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ConsultarCoveService
{
    private $soapClient;
    private $endpoint;
    private $soapAction;
    private $username;
    private $password;

    public function __construct()
    {
        $this->endpoint = config('vucem.consultar_cove.endpoint', 'http://www.ventanillaunica.gob.mx/ConsultarRespuestaCoveService');
        $this->soapAction = config('vucem.consultar_cove.soap_action', 'http://www.ventanillaunica.gob.mx/ConsultarRespuestaCove');
        
        // Obtener credenciales del usuario autenticado
        $user = Auth::user();
        if ($user) {
            $this->username = $user->rfc; // Se desencripta automáticamente por el modelo
            $this->password = $user->webservice_key; // Se desencripta automáticamente por el modelo
        } else {
            $this->username = null;
            $this->password = null;
        }
        
        $this->initializeSoapClient();
    }

    /**
     * Inicializar el cliente SOAP con WSDL local
     */
    private function initializeSoapClient(): void
    {
        try {
            $wsdlPath = base_path('wsdl/vucem/ConsultarRespuestaCove.wsdl');
            
            if (!file_exists($wsdlPath)) {
                throw new Exception("WSDL no encontrado en: {$wsdlPath}");
            }

            $options = [
                'location' => $this->endpoint,
                'soap_version' => \SOAP_1_1,
                'exceptions' => true,
                'trace' => true,
                'cache_wsdl' => \WSDL_CACHE_NONE, // Para desarrollo, cambiar a WSDL_CACHE_BOTH en producción
                'connection_timeout' => 30,
                'user_agent' => 'MVE-Laravel-SOAP-Client',
            ];

            $this->soapClient = new SoapClient($wsdlPath, $options);
            
        } catch (Exception $e) {
            Log::error('Error inicializando SOAP client ConsultarCove: ' . $e->getMessage());
            throw new Exception('Error de configuración del servicio VUCEM');
        }
    }

    /**
     * Consultar información de un COVE por su folio
     */
    public function consultarCove(string $folioCove): array
    {
        try {
            // Validar que el usuario esté autenticado
            if (!Auth::check()) {
                return [
                    'success' => false,
                    'message' => 'Usuario no autenticado. Inicie sesión para consultar COVEs.',
                    'error_type' => 'auth_error'
                ];
            }

            // Validar credenciales del perfil del usuario
            if (empty($this->username) || empty($this->password)) {
                $missing = [];
                if (empty($this->username)) $missing[] = 'RFC del solicitante';
                if (empty($this->password)) $missing[] = 'clave de webservice VUCEM';
                
                return [
                    'success' => false,
                    'message' => 'Complete su perfil: faltan datos requeridos (' . implode(', ', $missing) . '). Vaya a Mi Perfil para configurarlos.',
                    'error_type' => 'profile_incomplete'
                ];
            }

            // Validar folio
            if (empty(trim($folioCove))) {
                return [
                    'success' => false,
                    'message' => 'El folio de COVE es requerido',
                    'error_type' => 'validation_error'
                ];
            }

            // Configurar WS-Security header
            $this->setSecurityHeader();

            // Preparar la petición SOAP
            $requestData = $this->prepareRequest($folioCove);

            Log::info('Consultando COVE: ' . $folioCove, [
                'endpoint' => $this->endpoint,
                'username' => $this->username,
                'user_id' => Auth::id()
            ]);

            // Realizar la llamada SOAP
            $response = $this->soapClient->__soapCall('ConsultarRespuestaCove', [$requestData], [
                'soapaction' => $this->soapAction
            ]);

            // Procesar la respuesta
            return $this->processResponse($response, $folioCove);

        } catch (SoapFault $e) {
            Log::error('SOAP Fault al consultar COVE: ' . $e->getMessage(), [
                'folio_cove' => $folioCove,
                'fault_code' => $e->faultcode,
                'fault_string' => $e->faultstring
            ]);

            return [
                'success' => false,
                'message' => $this->interpretSoapFault($e),
                'error_type' => 'soap_fault',
                'details' => [
                    'fault_code' => $e->faultcode,
                    'fault_string' => $e->faultstring
                ]
            ];

        } catch (Exception $e) {
            Log::error('Error general al consultar COVE: ' . $e->getMessage(), [
                'folio_cove' => $folioCove,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error de comunicación con el servicio VUCEM. Intente más tarde.',
                'error_type' => 'network_error'
            ];
        }
    }

    /**
     * Configurar el header de seguridad WS-Security
     */
    private function setSecurityHeader(): void
    {
        $namespace = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
        
        $usernameToken = [
            'Username' => $this->username,
            'Password' => [
                '_' => $this->password,
                'Type' => 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText'
            ]
        ];

        $security = [
            'UsernameToken' => $usernameToken
        ];

        $header = new SoapHeader($namespace, 'Security', $security, true);
        $this->soapClient->__setSoapHeaders($header);
    }

    /**
     * Preparar la estructura de la petición según el XSD
     */
    private function prepareRequest(string $folioCove): array
    {
        // Nota: Según el XSD, se requiere numeroOperacion y firmaElectronica
        // Pero para consultas, normalmente se usa el folio como numeroOperacion
        
        return [
            'numeroOperacion' => intval($folioCove), // Asumiendo que el folio es numérico
            'firmaElectronica' => [
                'certificado' => '', // Se puede dejar vacío para consultas simples
                'cadenaOriginal' => '',
                'firma' => ''
            ]
        ];
    }

    /**
     * Procesar la respuesta del servicio SOAP
     */
    private function processResponse($response, string $folioCove): array
    {
        try {
            if (empty($response)) {
                return [
                    'success' => false,
                    'message' => 'Respuesta vacía del servicio VUCEM',
                    'error_type' => 'empty_response'
                ];
            }

            // Acceder a la estructura de respuesta según el XSD
            $respuestaPeticion = $response;
            
            if (isset($respuestaPeticion->respuestasOperaciones)) {
                $operacion = $respuestaPeticion->respuestasOperaciones;
                
                // Verificar si contiene error
                if (isset($operacion->contieneError) && $operacion->contieneError) {
                    $errorMessages = [];
                    if (isset($operacion->errores) && isset($operacion->errores->mensaje)) {
                        $mensajes = is_array($operacion->errores->mensaje) ? 
                            $operacion->errores->mensaje : [$operacion->errores->mensaje];
                        $errorMessages = array_map('strval', $mensajes);
                    }
                    
                    return [
                        'success' => false,
                        'message' => 'COVE no encontrado o no autorizado: ' . implode(', ', $errorMessages),
                        'error_type' => 'cove_not_found',
                        'vucem_errors' => $errorMessages
                    ];
                }

                // Si llegamos aquí, la consulta fue exitosa
                return [
                    'success' => true,
                    'data' => [
                        'cove' => $folioCove,
                        'numero_factura' => $operacion->numeroFacturaORelacionFacturas ?? '',
                        'edocument' => $operacion->eDocument ?? '',
                        'cadena_original' => $operacion->cadenaOriginal ?? '',
                        'sello_digital' => $operacion->selloDigital ?? '',
                        // Datos adicionales que podemos derivar o configurar
                        'metodo_valoracion' => $this->determineMetodoValoracion($operacion),
                        'fecha_expedicion' => $this->extractFechaExpedicion($response),
                        'emisor' => $this->extractEmisor($operacion)
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => 'Formato de respuesta inesperado del servicio VUCEM',
                'error_type' => 'invalid_response_format'
            ];

        } catch (Exception $e) {
            Log::error('Error procesando respuesta VUCEM: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error procesando la respuesta del servicio VUCEM',
                'error_type' => 'processing_error'
            ];
        }
    }

    /**
     * Interpretar errores SOAP Fault de manera amigable
     */
    private function interpretSoapFault(SoapFault $e): string
    {
        $faultString = strtolower($e->faultstring);
        
        if (strpos($faultString, 'unauthorized') !== false || strpos($faultString, 'authentication') !== false) {
            return 'RFC o clave de webservice incorrectos. Verifique su información en Mi Perfil.';
        }
        
        if (strpos($faultString, 'timeout') !== false) {
            return 'Tiempo de espera agotado. El servicio VUCEM no responde.';
        }
        
        if (strpos($faultString, 'not found') !== false) {
            return 'El COVE especificado no existe o no está asociado al RFC configurado.';
        }
        
        return 'Error del servicio VUCEM: ' . $e->faultstring;
    }

    /**
     * Determinar método de valoración basado en la respuesta
     */
    private function determineMetodoValoracion($operacion): string
    {
        // Aquí puedes implementar lógica para determinar el método de valoración
        // basado en los datos de la operación
        return '1'; // Método 1 por defecto, ajustar según necesidades
    }

    /**
     * Extraer fecha de expedición de la respuesta
     */
    private function extractFechaExpedicion($response): string
    {
        // Si viene en la respuesta, extraerla
        if (isset($response->horaRecepcion)) {
            return date('Y-m-d', strtotime($response->horaRecepcion));
        }
        
        return date('Y-m-d'); // Fecha actual como fallback
    }

    /**
     * Extraer información del emisor
     */
    private function extractEmisor($operacion): string
    {
        // Aquí puedes implementar lógica para extraer datos del emisor
        // de los datos de la operación
        return 'Emisor VUCEM'; // Placeholder, ajustar según datos disponibles
    }

    /**
     * Obtener información de depuración del último request/response
     */
    public function getDebugInfo(): array
    {
        if (!$this->soapClient) {
            return ['error' => 'SOAP Client no inicializado'];
        }

        return [
            'last_request_headers' => $this->soapClient->__getLastRequestHeaders(),
            'last_request' => $this->soapClient->__getLastRequest(),
            'last_response_headers' => $this->soapClient->__getLastResponseHeaders(),
            'last_response' => $this->soapClient->__getLastResponse(),
        ];
    }
}