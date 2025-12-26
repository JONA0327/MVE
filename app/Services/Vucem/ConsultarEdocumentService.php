<?php

namespace App\Services\Vucem;

use SoapClient;
use SoapFault;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\CoveConsultaException;
use App\Services\Vucem\EFirmaService;

class ConsultarEdocumentService
{
    private SoapClient $soapClient;
    private string $rfc;
    private string $claveWebService;
    private string $endpoint;
    private string $soapAction;
    private EFirmaService $efirmaService;
    private array $debugInfo = [];

    public function __construct()
    {
        // Obtener credenciales del usuario autenticado
        $user = Auth::user();

        if (!$user) {
            throw new Exception('Usuario no autenticado');
        }

        if (!$user->rfc) {
            throw new Exception('Usuario no tiene RFC configurado en su perfil');
        }

        // Usar contraseña global de VUCEM (para pruebas)
        $this->rfc = $user->rfc;
        $this->claveWebService = env('VUCEM_WS_PASSWORD');
        
        if (!$this->claveWebService) {
            throw new Exception('VUCEM_WS_PASSWORD no configurado en .env');
        }
        $this->endpoint = config('vucem.edocument.endpoint', 'https://www.ventanillaunica.gob.mx/ventanilla/ConsultarEdocument');
        $this->soapAction = config('vucem.edocument.soap_action', 'http://www.ventanillaunica.gob.mx/cove/ws/service/ConsultarEdocument');
        
        $this->efirmaService = app(EFirmaService::class);
        
        $this->initializeSoapClient();
    }

    private function initializeSoapClient(): void
    {
        try {
            $wsdlPath = base_path('wsdl/vucem/COVE/edocument/ConsultarEdocument.wsdl');
            
            if (!file_exists($wsdlPath)) {
                throw new Exception("WSDL no encontrado en: {$wsdlPath}");
            }

            Log::info('[EDOCUMENT] Inicializando SoapClient', [
                'wsdl' => $wsdlPath,
                'endpoint' => $this->endpoint,
                'rfc' => $this->rfc
            ]);
            
            $this->soapClient = new SoapClient($wsdlPath, [
                'trace' => true,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'soap_version' => SOAP_1_1,
                'connection_timeout' => 30,
                'user_agent' => 'Laravel-VUCEM-Client/1.0',
                'location' => $this->endpoint,
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ])
            ]);

            Log::info('[EDOCUMENT] Cliente SOAP inicializado correctamente', [
                'endpoint' => $this->endpoint,
                'wsdl' => $wsdlPath,
                'rfc' => $this->rfc
            ]);

        } catch (Exception $e) {
            Log::error('[EDOCUMENT] Error inicializando cliente SOAP: ' . $e->getMessage());
            throw new CoveConsultaException("Error inicializando cliente SOAP: " . $e->getMessage());
        }
    }

    /**
     * Consultar edocument COVE en VUCEM
     *
     * @param string $eDocument El COVE a consultar
     * @param string|null $numeroAdenda Número de adenda opcional
     * @return array
     * @throws CoveConsultaException
     */
    public function consultarEdocument(string $eDocument, ?string $numeroAdenda = null): array
    {
        try {
            Log::info('[EDOCUMENT] Iniciando consulta', [
                'eDocument' => $eDocument,
                'numeroAdenda' => $numeroAdenda,
                'rfc' => $this->rfc,
                'endpoint' => $this->endpoint
            ]);

            // Validar eDocument
            if (empty($eDocument) || strlen($eDocument) < 5) {
                return [
                    'success' => false,
                    'message' => 'El eDocument debe tener al menos 5 caracteres',
                    'error_type' => 'validation_error'
                ];
            }

            // Establecer headers de seguridad
            $this->setSecurityHeader();

            // Generar firma electrónica para el eDocument
            Log::info('[EDOCUMENT] Generando firma electrónica para eDocument: ' . $eDocument);
            $firmaElectronica = $this->efirmaService->generarFirmaElectronica($eDocument, $this->rfc);

            // Construir request con el wrapper correcto
            $requestData = [
                'request' => [
                    'firmaElectronica' => $firmaElectronica,
                    'criterioBusqueda' => [
                        'eDocument' => $eDocument
                    ]
                ]
            ];

            // Agregar número de adenda si se proporciona
            if ($numeroAdenda) {
                $requestData['request']['criterioBusqueda']['numeroAdenda'] = $numeroAdenda;
            }

            Log::info('[EDOCUMENT] Enviando request SOAP', [
                'eDocument' => $eDocument,
                'tiene_adenda' => !empty($numeroAdenda)
            ]);

            // Realizar llamada SOAP
            $response = $this->soapClient->__soapCall(
                'ConsultarEdocument',
                [$requestData],
                [
                    'SOAPAction' => $this->soapAction,
                    'uri' => 'http://www.ventanillaunica.gob.mx/cove/ws/service/'
                ]
            );

            // Guardar información de debug INMEDIATAMENTE
            $this->debugInfo = [
                'last_request' => $this->soapClient->__getLastRequest(),
                'last_response' => $this->soapClient->__getLastResponse(),
                'last_request_headers' => $this->soapClient->__getLastRequestHeaders(),
                'last_response_headers' => $this->soapClient->__getLastResponseHeaders()
            ];

            // Log SIEMPRE para debug
            Log::info('[EDOCUMENT] SOAP Request enviado', [
                'request' => $this->debugInfo['last_request']
            ]);
            Log::info('[EDOCUMENT] SOAP Response recibido', [
                'response' => $this->debugInfo['last_response']
            ]);

            return $this->processResponse($response, $eDocument);

        } catch (SoapFault $e) {
            return $this->handleSoapFault($e, $eDocument);
        } catch (Exception $e) {
            return $this->handleNetworkError($e, $eDocument);
        }
    }

    /**
     * Procesar respuesta del webservice
     */
    private function processResponse($response, string $eDocument): array
    {
        Log::info('[EDOCUMENT] Procesando respuesta', [
            'eDocument' => $eDocument,
            'response_type' => gettype($response)
        ]);

        if (!$response) {
            return [
                'success' => false,
                'message' => 'Respuesta vacía del servidor',
                'eDocument' => $eDocument
            ];
        }

        // Verificar si hay errores en la respuesta
        if (isset($response->contieneError) && $response->contieneError) {
            $errores = [];
            if (isset($response->errores) && is_array($response->errores)) {
                foreach ($response->errores as $error) {
                    $errores[] = $error->descripcion ?? 'Error sin descripción';
                }
            }
            
            return [
                'success' => false,
                'message' => $response->mensaje ?? 'Error del servidor',
                'errores' => $errores,
                'eDocument' => $eDocument
            ];
        }

        // Procesar resultado exitoso
        $result = [
            'success' => true,
            'message' => $response->mensaje ?? 'Consulta exitosa',
            'eDocument' => $eDocument
        ];

        // Extraer información del resultado de búsqueda
        if (isset($response->resultadoBusqueda)) {
            $resultadoBusqueda = $response->resultadoBusqueda;
            
            if (isset($resultadoBusqueda->cove)) {
                $cove = $resultadoBusqueda->cove;
                
                $result['cove_data'] = [
                    'eDocument' => $cove->eDocument ?? null,
                    'tipoOperacion' => $cove->tipoOperacion ?? null,
                    'numeroFacturaRelacionFacturas' => $cove->numeroFacturaRelacionFacturas ?? null,
                    'relacionFacturas' => $cove->relacionFacturas ?? null,
                    'automotriz' => $cove->automotriz ?? null
                ];
            }
        }

        Log::info('[EDOCUMENT] Consulta exitosa', [
            'eDocument' => $eDocument,
            'tiene_cove_data' => isset($result['cove_data'])
        ]);

        return $result;
    }

    /**
     * Manejar errores SOAP
     */
    private function handleSoapFault(SoapFault $e, string $eDocument): array
    {
        $errorMessage = $e->getMessage();
        
        // Capturar debug info incluso en error
        $this->debugInfo = [
            'last_request' => $this->soapClient->__getLastRequest(),
            'last_response' => $this->soapClient->__getLastResponse(),
            'last_request_headers' => $this->soapClient->__getLastRequestHeaders(),
            'last_response_headers' => $this->soapClient->__getLastResponseHeaders()
        ];
        
        Log::warning('[EDOCUMENT] SOAP Fault', [
            'eDocument' => $eDocument,
            'fault_code' => $e->faultcode ?? 'Unknown',
            'fault_string' => $e->faultstring ?? $errorMessage
        ]);
        
        // Log request/response para debug
        Log::info('[EDOCUMENT] SOAP Request (Error)', [
            'request' => $this->debugInfo['last_request']
        ]);
        Log::info('[EDOCUMENT] SOAP Response (Error)', [
            'response' => $this->debugInfo['last_response']
        ]);

        return [
            'success' => false,
            'message' => $errorMessage,
            'error_type' => 'soap_fault',
            'eDocument' => $eDocument
        ];
    }

    /**
     * Manejar errores de red/conexión
     */
    private function handleNetworkError(Exception $e, string $eDocument): array
    {
        Log::error('[EDOCUMENT] Error de red/conexión', [
            'eDocument' => $eDocument,
            'error' => $e->getMessage(),
            'endpoint' => $this->endpoint
        ]);

        throw new CoveConsultaException($e->getMessage());
    }

    /**
     * Establecer header de seguridad WS-Security
     */
    private function setSecurityHeader(): void
    {
        $securityXML = '
            <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"
                           xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                <wsse:UsernameToken wsu:Id="UsernameToken-1">
                    <wsse:Username>' . htmlspecialchars($this->rfc) . '</wsse:Username>
                    <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">' . 
                        htmlspecialchars($this->claveWebService) . '</wsse:Password>
                </wsse:UsernameToken>
            </wsse:Security>';

        $this->soapClient->__setSoapHeaders([
            new \SoapHeader('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd', 'Security', new \SoapVar($securityXML, XSD_ANYXML))
        ]);
    }

    /**
     * Obtener información de debug
     */
    public function getDebugInfo(): array
    {
        return $this->debugInfo;
    }
}