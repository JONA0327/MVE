# Servicio ConsultarRespuestaCove - Documentación

## Descripción General

El servicio `ConsultarRespuestaCoveService` permite consultar los datos estructurados de un COVE (Constancia de Operaciones de Valor en Efectivo) previamente enviado a VUCEM.

**Diferencia con ConsultaAcuses:**
- `ConsultaAcusesService`: Descarga el PDF del acuse (tanto para eDocument como COVE)
- `ConsultarRespuestaCoveService`: Obtiene datos estructurados del COVE (facturas, errores, sello digital, etc.)

## Endpoint

```
URL: https://www.ventanillaunica.gob.mx:8110/ventanilla/ConsultarRespuestaCoveService
WSDL: https://www.ventanillaunica.gob.mx:8110/ventanilla/ConsultarRespuestaCoveService?wsdl
Puerto: 8110 (específico para este servicio)
SOAPAction: http://www.ventanillaunica.gob.mx/ConsultarRespuestaCove
```

## Estructura de la Petición

### Envelope SOAP
```xml
<soapenv:Envelope 
    xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:ser="http://www.ventanillaunica.gob.mx/cove/ws/service/"
    xmlns:oxml="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
    
    <soapenv:Header>
        <wsse:Security soapenv:mustUnderstand="1" 
            xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <wsse:UsernameToken>
                <wsse:Username>RFC_DEL_USUARIO</wsse:Username>
                <wsse:Password Type="...#PasswordText">CLAVE_WEBSERVICE</wsse:Password>
            </wsse:UsernameToken>
        </wsse:Security>
    </soapenv:Header>
    
    <soapenv:Body>
        <oxml:solicitarConsultarRespuestaCoveServicio>
            <numeroOperacion>1234567890</numeroOperacion>
            <firmaElectronica>
                <certificado>BASE64_CERTIFICADO</certificado>
                <cadenaOriginal>|1234567890|RFC_USUARIO|</cadenaOriginal>
                <firma>BASE64_FIRMA_DIGITAL</firma>
            </firmaElectronica>
        </oxml:solicitarConsultarRespuestaCoveServicio>
    </soapenv:Body>
</soapenv:Envelope>
```

### Parámetros Requeridos

1. **WS-Security Header:**
   - `Username`: RFC del usuario registrado en VUCEM
   - `Password`: Clave de webservice (64 caracteres)

2. **Body:**
   - `numeroOperacion`: Número de operación asignado al enviar el COVE
   - `firmaElectronica`:
     - `certificado`: Certificado e.firma en Base64
     - `cadenaOriginal`: `|numeroOperacion|RFC|` (con pipes)
     - `firma`: Firma digital de la cadenaOriginal con llave privada e.firma

## Estructura de la Respuesta

### XML de Respuesta
```xml
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <ns2:solicitarConsultarRespuestaCoveServicioResponse 
            xmlns:ns2="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
            <return>
                <numeroOperacion>1234567890</numeroOperacion>
                <horaRecepcion>2025-12-29T10:30:00</horaRecepcion>
                <respuestasOperaciones>
                    <numeroFacturaORelacionFacturas>FACTURA001</numeroFacturaORelacionFacturas>
                    <contieneError>false</contieneError>
                    <eDocument>0170220LIS5D4</eDocument>
                    <numeroAdenda>12345</numeroAdenda>
                    <errores/>
                    <cadenaOriginal>||01702251RTAD3|COVE2411FXFM4...</cadenaOriginal>
                    <selloDigital>aGVsbG8gd29ybGQ=...</selloDigital>
                </respuestasOperaciones>
                <!-- Más respuestasOperaciones si hay múltiples facturas -->
            </return>
        </ns2:solicitarConsultarRespuestaCoveServicioResponse>
    </soap:Body>
</soap:Envelope>
```

### Estructura de Datos (Array PHP)
```php
[
    'success' => true,
    'numeroOperacion' => 1234567890,
    'horaRecepcion' => '2025-12-29T10:30:00',
    'respuestasOperaciones' => [
        [
            'numeroFacturaORelacionFacturas' => 'FACTURA001',
            'contieneError' => false,
            'eDocument' => '0170220LIS5D4',
            'numeroAdenda' => '12345',
            'errores' => [],
            'cadenaOriginal' => '||01702251RTAD3|COVE2411FXFM4...',
            'selloDigital' => 'aGVsbG8gd29ybGQ=...'
        ],
        // ... más operaciones
    ],
    'leyenda' => 'Mensaje opcional del servicio',
    'raw_response' => '<?xml version="1.0"...' // XML completo
]
```

## Uso del Servicio

### Desde un Controlador
```php
use App\Services\Vucem\ConsultarRespuestaCoveService;

public function consultarCove(Request $request)
{
    $numeroOperacion = $request->input('numero_operacion');
    $user = Auth::user();
    
    $service = new ConsultarRespuestaCoveService($user);
    $resultado = $service->consultarRespuesta($numeroOperacion);
    
    if ($resultado['success']) {
        return response()->json([
            'success' => true,
            'data' => $resultado
        ]);
    } else {
        return response()->json([
            'success' => false,
            'message' => $resultado['message']
        ], 400);
    }
}
```

### Desde Artisan Command
```bash
# Probar con número de operación
php artisan vucem:test-consultar-cove 1234567890

# Con usuario específico
php artisan vucem:test-consultar-cove 1234567890 --user=2

# Con salida verbose (muestra XML completo)
php artisan vucem:test-consultar-cove 1234567890 -v
```

### Desde Tinker
```php
php artisan tinker

$user = \App\Models\User::find(1);
$service = new \App\Services\Vucem\ConsultarRespuestaCoveService($user);
$resultado = $service->consultarRespuesta(1234567890);
print_r($resultado);
```

## Campos de Respuesta

### RespuestaOperacion
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `numeroFacturaORelacionFacturas` | string | Número de factura o relación de facturas |
| `contieneError` | boolean | `true` si hay errores, `false` si todo OK |
| `eDocument` | string | Folio del eDocument generado (ej: `0170220LIS5D4`) |
| `numeroAdenda` | string | Número de adenda si aplica |
| `errores` | array | Lista de mensajes de error (vacío si no hay errores) |
| `cadenaOriginal` | string | Cadena original del sello digital |
| `selloDigital` | string | Sello digital en Base64 |

### Casos de Error
- `contieneError: true` → Revisar el array `errores[]`
- `eDocument` vacío → No se generó eDocument (posible error)
- HTTP 500 → Error del servidor VUCEM
- SOAP Fault → Error en la petición o credenciales

## Requisitos de Configuración

### 1. Usuario en Base de Datos
```php
// Tabla: users
$user->rfc = 'NET070608EM9'; // RFC registrado en VUCEM
$user->webservice_key = 'clave_de_64_caracteres'; // Encriptada
$user->webservice_user = 'NET070608EM9'; // Opcional, por defecto usa el RFC
```

### 2. Archivos e.firma
```
pruebaEfirma/
├── 00001000000716248795.cer              (Certificado)
├── Claveprivada_FIEL_NET070608EM9_*.key  (Llave privada)
└── CONTRASEÑA.txt                         (Contraseña en texto plano)
```

### 3. Configuración en config/vucem.php
```php
'consultar_respuesta_cove' => [
    'endpoint' => 'https://www.ventanillaunica.gob.mx:8110/ventanilla/ConsultarRespuestaCoveService',
    'soap_action' => 'http://www.ventanillaunica.gob.mx/ConsultarRespuestaCove',
    'soap_version' => SOAP_1_1,
    'connection_timeout' => 30,
],

'efirma' => [
    'path' => 'pruebaEfirma',
    'cert_file' => '00001000000716248795.cer',
    'key_file' => 'Claveprivada_FIEL_NET070608EM9_20250604_163343.key',
    'password_file' => 'CONTRASEÑA.txt',
],
```

## Diferencias con ConsultaAcuses

| Característica | ConsultaAcuses | ConsultarRespuestaCove |
|----------------|----------------|------------------------|
| **Propósito** | Descargar PDF del acuse | Obtener datos estructurados |
| **Puerto** | Puerto estándar (443) | Puerto 8110 |
| **Entrada** | Folio eDocument o COVE | Número de operación |
| **Salida** | PDF binario (MTOM) | XML con datos estructurados |
| **e.firma** | No requerida | Requerida (firma digital) |
| **Namespace** | `/consulta/acuses/oxml` | `/cove/ws/oxml/` |
| **Casos de uso** | Guardar acuse como archivo | Mostrar datos en UI, validación |

## Errores Comunes

### 1. "Error generando firma electrónica"
**Causa:** Archivos e.firma incorrectos o contraseña inválida
**Solución:**
- Verificar que los archivos .cer y .key existen
- Verificar que CONTRASEÑA.txt tiene la contraseña correcta
- Verificar que el certificado no ha expirado

### 2. "SOAP Fault: Unauthorized"
**Causa:** Credenciales incorrectas en WS-Security
**Solución:**
- Verificar RFC del usuario
- Verificar que webservice_key tiene 64 caracteres
- Verificar que el RFC está registrado en VUCEM

### 3. "Número de operación no encontrado"
**Causa:** El numeroOperacion no existe o no pertenece al RFC
**Solución:**
- Verificar que el COVE fue enviado exitosamente
- Verificar que el numeroOperacion es correcto
- Verificar que el RFC del usuario es el mismo que envió el COVE

### 4. "HTTP 500 Internal Server Error"
**Causa:** Error del servidor VUCEM
**Solución:**
- Revisar los logs de Laravel para ver el XML enviado
- Verificar que la estructura XML es correcta
- Verificar que la firma electrónica es válida
- Reintentar después de algunos minutos

## Logs

El servicio genera logs detallados en `storage/logs/laravel.log`:

```
[CONSULTAR-RESPUESTA-COVE] Iniciando consulta
  numero_operacion: 1234567890
  rfc: NET070608EM9
  endpoint: https://www.ventanillaunica.gob.mx:8110/...

[E-FIRMA] Iniciando generación de firma con cadena raw
  cadena_original: |1234567890|NET070608EM9|
  
[E-FIRMA] Firma generada exitosamente
  certificado_length: 1234
  firma_length: 344

[CONSULTAR-RESPUESTA-COVE] Enviando request SOAP
[CONSULTAR-RESPUESTA-COVE] Respuesta recibida
[CONSULTAR-RESPUESTA-COVE] Respuesta procesada exitosamente
```

## Archivos Relacionados

```
app/
└── Services/
    └── Vucem/
        ├── ConsultarRespuestaCoveService.php  (Servicio principal)
        └── EFirmaService.php                   (Firma electrónica)

app/Console/Commands/
└── TestConsultarRespuestaCoveCommand.php      (Comando de prueba)

config/
└── vucem.php                                   (Configuración)

wsdl/vucem/COVE/
├── ConsultarRespuestaCoveService.wsdl         (WSDL del servicio)
└── ConsultarRespuestaCove_xsd1.xsd            (Esquemas XSD)

docs/
├── CONSULTAR_RESPUESTA_COVE.md                (Esta documentación)
├── COVE_DOCUMENTATION.md                       (Documentación general COVE)
└── ACUSES_INTEGRATION.md                       (Documentación ConsultaAcuses)
```

## Ejemplo Completo

```php
<?php

use App\Services\Vucem\ConsultarRespuestaCoveService;
use Illuminate\Support\Facades\Auth;

class CoveController extends Controller
{
    public function consultarRespuesta(Request $request)
    {
        // Validar entrada
        $request->validate([
            'numero_operacion' => 'required|integer|min:1'
        ]);

        $numeroOperacion = $request->input('numero_operacion');
        $user = Auth::user();

        try {
            // Crear servicio con el usuario autenticado
            $service = new ConsultarRespuestaCoveService($user);
            
            // Ejecutar consulta
            $resultado = $service->consultarRespuesta($numeroOperacion);

            // Verificar éxito
            if (!$resultado['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $resultado['message'],
                    'error_type' => $resultado['error_type'] ?? 'unknown'
                ], 400);
            }

            // Procesar respuestas
            $respuestas = $resultado['respuestasOperaciones'] ?? [];
            $tieneErrores = false;
            $erroresDetalle = [];

            foreach ($respuestas as $resp) {
                if ($resp['contieneError']) {
                    $tieneErrores = true;
                    $erroresDetalle = array_merge($erroresDetalle, $resp['errores']);
                }
            }

            // Retornar resultado
            return response()->json([
                'success' => true,
                'numeroOperacion' => $resultado['numeroOperacion'],
                'horaRecepcion' => $resultado['horaRecepcion'],
                'tieneErrores' => $tieneErrores,
                'errores' => $erroresDetalle,
                'respuestas' => $respuestas,
                'leyenda' => $resultado['leyenda'] ?? null
            ]);

        } catch (\Exception $e) {
            \Log::error('Error consultando COVE', [
                'numero_operacion' => $numeroOperacion,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al consultar COVE: ' . $e->getMessage()
            ], 500);
        }
    }
}
```

## Resumen

✅ **Implementado:**
- Servicio ConsultarRespuestaCoveService con firma electrónica
- Comando Artisan de prueba
- Configuración en vucem.php
- Manejo de errores y validaciones
- Logs detallados

📋 **Pendiente:**
- Pruebas con número de operación real
- Integración en ManifestationController
- Ruta web/API para acceso desde frontend
- Caché de respuestas (opcional)
- Tests unitarios

---
**Última actualización:** 29 de diciembre de 2025
