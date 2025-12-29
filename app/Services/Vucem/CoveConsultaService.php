<?php

namespace App\Services\Vucem;

use App\Exceptions\CoveConsultaException;
use SoapClient;
use SoapHeader;
use SoapFault;
use Exception;
use Illuminate\Support\Facades\Log;

class CoveConsultaService
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
            $wsdl = base_path('wsdl/vucem/COVE/ConsultarRespuestaCove.wsdl');
            
            if (!file_exists($wsdl)) {
                throw new Exception("WSDL no encontrado: {$wsdl}");
            }

            // CORRECCIÓN (Opción A): Usar la configuración centralizada.
            // Esto asegura que tome 'VUCEM_CONSULTAR_COVE_ENDPOINT' definido en config/vucem.php
            $endpoint = config('vucem.consultar_cove.endpoint');
            
            $this->client = new SoapClient($wsdl, [
                'trace' => true,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'soap_version' => SOAP_1_1,
                'connection_timeout' => 30,
                'user_agent' => 'Laravel-VUCEM-Client/1.0',
                'location' => $endpoint, // Sobrescribir endpoint del WSDL con la URL correcta
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
     * Consulta COVE por número de operación
     *
     * @param int $numeroOperacion
     * @return array
     * @throws CoveConsultaException
     */
    public function consultarPorNumeroOperacion(int $numeroOperacion): array
    {
        try {
            // Obtener RFC del sello desde configuración
            $rfc = config('vucem.rfc') ?? env('VUCEM_RFC');
            if (!$rfc) {
                throw new CoveConsultaException("RFC no configurado (VUCEM_RFC)");
            }

            // Construir cadena original
            $cadenaOriginal = "|{$numeroOperacion}|{$rfc}|";

            Log::info("[COVE-CONSULTA] Iniciando consulta", [
                'numero_operacion' => $numeroOperacion,
                'rfc' => $rfc,
                'cadena_original' => $cadenaOriginal
            ]);

            // Generar firma electrónica
            $firmaData = $this->efirmaService->generarFirmaElectronica($numeroOperacion, $rfc);

            // Armar el request SOAP exactamente como se especifica
            $request = [
                'numeroOperacion' => $numeroOperacion,
                'firmaElectronica' => [
                    'certificado' => $firmaData['certificado'],
                    'cadenaOriginal' => $firmaData['cadenaOriginal'],
                    'firma' => $firmaData['firma'],
                ],
            ];

            // Configurar WS-Security header con UsernameToken
            $this->setWSSecurityHeader($rfc);

            // Realizar llamada SOAP
            $response = $this->client->__soapCall('ConsultarRespuestaCove', [$request]);

            Log::info("[COVE-CONSULTA] Respuesta recibida", [
                'response' => $response
            ]);

            // Parsear respuesta
            return $this->parseResponse($response);

        } catch (SoapFault $e) {
            $error = "Error SOAP: " . $e->getMessage();
            if (isset($e->detail)) {
                $error .= " - Detalle: " . print_r($e->detail, true);
            }
            
            Log::error("[COVE-CONSULTA] Error SOAP", [
                'error' => $error,
                'fault_code' => $e->faultcode ?? null,
                'fault_string' => $e->faultstring ?? null
            ]);
            
            throw new CoveConsultaException($e->faultstring ?? $error);
            
        } catch (CoveConsultaException $e) {
            throw $e;
            
        } catch (Exception $e) {
            $error = "Error en consulta COVE: " . $e->getMessage();
            Log::error("[COVE-CONSULTA] Error general", ['error' => $error]);
            throw new CoveConsultaException($error);
        }
    }

    /**
     * Configura el header WS-Security con UsernameToken
     *
     * @param string $username
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
     * Parsea la respuesta SOAP en el formato simplificado especificado
     *
     * @param object $response
     * @return array
     */
    private function parseResponse(object $response): array
    {
        $result = [
            'numeroOperacion' => null,
            'horaRecepcion' => null,
            'leyenda' => null,
            'resultados' => []
        ];

        // Mapear datos básicos de la respuesta
        if (isset($response->numeroOperacion)) {
            $result['numeroOperacion'] = (int) $response->numeroOperacion;
        }

        if (isset($response->horaRecepcion)) {
            $result['horaRecepcion'] = (string) $response->horaRecepcion;
        }

        if (isset($response->leyenda)) {
            $result['leyenda'] = (string) $response->leyenda;
        }

        // Procesar respuestas de operaciones
        if (isset($response->respuestasOperaciones)) {
            $operaciones = is_array($response->respuestasOperaciones) 
                ? $response->respuestasOperaciones 
                : [$response->respuestasOperaciones];

            foreach ($operaciones as $operacion) {
                $resultado = [
                    'numeroFactura' => (string) ($operacion->numeroFacturaORelacionFacturas ?? ''),
                    'contieneError' => (bool) ($operacion->contieneError ?? false),
                    'eDocument' => isset($operacion->eDocument) ? (string) $operacion->eDocument : null,
                    'errores' => []
                ];

                // Procesar errores
                if (isset($operacion->errores)) {
                    $errores = isset($operacion->errores->mensaje) 
                        ? (is_array($operacion->errores->mensaje) 
                            ? $operacion->errores->mensaje 
                            : [$operacion->errores->mensaje])
                        : [];

                    $resultado['errores'] = array_map('strval', $errores);
                }

                $result['resultados'][] = $resultado;
            }
        }

        return $result;
    }

    /**
     * Obtiene información de debug del último request/response SOAP
     *
     * @return array
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