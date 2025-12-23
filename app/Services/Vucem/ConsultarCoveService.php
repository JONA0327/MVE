<?php

namespace App\Services\Vucem;

use SoapClient;
use SoapFault;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ConsultarCoveService
{
    private $soapClient;
    private $endpoint;
    private $soapAction;
    private $rfc;
    private $wsPassword;
    private $debugInfo = [];

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
        
        if (!$user->hasWebserviceKey()) {
            throw new Exception('Usuario no tiene clave de webservice configurada en su perfil');
        }
        
        $this->rfc = $user->rfc;
        $this->wsPassword = $user->getDecryptedWebserviceKey();
        $this->endpoint = config('vucem.consultar_cove.endpoint');
        $this->soapAction = config('vucem.consultar_cove.soap_action');
        
        $this->initializeSoapClient();
    }

    /**
     * Inicializar cliente SOAP con configuración para ambiente de pruebas
     */
    private function initializeSoapClient(): void
    {
        try {
            $wsdlPath = config('vucem.consultar_cove.wsdl_path');
            
            if (!file_exists($wsdlPath)) {
                throw new Exception("WSDL no encontrado en: {$wsdlPath}");
            }

            // Configuración específica para ambiente de producción VUCEM
            $options = [
                'location' => $this->endpoint, // Sobrescribir endpoint del WSDL
                'soap_version' => \SOAP_1_1,
                'exceptions' => true,
                'trace' => true,
                'cache_wsdl' => \WSDL_CACHE_NONE,
                'connection_timeout' => 8, // Timeout más agresivo
                'user_agent' => config('vucem.consultar_cove.user_agent', 'MVE-Laravel-SOAP-Client/1.0'),
                'stream_context' => stream_context_create([
                    'http' => [
                        'timeout' => 8, // Timeout más agresivo
                        'method' => 'POST',
                        'header' => 'Connection: close\r\n' // Cerrar conexión rápidamente
                    ]
                ])
            ];

            $this->soapClient = new SoapClient($wsdlPath, $options);
            
            Log::info('[COVE] Cliente SOAP inicializado correctamente', [
                'endpoint' => $this->endpoint,
                'wsdl' => $wsdlPath,
                'rfc' => $this->rfc
            ]);
            
        } catch (Exception $e) {
            Log::error('[COVE] Error inicializando cliente SOAP: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Consultar COVE en VUCEM (ambiente de pruebas)
     */
    public function consultarCove(string $folioCove): array
    {
        try {
            Log::info('[COVE] Iniciando consulta', [
                'folio' => $folioCove,
                'rfc' => $this->rfc,
                'endpoint' => $this->endpoint
            ]);

            // Validar folio
            if (empty($folioCove) || strlen($folioCove) < 5) {
                return [
                    'success' => false,
                    'message' => 'El folio de COVE debe tener al menos 5 caracteres',
                    'error_type' => 'validation_error'
                ];
            }

            // Establecer headers de seguridad
            $this->setSecurityHeader();

            // Preparar request con estructura correcta según WSDL
            $requestData = [
                'numeroOperacion' => $folioCove,
                'firmaElectronica' => [
                    'certificado' => '',
                    'cadenaOriginal' => '',
                    'firma' => ''
                ]
            ];

            // Ejecutar consulta SOAP con nombre correcto de operación
            $response = $this->soapClient->__soapCall(
                'ConsultarRespuestaCove',  // Nombre EXACTO de la operación del WSDL
                [$requestData],
                [
                    'SOAPAction' => $this->soapAction,
                    'uri' => 'http://www.ventanillaunica.gob.mx/cove/ws/oxml/'
                ]
            );

            // Guardar información de debug
            $this->debugInfo = [
                'last_request' => $this->soapClient->__getLastRequest(),
                'last_response' => $this->soapClient->__getLastResponse(),
                'last_request_headers' => $this->soapClient->__getLastRequestHeaders(),
                'last_response_headers' => $this->soapClient->__getLastResponseHeaders()
            ];

            if (config('vucem.logging.log_soap_requests')) {
                Log::info('[COVE] SOAP Request enviado', [
                    'request' => $this->debugInfo['last_request']
                ]);
                Log::info('[COVE] SOAP Response recibido', [
                    'response' => $this->debugInfo['last_response']
                ]);
            }

            return $this->processResponse($response, $folioCove);

        } catch (SoapFault $e) {
            return $this->handleSoapFault($e, $folioCove);
        } catch (Exception $e) {
            return $this->handleNetworkError($e, $folioCove);
        }
    }

    /**
     * Manejar errores SOAP Fault
     */
    private function handleSoapFault(SoapFault $e, string $folioCove): array
    {
        Log::warning('[COVE] SOAP Fault', [
            'folio' => $folioCove,
            'fault_code' => $e->faultcode,
            'fault_string' => $e->faultstring,
            'detail' => $e->detail ?? 'N/A'
        ]);

        $errorMessage = $this->interpretSoapFault($e);

        return [
            'success' => false,
            'message' => "Error del servicio VUCEM: {$errorMessage}",
            'error_type' => 'soap_fault',
            'details' => [
                'fault_code' => $e->faultcode,
                'fault_string' => $e->faultstring,
                'detail' => $e->detail ?? null
            ]
        ];
    }

    /**
     * Manejar errores de red/conexión
     */
    private function handleNetworkError(Exception $e, string $folioCove): array
    {
        Log::error('[COVE] Error de red/conexión', [
            'folio' => $folioCove,
            'error' => $e->getMessage(),
            'endpoint' => $this->endpoint
        ]);

        $message = 'No se pudo conectar con el servicio VUCEM';
        
        if (strpos($e->getMessage(), 'timeout') !== false) {
            $message = 'Tiempo de espera agotado al conectar con VUCEM';
        } elseif (strpos($e->getMessage(), 'Could not connect') !== false) {
            $message = 'No se pudo establecer conexión con VUCEM';
        } elseif (strpos($e->getMessage(), 'SSL') !== false) {
            $message = 'Error de certificado SSL al conectar con VUCEM';
        }

        return [
            'success' => false,
            'message' => $message,
            'error_type' => 'network',
            'details' => [
                'error' => $e->getMessage(),
                'endpoint' => $this->endpoint
            ]
        ];
    }

    /**
     * Establecer header de seguridad WS-Security UsernameToken
     */
    private function setSecurityHeader(): void
    {
        $securityNamespace = config('vucem.security.ws_security_namespace');
        $passwordType = config('vucem.security.password_type');

        $securityHeader = new \SoapHeader(
            $securityNamespace,
            'Security',
            [
                'UsernameToken' => [
                    'Username' => $this->rfc,
                    'Password' => [
                        '_' => $this->wsPassword,
                        'Type' => $passwordType
                    ]
                ]
            ]
        );

        $this->soapClient->__setSoapHeaders($securityHeader);
    }

    /**

     * Procesar respuesta del servicio
     */
    private function processResponse($response, string $folioCove): array
    {
        Log::info('[COVE] Procesando respuesta', [
            'folio' => $folioCove,
            'response_type' => gettype($response)
        ]);

        if (!$response || empty($response)) {
            return [
                'success' => false,
                'message' => 'Respuesta vacía del servicio VUCEM',
                'error_type' => 'empty_response'
            ];
        }

        try {
            // Estructura de respuesta: solicitarConsultarRespuestaCoveServicioResponse
            $responseData = null;
            if (isset($response->solicitarConsultarRespuestaCoveServicioResponse)) {
                $responseData = $response->solicitarConsultarRespuestaCoveServicioResponse;
            } else {
                $responseData = $response;
            }

            // Extraer datos de la operación si existe
            $operacion = $responseData->operacion ?? $responseData;
            
            if (!$operacion || empty($operacion)) {
                return [
                    'success' => false,
                    'message' => "COVE {$folioCove} no encontrado en VUCEM",
                    'error_type' => 'not_found'
                ];
            }

            $coveData = [
                'success' => true,
                'data' => [
                    'folio' => $folioCove,
                    'numero' => $operacion->numero ?? 'N/A',
                    'estatus' => $operacion->estatus ?? 'Válido',
                    'fecha_emision' => $this->extractFechaExpedicion($operacion),
                    'rfc_solicitante' => $this->rfc,
                    'metodo_valoracion' => $this->determineMetodoValoracion($operacion),
                    'numero_factura' => $operacion->numeroFactura ?? $operacion->factura ?? 'N/A',
                    'emisor' => $this->extractEmisor($operacion),
                    'fecha_expedicion' => $this->extractFechaExpedicion($operacion)
                ]
            ];

            Log::info('[COVE] COVE encontrado exitosamente', $coveData['data']);
            
            return $coveData;

        } catch (Exception $e) {
            Log::error('[COVE] Error procesando respuesta: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error procesando respuesta de VUCEM: ' . $e->getMessage(),
                'error_type' => 'processing_error'
            ];
        }
    }

    /**
     * Interpretar códigos de error SOAP Fault
     */
    private function interpretSoapFault(SoapFault $e): string
    {
        $faultString = strtolower($e->faultstring);
        
        if (strpos($faultString, 'invalid') !== false || strpos($faultString, 'not found') !== false) {
            return 'COVE no encontrado o inválido';
        }
        
        if (strpos($faultString, 'authentication') !== false || strpos($faultString, 'unauthorized') !== false) {
            return 'Credenciales de autenticación inválidas';
        }
        
        if (strpos($faultString, 'timeout') !== false) {
            return 'Tiempo de espera agotado en VUCEM';
        }

        return $e->faultstring;
    }

    /**
     * Determinar método de valoración
     */
    private function determineMetodoValoracion($operacion): string
    {
        if (isset($operacion->metodoValoracion)) {
            return $operacion->metodoValoracion;
        }
        
        return '1'; // Valor de transacción por defecto
    }

    /**
     * Extraer fecha de expedición
     */
    private function extractFechaExpedicion($operacion): string
    {
        if (isset($operacion->fechaExpedicion)) {
            return date('Y-m-d', strtotime($operacion->fechaExpedicion));
        }
        
        if (isset($operacion->fecha)) {
            return date('Y-m-d', strtotime($operacion->fecha));
        }
        
        return date('Y-m-d'); // Fecha actual como fallback
    }

    /**
     * Extraer emisor
     */
    private function extractEmisor($operacion): string
    {
        return $operacion->emisor ?? $operacion->proveedor ?? 'N/A';
    }

    /**
     * Obtener información de debug
     */
    public function getDebugInfo(): array
    {
        return $this->debugInfo;
    }
}