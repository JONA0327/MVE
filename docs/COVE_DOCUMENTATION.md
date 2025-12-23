# Documentación COVE - Consulta de Comprobante de Valor Electrónico

Este documento explica la implementación del sistema de consulta COVE (Comprobante de Valor Electrónico) integrado con los servicios web SOAP de VUCEM (Ventanilla Única de Comercio Exterior).

## Configuración

### 1. Variables de Entorno

Agrega las siguientes variables a tu archivo `.env`:

```bash
# Configuración VUCEM - ConsultarRespuestaCove
VUCEM_CONSULTAR_COVE_ENDPOINT=http://www.ventanillaunica.gob.mx/ConsultarRespuestaCoveService
VUCEM_CONSULTAR_COVE_ACTION=http://www.ventanillaunica.gob.mx/ConsultarRespuestaCove
VUCEM_SOAP_TIMEOUT=30
VUCEM_LOG_SOAP=false

# Las credenciales NO van en .env, se obtienen del perfil de usuario
# Los usuarios deben tener configurados en su perfil:
# - rfc (campo encriptado)
# - webservice_key (campo encriptado - clave del webservice VUCEM)
```

### 2. Perfil de Usuario

Cada usuario debe tener configurado en su perfil:

- **RFC**: RFC del solicitante (campo encriptado en la base de datos)
- **Clave de Webservice**: Clave proporcionada por VUCEM para acceso a webservices (campo encriptado)

### 3. Archivo WSDL

El sistema requiere el archivo WSDL del servicio VUCEM. Colócalo en:

```
wsdl/vucem/ConsultarRespuestaCove.wsdl
```

## Uso del Sistema

### Endpoint API

```http
GET /api/cove/{folio}
```

**Parámetros:**
- `folio`: Folio del COVE (15 dígitos)

**Respuesta exitosa:**
```json
{
    "success": true,
    "data": {
        "folio": "123456789012345",
        "numero": "COVE-001",
        "estatus": "Válido",
        "fecha_emision": "2024-01-15",
        "rfc_solicitante": "RFC123456789",
        "tipo_solicitud": "Importación",
        "valor_dolares": 1500.00,
        "vigencia": "30 días"
    }
}
```

**Respuesta de error:**
```json
{
    "success": false,
    "error": {
        "type": "soap_fault",
        "code": "INVALID_FOLIO",
        "message": "El folio especificado no es válido",
        "debug_info": {
            "soap_code": "Client",
            "detail": "Folio debe ser numérico de 15 dígitos"
        }
    }
}
```

### Integración Frontend

El sistema incluye integración JavaScript para consultas desde el frontend:

```javascript
// Función de consulta COVE
async function consultarCove(folio) {
    try {
        const response = await fetch(`/api/cove/${folio}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();
        
        if (data.success) {
            // Manejar datos del COVE
            console.log('COVE encontrado:', data.data);
        } else {
            // Mostrar error en modal
            mostrarErrorModal(data.error);
        }
    } catch (error) {
        console.error('Error en consulta COVE:', error);
    }
}
```

## Tipos de Error

El sistema maneja los siguientes tipos de error:

### 1. `auth_error`
Usuario no autenticado en el sistema.

### 2. `profile_incomplete` 
Usuario no tiene RFC o clave de webservice configurados en su perfil.

### 3. `invalid_folio`
El folio proporcionado no es válido (debe ser numérico de 15 dígitos).

### 4. `soap_fault`
Error del servicio web VUCEM (servicio no disponible, credenciales incorrectas, etc.).

### 5. `not_found`
COVE no encontrado en VUCEM.

### 6. `connection_error`
Error de conexión con los servicios de VUCEM.

## Arquitectura del Sistema

### Componentes Principales

1. **ConsultarCoveService** (`app/Services/ConsultarCoveService.php`)
   - Cliente SOAP para VUCEM
   - Implementa autenticación WS-Security UsernameToken
   - Manejo de errores y logging

2. **CoveController** (`app/Http/Controllers/CoveController.php`)
   - API REST para consultas COVE
   - Validación de parámetros
   - Transformación de respuestas

3. **Frontend Integration** (`resources/views/step1.blade.php`)
   - JavaScript para consultas asíncronas
   - Sistema de modales para errores
   - Integración con AlpineJS

### Autenticación VUCEM

El sistema utiliza autenticación WS-Security UsernameToken:

```xml
<wsse:Security>
    <wsse:UsernameToken>
        <wsse:Username>{RFC_USUARIO}</wsse:Username>
        <wsse:Password Type="PasswordText">{CLAVE_WEBSERVICE}</wsse:Password>
    </wsse:UsernameToken>
</wsse:Security>
```

Las credenciales se obtienen automáticamente del perfil del usuario autenticado:
- RFC: Campo encriptado `rfc` del usuario
- Clave: Campo encriptado `webservice_key` del usuario

## Testing

### Comando de Prueba

```bash
php artisan test:cove-configuration --user=1
```

Este comando:
- Autentica como el usuario especificado
- Valida su perfil (RFC y clave de webservice)
- Prueba la conectividad con VUCEM
- Realiza una consulta de prueba

### Tests Automatizados

```bash
# Tests unitarios del servicio
php artisan test tests/Unit/ConsultarCoveServiceTest.php

# Tests de integración del API
php artisan test tests/Feature/CoveApiTest.php
```

## Troubleshooting

### Problemas Comunes

1. **Error "Profile Incomplete"**
   - Verificar que el usuario tenga RFC configurado
   - Verificar que el usuario tenga webservice_key configurado
   - Ambos campos deben estar encriptados en la BD

2. **Error de Autenticación VUCEM**
   - Verificar que el RFC sea correcto
   - Verificar que la clave de webservice sea válida
   - Contactar a VUCEM si persiste el error

3. **Error de Conexión**
   - Verificar conectividad a internet
   - Verificar que el endpoint VUCEM esté disponible
   - Revisar configuración de proxy si aplica

### Logging

Los logs del sistema se encuentran en:
- `storage/logs/laravel.log` (errores generales)
- Búsqueda por: `[COVE]` para filtrar logs del sistema COVE

### Configuración de Desarrollo

Para desarrollo local, puedes usar endpoints de prueba:

```bash
# En .env para desarrollo
VUCEM_CONSULTAR_COVE_ENDPOINT=http://localhost:8080/mock/ConsultarRespuestaCoveService
```

## Seguridad

### Encriptación de Credenciales

Las credenciales de VUCEM se almacenan encriptadas en la base de datos:

```php
// En el modelo User
protected $casts = [
    'rfc' => 'encrypted',
    'webservice_key' => 'encrypted',
];
```

### Validación de Entrada

Todos los folios se validan:
- Longitud exacta de 15 dígitos
- Solo caracteres numéricos
- Sanitización de entrada

### Rate Limiting

Se recomienda implementar rate limiting para el endpoint API:

```php
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/api/cove/{folio}', [CoveController::class, 'showByFolio']);
});
```