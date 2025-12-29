<?php

namespace App\Services\Vucem;

use SoapClient;
use SoapVar;
use SoapHeader;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ConsultaAcusesService
{
    private string $endpoint;
    private string $soapAction;
    private string $webserviceUser;
    private string $webserviceKey;
    private ?SoapClient $soapClient = null;
    private array $debugInfo = [];

    const NAMESPACE_CONSULTA_ACUSES = 'http://www.ventanillaunica.gob.mx/consulta/acuses/oxml';
    const NAMESPACE_WS_CONSULTA_ACUSES = 'http://www.ventanillaunica.gob.mx/ws/consulta/acuses/';
    const NAMESPACE_WSSE = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
    const NAMESPACE_WSU = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';
    const NAMESPACE_SOAP = 'http://schemas.xmlsoap.org/soap/envelope/';

    public function __construct()
    {
        $this->endpoint = config('vucem.edocument.endpoint');
        $this->soapAction = config('vucem.edocument.soap_action');
        
        $user = Auth::user();
        if ($user) {
            // Usar webservice_user si está configurado, si no, usar RFC
            $this->webserviceUser = $user->webservice_user ?? $user->rfc ?? '';
            $this->webserviceKey = $user->getDecryptedWebserviceKey() ?? '';
        }
    }

    /**
     * Consultar acuse (eDocument o COVE) según el folio
     * 
     * Este servicio detecta automáticamente el tipo de acuse según el formato del folio:
     * - Si el folio es formato eDocument (ej: 0170220LIS5D4) → devuelve acuse de eDocument
     * - Si el folio es formato COVE (ej: COVE214KNPVU4) → devuelve acuse de COVE (Acuse de Valor)
     * 
     * @param string $folio Folio de eDocument o COVE
     * @return array Resultado con el PDF en base64 o error
     */
    public function consultarAcuse(string $folio): array
    {
        return $this->consultarAcuseEdocument($folio);
    }

    /**
     * Consultar acuse de eDocument (devuelve PDF en base64)
     * 
     * @deprecated Use consultarAcuse() instead - maneja tanto eDocument como COVE
     */
    public function consultarAcuseEdocument(string $folio): array
    {
        $client = null;
        $soapRequest = null;
        $rawResponse = null;
        
        try {
            Log::info('[CONSULTA-ACUSES] Iniciando consulta de acuse', [
                'folio' => $folio,
                'endpoint' => $this->endpoint
            ]);

            // Validar folio
            if (empty($folio)) {
                return [
                    'success' => false,
                    'message' => 'El folio del eDocument es requerido',
                    'error_type' => 'validation_error'
                ];
            }

            // Crear cliente SOAP
            $client = $this->createSoapClient();

            // Establecer header de seguridad
            $this->setSecurityHeader($client);

            Log::info('[CONSULTA-ACUSES] Enviando request SOAP', [
                'folio' => $folio
            ]);

            // Crear el SOAP request manualmente según Hoja Informativa 23
            // ESTRUCTURA EXACTA que espera el WS usando soapenv (no SOAP-ENV)
            
            // Header WS-Security
            $securityHeader = sprintf(
                '<wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="%s" xmlns:wsu="%s">' .
                '<wsse:UsernameToken>' .
                '<wsse:Username>%s</wsse:Username>' .
                '<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">%s</wsse:Password>' .
                '</wsse:UsernameToken>' .
                '</wsse:Security>',
                self::NAMESPACE_WSSE,
                self::NAMESPACE_WSU,
                $this->webserviceUser,
                $this->webserviceKey
            );
            
            // Body con consultaAcusesPeticion
            $bodyContent = sprintf(
                '<oxml:consultaAcusesPeticion>' .
                '<idEdocument>%s</idEdocument>' .
                '</oxml:consultaAcusesPeticion>',
                htmlspecialchars($folio, ENT_XML1)
            );
            
            // Envelope completo con soapenv
            $soapRequest = sprintf(
                '<?xml version="1.0" encoding="UTF-8"?>' .
                '<soapenv:Envelope xmlns:soapenv="%s" xmlns:oxml="%s">' .
                '<soapenv:Header>%s</soapenv:Header>' .
                '<soapenv:Body>%s</soapenv:Body>' .
                '</soapenv:Envelope>',
                self::NAMESPACE_SOAP,
                self::NAMESPACE_CONSULTA_ACUSES,
                $securityHeader,
                $bodyContent
            );

            Log::info('[CONSULTA-ACUSES] XML Request completo:', [
                'xml' => $soapRequest
            ]);

            // Usar __doRequest directamente para enviar nuestro XML
            try {
                $location = $this->endpoint;
                $action = 'http://www.ventanillaunica.gob.mx/ventanilla/ConsultaAcusesService/consultarAcuseEdocument';
                $version = SOAP_1_1;
                
                $rawResponse = $client->__doRequest(
                    $soapRequest,
                    $location,
                    $action,
                    $version
                );
                
                // Parsear la respuesta MTOM
                $response = $this->parseMtomResponse($rawResponse);
                
                if (!$response) {
                    throw new Exception('No se pudo parsear la respuesta del servidor');
                }
                
            } catch (\SoapFault $soapFault) {
                throw $soapFault;
            }

            // Guardar información de debug
            $this->debugInfo = [
                'last_request' => $soapRequest,
                'last_response' => $rawResponse ?? '',
                'last_request_headers' => 'Custom request via __doRequest',
                'last_response_headers' => 'Custom request via __doRequest'
            ];

            // Log de request/response
            Log::info('[CONSULTA-ACUSES] SOAP Request enviado', [
                'request' => $this->debugInfo['last_request']
            ]);
            Log::info('[CONSULTA-ACUSES] SOAP Response recibido', [
                'response' => $this->debugInfo['last_response']
            ]);

            // Procesar respuesta
            return $this->processResponse($response);

        } catch (Exception $e) {
            // Guardar información de debug ANTES de loguear el error
            if ($client && isset($soapRequest)) {
                try {
                    $this->debugInfo = [
                        'last_request' => $soapRequest ?? 'N/A',
                        'last_response' => $rawResponse ?? 'N/A',
                        'last_request_headers' => 'Custom request',
                        'last_response_headers' => 'Custom request'
                    ];
                } catch (\Exception $debugEx) {
                    // Ignorar errores al obtener debug
                }
            }

            Log::error('[CONSULTA-ACUSES] Error en consulta', [
                'folio' => $folio,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_type' => 'exception',
                'debug' => $this->debugInfo
            ];
        }
    }

    /**
     * Crear cliente SOAP
     */
    private function createSoapClient(): SoapClient
    {
        $wsdlPath = base_path('wsdl/vucem/COVE/edocument/ConsultaAcusesServiceWS.wsdl');

        if (!file_exists($wsdlPath)) {
            throw new Exception("WSDL no encontrado en: {$wsdlPath}");
        }

        Log::info('[CONSULTA-ACUSES] Inicializando SoapClient', [
            'wsdl' => $wsdlPath,
            'endpoint' => $this->endpoint
        ]);

        return new SoapClient($wsdlPath, [
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
    }

    /**
     * Establecer header de seguridad WS-Security según Hoja Informativa 23
     */
    private function setSecurityHeader(SoapClient $client): void
    {
        if (empty($this->webserviceUser) || empty($this->webserviceKey)) {
            throw new Exception('Credenciales de Web Service no configuradas');
        }

        // Crear el XML del header WS-Security EXACTO según Hoja Informativa 23
        // CRÍTICO: soapenv:mustUnderstand (no SOAP-ENV:mustUnderstand)
        $securityXml = sprintf(
            '<wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="%s" xmlns:wsu="%s">' .
            '<wsse:UsernameToken>' .
            '<wsse:Username>%s</wsse:Username>' .
            '<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">%s</wsse:Password>' .
            '</wsse:UsernameToken>' .
            '</wsse:Security>',
            self::NAMESPACE_WSSE,
            self::NAMESPACE_WSU,
            $this->webserviceUser,
            $this->webserviceKey
        );

        $securityHeader = new SoapHeader(
            self::NAMESPACE_WSSE,
            'Security',
            new SoapVar($securityXml, XSD_ANYXML),
            true
        );

        $client->__setSoapHeaders([$securityHeader]);

        Log::info('[CONSULTA-ACUSES] Header de seguridad establecido', [
            'username' => $this->webserviceUser
        ]);
    }

    /**
     * Procesar respuesta del servicio
     */
    private function processResponse($response): array
    {
        try {
            // La respuesta tiene esta estructura:
            // - code: int
            // - descripcion: string
            // - error: boolean
            // - mensaje: array de mensajes
            // - mensajeErrores: array de errores
            // - acuseDocumento: string (PDF en base64)

            Log::info('[CONSULTA-ACUSES] Procesando respuesta', [
                'response_type' => gettype($response),
                'response_class' => is_object($response) ? get_class($response) : null
            ]);

            if (is_object($response) && property_exists($response, 'respuesta')) {
                $respuesta = $response->respuesta;
            } else {
                $respuesta = $response;
            }

            // Verificar si hay error
            $hasError = $respuesta->error ?? false;
            $code = $respuesta->code ?? null;
            $descripcion = $respuesta->descripcion ?? '';
            $acuseDocumento = $respuesta->acuseDocumento ?? null;

            // Construir mensajes
            $mensajes = [];
            if (isset($respuesta->mensaje) && is_array($respuesta->mensaje)) {
                foreach ($respuesta->mensaje as $msg) {
                    $mensajes[] = [
                        'clave' => $msg->claveMensaje ?? '',
                        'descripcion' => $msg->descripcion ?? ''
                    ];
                }
            }

            $mensajesError = [];
            if (isset($respuesta->mensajeErrores) && is_array($respuesta->mensajeErrores)) {
                foreach ($respuesta->mensajeErrores as $msg) {
                    $mensajesError[] = [
                        'clave' => $msg->claveMensaje ?? '',
                        'descripcion' => $msg->descripcion ?? ''
                    ];
                }
            }

            Log::info('[CONSULTA-ACUSES] Respuesta procesada', [
                'has_error' => $hasError,
                'code' => $code,
                'descripcion' => $descripcion,
                'tiene_acuse' => !empty($acuseDocumento)
            ]);

            return [
                'success' => !$hasError && !empty($acuseDocumento),
                'code' => $code,
                'descripcion' => $descripcion,
                'acuse_documento' => $acuseDocumento,
                'mensajes' => $mensajes,
                'mensajes_error' => $mensajesError,
                'debug' => $this->debugInfo
            ];

        } catch (Exception $e) {
            Log::error('[CONSULTA-ACUSES] Error procesando respuesta', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error procesando respuesta: ' . $e->getMessage(),
                'debug' => $this->debugInfo
            ];
        }
    }

    /**
     * Obtener información de debug de la última llamada
     */
    public function getDebugInfo(): array
    {
        return $this->debugInfo;
    }

    /**
     * Parsear respuesta MTOM manualmente
     */
    private function parseMtomResponse(string $rawResponse): ?\stdClass
    {
        try {
            // Extraer el XML de la respuesta multipart
            // Buscar el inicio del XML después de los headers MIME
            $xmlStart = strpos($rawResponse, '<?xml');
            if ($xmlStart === false) {
                Log::error('[CONSULTA-ACUSES] No se encontró XML en la respuesta');
                return null;
            }

            // Extraer hasta el final del envelope
            $xmlEnd = strpos($rawResponse, '</S:Envelope>');
            if ($xmlEnd === false) {
                $xmlEnd = strpos($rawResponse, '</SOAP-ENV:Envelope>');
            }
            
            if ($xmlEnd === false) {
                Log::error('[CONSULTA-ACUSES] No se encontró el cierre del Envelope');
                return null;
            }

            $xml = substr($rawResponse, $xmlStart, $xmlEnd - $xmlStart + strlen('</S:Envelope>'));
            
            Log::info('[CONSULTA-ACUSES] XML extraído de respuesta MTOM');

            // Parsear el XML
            $dom = new \DOMDocument();
            $dom->loadXML($xml);
            
            // Buscar el elemento responseConsultaAcuses
            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace('S', 'http://schemas.xmlsoap.org/soap/envelope/');
            $xpath->registerNamespace('ns3', 'http://www.ventanillaunica.gob.mx/ws/consulta/acuses/');
            
            $responseNodes = $xpath->query('//ns3:responseConsultaAcuses');
            if ($responseNodes->length === 0) {
                Log::error('[CONSULTA-ACUSES] No se encontró responseConsultaAcuses en el XML');
                return null;
            }

            $responseNode = $responseNodes->item(0);
            
            // Construir objeto de respuesta
            $response = new \stdClass();
            $response->respuesta = new \stdClass();
            
            // Extraer campos
            $codeNode = $xpath->query('.//code', $responseNode)->item(0);
            $response->respuesta->code = $codeNode ? (int)$codeNode->nodeValue : null;
            
            $errorNode = $xpath->query('.//error', $responseNode)->item(0);
            $response->respuesta->error = $errorNode ? ($errorNode->nodeValue === 'true') : false;
            
            $descripcionNode = $xpath->query('.//descripcion', $responseNode)->item(0);
            $response->respuesta->descripcion = $descripcionNode ? $descripcionNode->nodeValue : null;
            
            $acuseNode = $xpath->query('.//acuseDocumento', $responseNode)->item(0);
            $response->respuesta->acuseDocumento = $acuseNode ? $acuseNode->nodeValue : null;
            
            // Extraer mensajes
            $response->respuesta->mensaje = [];
            $mensajeNodes = $xpath->query('.//mensaje', $responseNode);
            foreach ($mensajeNodes as $msgNode) {
                $msg = new \stdClass();
                $claveNode = $xpath->query('.//claveMensaje', $msgNode)->item(0);
                $descNode = $xpath->query('.//descripcion', $msgNode)->item(0);
                $msg->claveMensaje = $claveNode ? $claveNode->nodeValue : '';
                $msg->descripcion = $descNode ? $descNode->nodeValue : '';
                $response->respuesta->mensaje[] = $msg;
            }
            
            // Extraer errores
            $response->respuesta->mensajeErrores = [];
            $errorNodes = $xpath->query('.//mensajeErrores', $responseNode);
            foreach ($errorNodes as $errNode) {
                $err = new \stdClass();
                $claveNode = $xpath->query('.//claveMensaje', $errNode)->item(0);
                $descNode = $xpath->query('.//descripcion', $errNode)->item(0);
                $err->claveMensaje = $claveNode ? $claveNode->nodeValue : '';
                $err->descripcion = $descNode ? $descNode->nodeValue : '';
                $response->respuesta->mensajeErrores[] = $err;
            }

            Log::info('[CONSULTA-ACUSES] Respuesta MTOM parseada exitosamente', [
                'code' => $response->respuesta->code,
                'error' => $response->respuesta->error,
                'tiene_acuse' => !empty($response->respuesta->acuseDocumento)
            ]);

            return $response;
            
        } catch (\Exception $e) {
            Log::error('[CONSULTA-ACUSES] Error parseando respuesta MTOM', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}
