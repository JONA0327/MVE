<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración VUCEM (Ventanilla Única de Comercio Exterior)
    |--------------------------------------------------------------------------
    |
    | Configuración para los servicios web de VUCEM.
    | Configuración para ambiente de PRUEBAS.
    |
    | IMPORTANTE: Las credenciales (RFC y clave webservice) se obtienen 
    | automáticamente del perfil del usuario autenticado, NO del .env
    |
    */

    /*
    |--------------------------------------------------------------------------
    | SEGURIDAD COVE - Control de Generación
    |--------------------------------------------------------------------------
    |
    | ⚠️  PELIGRO: RecibirCove genera trámites REALES ante SAT
    | ✅  SEGURO:  ConsultarRespuestaCove solo consulta
    |
    | Deshabilita RecibirCove en producción para evitar generar 
    | trámites no deseados.
    |
    */
    'cove_recibir_enabled' => env('COVE_RECIBIR_ENABLED', false),

    // RFC para el sello digital (RFC de prueba oficial SAT)
    'rfc' => env('VUCEM_RFC', 'GWT921026L97'),

    /*
    |--------------------------------------------------------------------------
    | Configuración del Servicio ConsultarEdocument  
    |--------------------------------------------------------------------------
    */
    'edocument' => [
        'endpoint' => env('VUCEM_EDOCUMENT_ENDPOINT', 'https://www.ventanillaunica.gob.mx/ventanilla/ConsultarEdocument'),
        'soap_action' => env('VUCEM_EDOCUMENT_ACTION', 'http://www.ventanillaunica.gob.mx/cove/ws/service/ConsultarEdocument'),
        'wsdl_path' => base_path('wsdl/vucem/COVE/edocument/ConsultarEdocument.wsdl'),
        
        // Configuración SOAP para ambiente de pruebas
        'soap_version' => SOAP_1_1,
        'connection_timeout' => 30,
        'trace' => true,
        'exceptions' => true,
        'cache_wsdl' => WSDL_CACHE_NONE,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración del Servicio ConsultarRespuestaCove  
    |--------------------------------------------------------------------------
    */
    'consultar_cove' => [
        'endpoint' => env('VUCEM_CONSULTAR_COVE_ENDPOINT', 'https://www.ventanillaunica.gob.mx/ventanilla/ConsultarRespuestaCoveService'),
        'soap_action' => env('VUCEM_CONSULTAR_COVE_ACTION', 'http://www.ventanillaunica.gob.mx/ConsultarRespuestaCove'),
        'wsdl_path' => base_path('wsdl/vucem/COVE/ConsultarCOVE/ConsultarRespuestaCove.wsdl'),
        
        // Configuración SOAP para ambiente de pruebas
        'timeout' => env('VUCEM_SOAP_TIMEOUT', 8), // Timeout reducido
        'user_agent' => 'MVE-Laravel-SOAP-Client/1.0',
        'soap_version' => 1, // SOAP_1_1
        'connection_timeout' => 8, // Timeout de conexión reducido
        
        // Cache WSDL deshabilitado para pruebas
        'cache_wsdl' => 0, // WSDL_CACHE_NONE
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Seguridad y Logging
    |--------------------------------------------------------------------------
    */
    'security' => [
        // Namespace para WS-Security
        'ws_security_namespace' => 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd',
        
        // Tipo de password para UsernameToken
        'password_type' => 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText',
    ],

    'logging' => [
        // Log detallado de requests/responses SOAP (habilitado para pruebas)
        'log_soap_requests' => env('VUCEM_LOG_SOAP', true),
        
        // Log de errores siempre habilitado
        'log_errors' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | URLs de Servicios VUCEM por ambiente
    |--------------------------------------------------------------------------
    */
    'endpoints' => [
        'production' => [
            'consultar_cove' => 'https://www.ventanillaunica.gob.mx/ConsultarRespuestaCoveService',
        ],
        'testing' => [
            'consultar_cove' => 'https://www2.ventanillaunica.gob.mx/ventanilla/ConsultarRespuestaCoveService',
        ],
        'development' => [
            'consultar_cove' => 'http://localhost:8080/mock/ConsultarRespuestaCoveService',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración e.firma (Firma Electrónica)
    |--------------------------------------------------------------------------
    */
    'efirma' => [
        // Ruta relativa donde están los archivos de e.firma
        'path' => env('E_FIRMA_PATH', 'pruebaEfirma'),
        
        // Contraseña desde archivo (más seguro que variable de entorno)
        'password_file' => 'CONTRASEÑA.txt',
        
        // Nombres de archivos específicos para NET070608EM9
        'cert_file' => '00001000000716248795.cer',
        'key_file' => 'Claveprivada_FIEL_NET070608EM9_20250604_163343.key',
        
        // Configuración de firma
        'signature_algorithm' => OPENSSL_ALGO_SHA256,
    ],
];