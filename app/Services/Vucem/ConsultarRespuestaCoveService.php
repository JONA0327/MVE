<?php

namespace App\Services\Vucem;

use SoapClient;
use SoapVar;
use SoapHeader;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\Vucem\EFirmaService;

/**
 * Servicio para consultar la respuesta de un COVE en VUCEM
 * 
 * Este servicio obtiene los datos estructurados de un COVE (no el PDF)
 * usando el Web Service ConsultarRespuestaCove
 * 
 * Endpoint: https://www.ventanillaunica.gob.mx:8110/ventanilla/ConsultarRespuestaCoveService
 * WSDL: https://www.ventanillaunica.gob.mx/ventanilla/ConsultarRespuestaCoveService?wsdl
 */
class ConsultarRespuestaCoveService
{
    private string $endpoint;
    private string $soapAction;
    private string $rfc;
    private string $webserviceUser;
    private string $webserviceKey;
    private EFirmaService $eFirmaService;
    private ?SoapClient $soapClient = null;
    private array $debugInfo = [];

    const NAMESPACE_SERVICE = 'http://www.ventanillaunica.gob.mx/cove/ws/service/';
    const NAMESPACE_OXML = 'http://www.ventanillaunica.gob.mx/cove/ws/oxml/';
    const NAMESPACE_WSSE = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
    const NAMESPACE_WSU = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';
    const NAMESPACE_SOAP = 'http://schemas.xmlsoap.org/soap/envelope/';

    public function __construct($user = null)
    {
        $this->endpoint = config('vucem.consultar_respuesta_cove.endpoint', 
            'https://www.ventanillaunica.gob.mx:8110/ventanilla/ConsultarRespuestaCoveService');
        
        $this->soapAction = config('vucem.consultar_respuesta_cove.soap_action',
            'http://www.ventanillaunica.gob.mx/ConsultarRespuestaCove');

        // Usar el usuario proporcionado o el autenticado
        $user = $user ?? Auth::user();
        if ($user) {
            $this->rfc = $user->rfc ?? '';
            $this->webserviceUser = $user->webservice_user ?? $user->rfc ?? '';
            $this->webserviceKey = $user->getDecryptedWebserviceKey() ?? '';
        }

        $this->eFirmaService = new EFirmaService();
    }

    /**
     * Consultar respuesta de COVE por número de operación
     * 
     * @param int $numeroOperacion Número de operación asignado al enviar el COVE
     * @return array Datos estructurados del COVE
     */
    public function consultarRespuesta(int $numeroOperacion): array
    {
        $soapRequest = null;
        $rawResponse = null;

        try {
            Log::info('[CONSULTAR-RESPUESTA-COVE] Iniciando consulta', [
                'numero_operacion' => $numeroOperacion,
                'rfc' => $this->rfc,
                'endpoint' => $this->endpoint
            ]);

            // Validar número de operación
            if (empty($numeroOperacion) || $numeroOperacion <= 0) {
                return [
                    'success' => false,
                    'message' => 'El número de operación es requerido y debe ser mayor a 0',
                    'error_type' => 'validation_error'
                ];
            }

            // Generar firma electrónica
            // La cadena original es: |numeroOperacion|RFC|
            $cadenaOriginal = "|{$numeroOperacion}|{$this->rfc}|";
            
            Log::info('[CONSULTAR-RESPUESTA-COVE] Generando firma electrónica', [
                'cadena_original' => $cadenaOriginal
            ]);

            $firmaData = $this->eFirmaService->generarFirmaElectronicaRaw($cadenaOriginal, $this->rfc);

            // Crear cliente SOAP
            $client = $this->createSoapClient();

            // Construir el request SOAP manualmente
            $soapRequest = $this->buildSoapRequest($numeroOperacion, $firmaData);

            Log::info('[CONSULTAR-RESPUESTA-COVE] Enviando request SOAP');

            // Enviar request
            $rawResponse = $client->__doRequest(
                $soapRequest,
                $this->endpoint,
                $this->soapAction,
                SOAP_1_1
            );

            // Guardar debug info
            $this->debugInfo = [
                'last_request' => $soapRequest,
                'last_response' => $rawResponse,
                'endpoint' => $this->endpoint,
                'soap_action' => $this->soapAction
            ];

            Log::info('[CONSULTAR-RESPUESTA-COVE] Respuesta recibida');

            // Procesar respuesta
            return $this->processResponse($rawResponse);

        } catch (Exception $e) {
            Log::error('[CONSULTAR-RESPUESTA-COVE] Error en consulta', [
                'numero_operacion' => $numeroOperacion,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (isset($soapRequest) && isset($rawResponse)) {
                $this->debugInfo = [
                    'last_request' => $soapRequest,
                    'last_response' => $rawResponse ?? 'N/A',
                    'endpoint' => $this->endpoint,
                    'soap_action' => $this->soapAction
                ];
            }

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_type' => 'exception',
                'debug' => $this->debugInfo
            ];
        }
    }

    /**
     * Construir el request SOAP completo
     */
    private function buildSoapRequest(int $numeroOperacion, array $firmaData): string
    {
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

        // Body con la petición
        // Estructura según XSD: solicitarConsultarRespuestaCoveServicio
        $bodyContent = sprintf(
            '<oxml:solicitarConsultarRespuestaCoveServicio>' .
            '<oxml:numeroOperacion>%d</oxml:numeroOperacion>' .
            '<oxml:firmaElectronica>' .
            '<oxml:certificado>%s</oxml:certificado>' .
            '<oxml:cadenaOriginal>%s</oxml:cadenaOriginal>' .
            '<oxml:firma>%s</oxml:firma>' .
            '</oxml:firmaElectronica>' .
            '</oxml:solicitarConsultarRespuestaCoveServicio>',
            $numeroOperacion,
            $firmaData['certificado'],
            htmlspecialchars($firmaData['cadenaOriginal'], ENT_XML1),
            $firmaData['firma']
        );

        // Envelope completo
        $soapEnvelope = sprintf(
            '<?xml version="1.0" encoding="UTF-8"?>' .
            '<soapenv:Envelope xmlns:soapenv="%s" xmlns:oxml="%s">' .
            '<soapenv:Header>%s</soapenv:Header>' .
            '<soapenv:Body>%s</soapenv:Body>' .
            '</soapenv:Envelope>',
            self::NAMESPACE_SOAP,
            self::NAMESPACE_OXML,
            $securityHeader,
            $bodyContent
        );

        return $soapEnvelope;
    }

    /**
     * Procesar la respuesta del servicio
     */
    private function processResponse(string $rawResponse): array
    {
        try {
            // Parsear XML
            $dom = new \DOMDocument();
            $dom->loadXML($rawResponse);
            
            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace('S', 'http://schemas.xmlsoap.org/soap/envelope/');
            $xpath->registerNamespace('ns', self::NAMESPACE_OXML);

            // Buscar el elemento de respuesta
            $responseNodes = $xpath->query('//ns:solicitarConsultarRespuestaCoveServicioResponse');
            
            if ($responseNodes->length === 0) {
                // Verificar si hay un fault
                $faultNodes = $xpath->query('//S:Fault');
                if ($faultNodes->length > 0) {
                    $faultString = $xpath->query('.//faultstring', $faultNodes->item(0))->item(0)->nodeValue ?? 'Error desconocido';
                    return [
                        'success' => false,
                        'message' => $faultString,
                        'error_type' => 'soap_fault',
                        'debug' => $this->debugInfo
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'No se encontró la respuesta en el XML',
                    'error_type' => 'parse_error',
                    'debug' => $this->debugInfo
                ];
            }

            $responseNode = $responseNodes->item(0);

            // Extraer datos de RespuestaPeticion
            $numeroOperacion = $xpath->query('.//ns:numeroOperacion', $responseNode)->item(0)->nodeValue ?? null;
            $horaRecepcion = $xpath->query('.//ns:horaRecepcion', $responseNode)->item(0)->nodeValue ?? null;
            $leyenda = $xpath->query('.//ns:leyenda', $responseNode)->item(0)->nodeValue ?? null;

            // Extraer respuestas de operaciones (puede haber múltiples)
            $operaciones = [];
            $respuestasOperacionesNodes = $xpath->query('.//ns:respuestasOperaciones', $responseNode);

            foreach ($respuestasOperacionesNodes as $opNode) {
                $operacion = [
                    'numero_factura_o_relacion' => $xpath->query('.//ns:numeroFacturaORelacionFacturas', $opNode)->item(0)->nodeValue ?? null,
                    'contiene_error' => $xpath->query('.//ns:contieneError', $opNode)->item(0)->nodeValue === 'true',
                    'edocument' => $xpath->query('.//ns:eDocument', $opNode)->item(0)->nodeValue ?? null,
                    'numero_adenda' => $xpath->query('.//ns:numeroAdenda', $opNode)->item(0)->nodeValue ?? null,
                    'cadena_original' => $xpath->query('.//ns:cadenaOriginal', $opNode)->item(0)->nodeValue ?? null,
                    'sello_digital' => $xpath->query('.//ns:selloDigital', $opNode)->item(0)->nodeValue ?? null,
                ];

                // Extraer errores si existen
                $errores = [];
                $erroresNodes = $xpath->query('.//ns:errores/ns:mensaje', $opNode);
                foreach ($erroresNodes as $errorNode) {
                    $errores[] = $errorNode->nodeValue;
                }
                $operacion['errores'] = $errores;

                $operaciones[] = $operacion;
            }

            $resultado = [
                'success' => true,
                'numero_operacion' => $numeroOperacion,
                'hora_recepcion' => $horaRecepcion,
                'leyenda' => $leyenda,
                'operaciones' => $operaciones,
                'debug' => $this->debugInfo
            ];

            Log::info('[CONSULTAR-RESPUESTA-COVE] Respuesta procesada exitosamente', [
                'numero_operacion' => $numeroOperacion,
                'total_operaciones' => count($operaciones)
            ]);

            return $resultado;

        } catch (Exception $e) {
            Log::error('[CONSULTAR-RESPUESTA-COVE] Error procesando respuesta', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error procesando respuesta: ' . $e->getMessage(),
                'error_type' => 'parse_error',
                'debug' => $this->debugInfo
            ];
        }
    }

    /**
     * Crear cliente SOAP
     */
    private function createSoapClient(): SoapClient
    {
        $wsdlPath = base_path('wsdl/vucem/COVE/ConsultarRespuestaCoveService.wsdl');

        if (!file_exists($wsdlPath)) {
            throw new Exception("WSDL no encontrado en: {$wsdlPath}");
        }

        Log::info('[CONSULTAR-RESPUESTA-COVE] Inicializando SoapClient', [
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
     * Obtener información de debug
     */
    public function getDebugInfo(): array
    {
        return $this->debugInfo;
    }
}
