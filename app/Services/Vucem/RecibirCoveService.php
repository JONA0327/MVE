<?php

namespace App\Services\Vucem;

use App\Exceptions\CoveConsultaException;
use SoapClient;
use SoapHeader;
use SoapFault;
use Exception;
use Illuminate\Support\Facades\Log;

class RecibirCoveService
{
    private SoapClient $client;
    private EFirmaService $efirmaService;

    public function __construct(EFirmaService $efirmaService)
    {
        $this->efirmaService = $efirmaService;
        $this->initSoapClient();
    }

    /**
     * Inicializa el cliente SOAP con el WSDL
     */
    private function initSoapClient(): void
    {
        try {
            $wsdl = base_path('wsdl/vucem/COVE/IngresarCOVE/RecibirCove.wsdl');
            
            if (!file_exists($wsdl)) {
                throw new Exception("WSDL no encontrado: {$wsdl}");
            }

            $endpoint = env('VUCEM_COVE_ENDPOINT', 'https://www2.ventanillaunica.gob.mx/ventanilla-procesamiento/RecibirCoveService');
            
            Log::info('[COVE] Inicializando SoapClient', [
                'wsdl' => $wsdl,
                'endpoint' => $endpoint
            ]);
            
            $this->client = new SoapClient($wsdl, [
                'trace' => true,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'soap_version' => SOAP_1_1,
                'connection_timeout' => 30,
                'user_agent' => 'Laravel-VUCEM-Client/1.0',
                'location' => $endpoint,
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ])
            ]);

        } catch (Exception $e) {
            throw new CoveConsultaException("Error inicializando cliente SOAP: " . $e->getMessage());
        }
    }

    /**
     * Genera un COVE a partir de datos de factura
     *
     * @param array $datosFactura
     * @return array
     * @throws CoveConsultaException
     */
    public function generarCove(array $datosFactura): array
    {
        // 🛡️ VALIDACIÓN DE SEGURIDAD: Prevenir generación accidental en producción
        if (!config('vucem.cove_recibir_enabled')) {
            throw new CoveConsultaException(
                "⚠️ SEGURIDAD: RecibirCove deshabilitado en este entorno.\n" .
                "Este servicio genera trámites REALES ante SAT.\n" . 
                "Para habilitar, configurar COVE_RECIBIR_ENABLED=true en .env"
            );
        }
        try {
            $rfc = config('vucem.rfc');
            if (!$rfc) {
                throw new CoveConsultaException("RFC no configurado");
            }

            // Construir cadena original según el formato COVE
            $cadenaOriginal = $this->construirCadenaOriginal($datosFactura);

            Log::info("[RECIBIR-COVE] Iniciando generación de COVE", [
                'cadena_original' => $cadenaOriginal,
                'rfc' => $rfc
            ]);

            // Generar firma electrónica
            $firmaData = $this->generarFirmaElectronica($cadenaOriginal, $rfc);

            // Armar el request SOAP para RecibirCove según la estructura del ejemplo
            $request = [
                'comprobantes' => [
                    'tipoOperacion' => $datosFactura['tipoOperacion'] ?? 'TOCE.IMP',
                    'patenteAduanal' => $datosFactura['patentesAduanales'] ?? [],
                    'fechaExpedicion' => $datosFactura['fechaExpedicion'] ?? date('Y-m-d'),
                    'observaciones' => $datosFactura['observaciones'] ?? 'Generación de COVE desde MVE',
                    'rfcConsulta' => $datosFactura['rfcsConsulta'] ?? [],
                    'tipoFigura' => $datosFactura['tipoFigura'] ?? '5',
                    'correoElectronico' => $datosFactura['correoElectronico'] ?? '',
                    'firmaElectronica' => [
                        'certificado' => $firmaData['certificado'],
                        'cadenaOriginal' => $firmaData['cadenaOriginal'],
                        'firma' => $firmaData['firma'],
                    ],
                    'numeroFacturaOriginal' => $datosFactura['numeroFacturaOriginal'] ?? '',
                    'factura' => [
                        'certificadoOrigen' => $datosFactura['certificadoOrigen'] ?? '1',
                        'numeroExportadorAutorizado' => $datosFactura['numeroExportadorAutorizado'] ?? '9984882',
                        'subdivision' => $datosFactura['subdivision'] ?? '0'
                    ],
                    'emisor' => $datosFactura['emisor'] ?? [],
                    'destinatario' => $datosFactura['destinatario'] ?? [],
                    'mercancias' => $datosFactura['mercancias'] ?? []
                ]
            ];

            // Configurar WS-Security header
            $this->setWSSecurityHeader($rfc);

            // Realizar llamada SOAP a RecibirCove
            $response = $this->client->__soapCall('RecibirCove', [$request]);

            Log::info("[RECIBIR-COVE] Respuesta recibida", [
                'response' => $response
            ]);

            // Parsear respuesta
            return $this->parseResponse($response);

        } catch (SoapFault $e) {
            $error = "Error SOAP: " . $e->getMessage();
            if (isset($e->detail)) {
                $error .= " - Detalle: " . print_r($e->detail, true);
            }
            
            Log::error("[RECIBIR-COVE] Error SOAP", [
                'error' => $error,
                'fault_code' => $e->faultcode ?? null,
                'fault_string' => $e->faultstring ?? null,
                'last_request' => $this->client->__getLastRequest(),
                'last_response' => $this->client->__getLastResponse()
            ]);
            
            throw new CoveConsultaException($e->faultstring ?? $error);
            
        } catch (CoveConsultaException $e) {
            throw $e;
            
        } catch (Exception $e) {
            $error = "Error en generación de COVE: " . $e->getMessage();
            Log::error("[RECIBIR-COVE] Error general", [
                'error' => $error,
                'last_request' => $this->client->__getLastRequest() ?? null,
                'last_response' => $this->client->__getLastResponse() ?? null
            ]);
            throw new CoveConsultaException($error);
        }
    }

    /**
     * Construye la cadena original según el formato COVE
     * Ejemplo: |TOCE.IMP|ITUTUET66545|0|2011-11-15|5|observaciones|RFC1|RFC2|patente1|patente2|...|
     */
    private function construirCadenaOriginal(array $datos): string
    {
        $elementos = [
            $datos['tipoOperacion'] ?? 'TOCE.IMP',
            $datos['numeroFacturaOriginal'] ?? '',
            $datos['certificadoOrigen'] ?? '0',
            $datos['fechaExpedicion'] ?? date('Y-m-d'),
            $datos['tipoFigura'] ?? '5',
            $datos['observaciones'] ?? 'Prueba del webservice de Cove',
        ];

        // Agregar RFCs de consulta
        if (!empty($datos['rfcsConsulta'])) {
            $elementos = array_merge($elementos, $datos['rfcsConsulta']);
        }

        // Agregar patentes aduanales
        if (!empty($datos['patentesAduanales'])) {
            $elementos = array_merge($elementos, $datos['patentesAduanales']);
        }

        // Agregar datos adicionales según el tipo
        if (!empty($datos['datosAdicionales'])) {
            $elementos = array_merge($elementos, $datos['datosAdicionales']);
        }

        return '|' . implode('|', $elementos) . '|';
    }

    /**
     * Genera la firma electrónica para la cadena original
     */
    private function generarFirmaElectronica(string $cadenaOriginal, string $rfc): array
    {
        // Usar el servicio de e.firma existente adaptando los parámetros
        return [
            'certificado' => $this->efirmaService->getCertificadoBase64(),
            'cadenaOriginal' => $cadenaOriginal,
            'firma' => $this->efirmaService->firmarCadenaBase64($cadenaOriginal)
        ];
    }

    /**
     * Configura el header WS-Security con UsernameToken
     */
    private function setWSSecurityHeader(string $username): void
    {
        // Primero intentar obtener password del .env (para pruebas)
        $password = env('VUCEM_WS_PASSWORD');
        
        // Si no está en .env, obtener del usuario autenticado
        if (!$password) {
            $user = auth()->user();
            if (!$user) {
                throw new CoveConsultaException("Usuario no autenticado y password no configurado en .env");
            }
            
            $password = $user->webservice_key;
            if (!$password) {
                throw new CoveConsultaException("Usuario no tiene clave de webservice configurada");
            }
        }

        // Namespace WS-Security
        $wssNS = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
        $wsuNS = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';

        // Crear timestamp
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $expires = gmdate('Y-m-d\TH:i:s\Z', time() + 300); // 5 minutos

        // Construir header XML
        $headerXML = "<wsse:Security xmlns:wsse=\"{$wssNS}\" xmlns:wsu=\"{$wsuNS}\">
            <wsu:Timestamp wsu:Id=\"TS-1\">
                <wsu:Created>{$timestamp}</wsu:Created>
                <wsu:Expires>{$expires}</wsu:Expires>
            </wsu:Timestamp>
            <wsse:UsernameToken wsu:Id=\"UsernameToken-1\">
                <wsse:Username>{$username}</wsse:Username>
                <wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">{$password}</wsse:Password>
            </wsse:UsernameToken>
        </wsse:Security>";

        $header = new SoapHeader($wssNS, 'Security', new \SoapVar($headerXML, XSD_ANYXML), true);
        $this->client->__setSoapHeaders([$header]);
    }

    /**
     * Parsea la respuesta SOAP
     */
    private function parseResponse(object $response): array
    {
        $result = [
            'success' => false,
            'cove' => null,
            'message' => '',
            'errors' => []
        ];

        // Procesar respuesta según estructura de RecibirCove
        if (isset($response->respuesta)) {
            $result['success'] = true;
            $result['message'] = 'COVE generado exitosamente';
            
            // Extraer folio COVE si está disponible
            if (isset($response->respuesta->cove)) {
                $result['cove'] = (string) $response->respuesta->cove;
            }
        }

        return $result;
    }

    /**
     * Obtiene información de debug del último request/response SOAP
     */
    public function getDebugInfo(): array
    {
        return [
            'last_request' => $this->client->__getLastRequest() ?? null,
            'last_response' => $this->client->__getLastResponse() ?? null,
            'last_request_headers' => $this->client->__getLastRequestHeaders() ?? null,
            'last_response_headers' => $this->client->__getLastResponseHeaders() ?? null,
        ];
    }
}