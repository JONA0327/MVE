<?php

namespace App\Services\Vucem;

use SoapClient;
use SoapFault;
use SoapVar;
use SoapHeader;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\CoveConsultaException;

/**
 * Servicio mejorado para consultar eDocument con control total de namespaces
 */
class ConsultarEdocumentServiceV2
{
    private SoapClient $soapClient;
    private string $rfc;
    private string $claveWebService;
    private string $endpoint;
    private string $soapAction;
    private EFirmaService $efirmaService;
    private array $debugInfo = [];

    // Namespaces oficiales según XSD
    private const NS_CONSULTAR = 'http://www.ventanillaunica.gob.mx/ConsultarEdocument/';
    private const NS_OXML = 'http://www.ventanillaunica.gob.mx/cove/ws/oxml/';
    private const NS_WSSE = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
    private const NS_WSU = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';

    public function __construct()
    {
        $user = Auth::user();

        if (!$user) {
            throw new Exception('Usuario no autenticado');
        }

        if (!$user->rfc) {
            throw new Exception('Usuario no tiene RFC configurado en su perfil');
        }

        $this->rfc = $user->rfc;
        $this->claveWebService = $user->getDecryptedWebserviceKey();
        
        if (!$this->claveWebService) {
            throw new Exception('El usuario no tiene clave webservice VUCEM configurada.');
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

            Log::info('[EDOCUMENT-V2] Inicializando SoapClient', [
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
                'user_agent' => 'Laravel-VUCEM-Client/2.0',
                'location' => $this->endpoint,
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ],
                    'http' => [
                        'protocol_version' => '1.1',
                        'header' => "Connection: close\r\n"
                    ]
                ])
            ]);

            Log::info('[EDOCUMENT-V2] Cliente SOAP inicializado correctamente');

        } catch (Exception $e) {
            Log::error('[EDOCUMENT-V2] Error inicializando cliente SOAP: ' . $e->getMessage());
            throw new CoveConsultaException("Error inicializando cliente SOAP: " . $e->getMessage());
        }
    }

    /**
     * Consultar edocument COVE en VUCEM con control total de XML
     *
     * @param string $eDocument El COVE a consultar
     * @param string|null $numeroAdenda Número de adenda opcional
     * @return array
     * @throws CoveConsultaException
     */
    public function consultarEdocument(string $eDocument, ?string $numeroAdenda = null): array
    {
        try {
            Log::info('[EDOCUMENT-V2] Iniciando consulta', [
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

            // Establecer headers de seguridad WS-Security
            $this->setSecurityHeader();

            // Generar firma electrónica
            Log::info('[EDOCUMENT-V2] Generando firma electrónica para eDocument: ' . $eDocument);
            $firmaData = $this->efirmaService->generarFirmaElectronica($eDocument, $this->rfc);

            // Construir XML manualmente con namespaces correctos
            $requestXml = $this->buildRequestXml($eDocument, $firmaData, $numeroAdenda);

            Log::info('[EDOCUMENT-V2] Request XML construido', [
                'xml_length' => strlen($requestXml)
            ]);

            // Crear SoapVar con el XML completo
            $soapVar = new SoapVar($requestXml, XSD_ANYXML);

            // Realizar llamada SOAP
            Log::info('[EDOCUMENT-V2] Enviando request SOAP');
            
            $response = $this->soapClient->__soapCall(
                'ConsultarEdocument',
                [$soapVar],
                [
                    'SOAPAction' => $this->soapAction,
                    'uri' => 'http://www.ventanillaunica.gob.mx/cove/ws/service/'
                ]
            );

            // Guardar información de debug
            $this->debugInfo = [
                'last_request' => $this->soapClient->__getLastRequest(),
                'last_response' => $this->soapClient->__getLastResponse(),
                'last_request_headers' => $this->soapClient->__getLastRequestHeaders(),
                'last_response_headers' => $this->soapClient->__getLastResponseHeaders()
            ];

            // Log para debug
            Log::info('[EDOCUMENT-V2] SOAP Request enviado', [
                'request' => $this->debugInfo['last_request']
            ]);
            Log::info('[EDOCUMENT-V2] SOAP Response recibido', [
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
     * Construye el XML del request con namespaces correctos
     */
    private function buildRequestXml(string $eDocument, array $firmaData, ?string $numeroAdenda): string
    {
        // Escapar valores XML
        $eDocumentEscaped = htmlspecialchars($eDocument, ENT_XML1, 'UTF-8');
        $certificado = htmlspecialchars($firmaData['certificado'], ENT_XML1, 'UTF-8');
        $cadenaOriginal = htmlspecialchars($firmaData['cadenaOriginal'], ENT_XML1, 'UTF-8');
        $firma = htmlspecialchars($firmaData['firma'], ENT_XML1, 'UTF-8');

        // Construir XML con estructura exacta según XSD
        $xml = <<<XML
<ns1:ConsultarEdocumentRequest xmlns:ns1="http://www.ventanillaunica.gob.mx/ConsultarEdocument/">
    <ns1:request>
        <oxml:firmaElectronica xmlns:oxml="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
            <oxml:certificado>{$certificado}</oxml:certificado>
            <oxml:cadenaOriginal>{$cadenaOriginal}</oxml:cadenaOriginal>
            <oxml:firma>{$firma}</oxml:firma>
        </oxml:firmaElectronica>
        <ns1:criterioBusqueda>
            <ns1:eDocument>{$eDocumentEscaped}</ns1:eDocument>
XML;

        // Agregar numeroAdenda si existe
        if ($numeroAdenda) {
            $numeroAdendaEscaped = htmlspecialchars($numeroAdenda, ENT_XML1, 'UTF-8');
            $xml .= "\n            <ns1:numeroAdenda>{$numeroAdendaEscaped}</ns1:numeroAdenda>";
        }

        $xml .= <<<XML

        </ns1:criterioBusqueda>
    </ns1:request>
</ns1:ConsultarEdocumentRequest>
XML;

        return $xml;
    }

    /**
     * Establecer header de seguridad WS-Security
     */
    private function setSecurityHeader(): void
    {
        $rfcEscaped = htmlspecialchars($this->rfc, ENT_XML1, 'UTF-8');
        $claveEscaped = htmlspecialchars($this->claveWebService, ENT_XML1, 'UTF-8');

        $securityXML = <<<XML
<wsse:Security xmlns:wsse="{$this::NS_WSSE}" xmlns:wsu="{$this::NS_WSU}">
    <wsse:UsernameToken wsu:Id="UsernameToken-1">
        <wsse:Username>{$rfcEscaped}</wsse:Username>
        <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">{$claveEscaped}</wsse:Password>
    </wsse:UsernameToken>
</wsse:Security>
XML;

        $securityVar = new SoapVar($securityXML, XSD_ANYXML);
        $securityHeader = new SoapHeader(self::NS_WSSE, 'Security', $securityVar);
        
        $this->soapClient->__setSoapHeaders([$securityHeader]);

        Log::info('[EDOCUMENT-V2] WS-Security header establecido', [
            'username' => $this->rfc
        ]);
    }

    /**
     * Procesar respuesta del webservice
     */
    private function processResponse($response, string $eDocument): array
    {
        Log::info('[EDOCUMENT-V2] Procesando respuesta', [
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
                    'automotriz' => $cove->automotriz ?? null,
                    'fechaExpedicion' => $cove->fechaExpedicion ?? null,
                    'tipoFigura' => $cove->tipoFigura ?? null,
                ];

                // Extraer emisor si existe
                if (isset($cove->emisor)) {
                    $result['cove_data']['emisor'] = [
                        'tipoIdentificador' => $cove->emisor->tipoIdentificador ?? null,
                        'identificacion' => $cove->emisor->identificacion ?? null,
                        'nombre' => $cove->emisor->nombre ?? null,
                    ];
                }

                // Extraer facturas si existen
                if (isset($cove->facturas) && isset($cove->facturas->factura)) {
                    $result['cove_data']['facturas'] = [];
                    $facturas = is_array($cove->facturas->factura) ? $cove->facturas->factura : [$cove->facturas->factura];
                    
                    foreach ($facturas as $factura) {
                        $result['cove_data']['facturas'][] = [
                            'numeroFactura' => $factura->numeroFactura ?? null,
                            'certificadoOrigen' => $factura->certificadoOrigen ?? null,
                        ];
                    }
                }
            }
        }

        Log::info('[EDOCUMENT-V2] Consulta exitosa', [
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
        
        Log::warning('[EDOCUMENT-V2] SOAP Fault', [
            'eDocument' => $eDocument,
            'fault_code' => $e->faultcode ?? 'Unknown',
            'fault_string' => $e->faultstring ?? $errorMessage
        ]);
        
        // Log request/response para debug
        Log::info('[EDOCUMENT-V2] SOAP Request (Error)', [
            'request' => $this->debugInfo['last_request']
        ]);
        Log::info('[EDOCUMENT-V2] SOAP Response (Error)', [
            'response' => $this->debugInfo['last_response']
        ]);

        return [
            'success' => false,
            'message' => $errorMessage,
            'error_type' => 'soap_fault',
            'eDocument' => $eDocument,
            'fault_code' => $e->faultcode ?? null,
            'fault_string' => $e->faultstring ?? null,
        ];
    }

    /**
     * Manejar errores de red/conexión
     */
    private function handleNetworkError(Exception $e, string $eDocument): array
    {
        Log::error('[EDOCUMENT-V2] Error de red/conexión', [
            'eDocument' => $eDocument,
            'error' => $e->getMessage(),
            'endpoint' => $this->endpoint
        ]);

        throw new CoveConsultaException($e->getMessage());
    }

    /**
     * Obtener información de debug
     */
    public function getDebugInfo(): array
    {
        return $this->debugInfo;
    }

    /**
     * Exportar XML del request para validación externa
     */
    public function exportRequestXml(string $eDocument, ?string $numeroAdenda = null): string
    {
        $firmaData = $this->efirmaService->generarFirmaElectronica($eDocument, $this->rfc);
        return $this->buildRequestXml($eDocument, $firmaData, $numeroAdenda);
    }
}
