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
    | Configuración del Servicio ConsultarRespuestaCove
    |--------------------------------------------------------------------------
    */
    'consultar_cove' => [
        'endpoint' => env('VUCEM_COVE_ENDPOINT', 'https://www2.ventanillaunica.gob.mx/ventanilla/ConsultarRespuestaCoveService'),
        'soap_action' => env('VUCEM_CONSULTAR_COVE_ACTION', 'http://www.ventanillaunica.gob.mx/ConsultarRespuestaCove'),
        'wsdl_path' => base_path('wsdl/vucem/ConsultarRespuestaCove.wsdl'),
        
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
];