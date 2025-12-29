# Servicio de Consulta de Acuses VUCEM

## Descripción

El servicio `ConsultaAcusesService` permite descargar acuses desde VUCEM utilizando el Web Service:

```
https://www.ventanillaunica.gob.mx/ventanilla-acuses-HA/ConsultaAcusesServiceWS
```

**Característica importante:** El mismo servicio maneja automáticamente dos tipos de acuses según el formato del folio:

- **Acuse de eDocument**: Si envías un folio formato eDocument (ej: `0170220LIS5D4`)
- **Acuse de COVE (Acuse de Valor)**: Si envías un folio formato COVE (ej: `COVE214KNPVU4`)

## Requisitos

### Configuración en `.env`

```env
VUCEM_EDOCUMENT_ENDPOINT=https://www.ventanillaunica.gob.mx/ventanilla-acuses-HA/ConsultaAcusesServiceWS
```

### Credenciales del Usuario

El usuario debe tener:
- **RFC** (usado como username en WS-Security)
- **webservice_key** (contraseña de 64 caracteres)

Si el campo `webservice_user` está vacío, el sistema usa automáticamente el RFC.

## Uso desde Artisan

### Descargar acuse de eDocument

```bash
php artisan vucem:test-acuses 0170220LIS5D4
```

### Descargar acuse de COVE

```bash
php artisan vucem:test-acuses COVE214KNPVU4
```

### Con debug completo

```bash
php artisan vucem:test-acuses 0170220LIS5D4 --debug
```

Los PDFs se guardan en: `storage/app/acuses/acuse_{folio}.pdf`

## Uso desde PHP

### Ejemplo básico

```php
use App\Services\Vucem\ConsultaAcusesService;

$service = new ConsultaAcusesService();

// Consultar acuse de eDocument
$result = $service->consultarAcuse('0170220LIS5D4');

// O consultar acuse de COVE
$result = $service->consultarAcuse('COVE214KNPVU4');

if ($result['success']) {
    $pdfBase64 = $result['acuse_documento'];
    $pdfContent = base64_decode($pdfBase64);
    
    // Guardar PDF
    file_put_contents('acuse.pdf', $pdfContent);
}
```

### Estructura de respuesta exitosa

```php
[
    'success' => true,
    'code' => 0,
    'descripcion' => '',
    'acuse_documento' => 'JVBERi0xLjQKJeLjz9MK...', // PDF en base64
    'mensajes' => [],
    'mensajes_error' => [],
    'debug' => [
        'last_request' => '...',
        'last_response' => '...',
        'last_request_headers' => '...',
        'last_response_headers' => '...'
    ]
]
```

### Estructura de respuesta con error

```php
[
    'success' => false,
    'code' => 0,
    'descripcion' => 'El Edocument tiene un formato inválido...',
    'acuse_documento' => null,
    'mensajes' => [],
    'mensajes_error' => [
        [
            'clave' => '2',
            'descripcion' => 'El Edocument tiene un formato inválido, favor de validar.'
        ],
        [
            'clave' => '4',
            'descripcion' => 'El RFC no tiene relación con el eDocument.'
        ]
    ],
    'debug' => [...]
]
```

## Uso desde Controlador Web

### Rutas disponibles

```php
// Descargar acuse (detecta automáticamente el tipo)
GET /acuses/{folio}

// Listar acuses en cache
GET /acuses
```

### Ejemplo en Blade

```blade
<!-- Botón para descargar acuse de eDocument -->
<a href="{{ route('acuses.descargar', ['folio' => '0170220LIS5D4']) }}" 
   target="_blank" 
   class="btn btn-primary">
    Ver Acuse eDocument
</a>

<!-- Botón para descargar acuse de COVE -->
<a href="{{ route('acuses.descargar', ['folio' => $manifestacion->cove_folio]) }}" 
   target="_blank" 
   class="btn btn-success">
    Ver Acuse de Valor (COVE)
</a>
```

### Ejemplo con AJAX

```javascript
async function descargarAcuse(folio) {
    try {
        const response = await fetch(`/acuses/${folio}`);
        
        if (!response.ok) {
            const error = await response.json();
            alert(`Error: ${error.message}`);
            return;
        }
        
        // Abrir PDF en nueva ventana
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        window.open(url, '_blank');
        
    } catch (error) {
        console.error('Error al descargar acuse:', error);
        alert('Error al descargar el acuse');
    }
}

// Uso
descargarAcuse('0170220LIS5D4');  // eDocument
descargarAcuse('COVE214KNPVU4');   // COVE
```

## Integración en el Sistema

### 1. Agregar campos a la tabla `manifestations`

```php
// En tu migración
Schema::table('manifestations', function (Blueprint $table) {
    $table->string('edocument_folio')->nullable();
    $table->string('cove_folio')->nullable();
});
```

### 2. Agregar botones en la vista

```blade
@if($manifestation->edocument_folio)
    <a href="{{ route('acuses.descargar', ['folio' => $manifestation->edocument_folio]) }}" 
       target="_blank" 
       class="btn btn-sm btn-primary">
        <i class="fas fa-file-pdf"></i> Ver Acuse eDocument
    </a>
@endif

@if($manifestation->cove_folio)
    <a href="{{ route('acuses.descargar', ['folio' => $manifestation->cove_folio]) }}" 
       target="_blank" 
       class="btn btn-sm btn-success">
        <i class="fas fa-certificate"></i> Ver Acuse de Valor (COVE)
    </a>
@endif
```

### 3. Método en el controlador (opcional)

Si prefieres manejar la lógica en tu `ManifestationController`:

```php
public function descargarAcuseEdocument(string $uuid)
{
    $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail();
    
    if (empty($manifestation->edocument_folio)) {
        abort(404, 'No hay folio de eDocument');
    }
    
    return redirect()->route('acuses.descargar', [
        'folio' => $manifestation->edocument_folio
    ]);
}

public function descargarAcuseCove(string $uuid)
{
    $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail();
    
    if (empty($manifestation->cove_folio)) {
        abort(404, 'No hay folio COVE');
    }
    
    return redirect()->route('acuses.descargar', [
        'folio' => $manifestation->cove_folio
    ]);
}
```

## Funcionamiento Técnico

### 1. Detección automática del tipo de acuse

El servicio VUCEM detecta automáticamente el tipo de trámite según el formato del folio:

- **eDocument**: `[0-9]{7}[A-Z0-9]{6}` (ej: 0170220LIS5D4)
- **COVE**: `COVE[A-Z0-9]+` (ej: COVE214KNPVU4)

### 2. Estructura del request SOAP

```xml
<SOAP-ENV:Envelope>
  <SOAP-ENV:Header>
    <wsse:Security SOAP-ENV:mustUnderstand="1">
      <wsse:UsernameToken>
        <wsse:Username>NET070608EM9</wsse:Username>
        <wsse:Password Type="...#PasswordText">CJ+ZEKptDgcl...</wsse:Password>
      </wsse:UsernameToken>
    </wsse:Security>
  </SOAP-ENV:Header>
  <SOAP-ENV:Body>
    <ns:consultaAcusesPeticion xmlns:ns="http://www.ventanillaunica.gob.mx/consulta/acuses/oxml">
      <idEdocument>0170220LIS5D4</idEdocument>
    </ns:consultaAcusesPeticion>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
```

### 3. Parser MTOM

El servicio devuelve respuestas en formato MTOM (multipart). El código incluye un parser personalizado para extraer el XML y el PDF en base64.

### 4. Cache de archivos

Los PDFs se guardan en `storage/app/acuses/` para evitar consultas repetidas al servicio VUCEM.

## Errores Comunes

### Error: "El Edocument tiene un formato inválido"

El folio no cumple con el formato esperado o no existe en VUCEM.

**Solución:** Verificar que el folio sea correcto.

### Error: "El RFC no tiene relación con el eDocument"

El folio pertenece a otro RFC.

**Solución:** Solo puedes consultar acuses de folios asociados a tu RFC.

### Error: "looks like we got no XML document"

La respuesta MTOM no se pudo parsear.

**Solución:** El código incluye un parser MTOM que maneja este caso automáticamente.

## Logs

Todos los eventos se registran en `storage/logs/laravel.log` con el prefijo `[CONSULTA-ACUSES]`:

```
[CONSULTA-ACUSES] Iniciando consulta de acuse: folio=0170220LIS5D4
[CONSULTA-ACUSES] Enviando request SOAP
[CONSULTA-ACUSES] Respuesta MTOM detectada, parseando manualmente
[CONSULTA-ACUSES] Respuesta MTOM parseada exitosamente
```

## Códigos de Respuesta

- **code: 0, error: false** → Éxito, acuse disponible
- **code: 0, error: true** → Error de validación (folio inválido, RFC no relacionado, etc.)

## Testing

Ejecutar pruebas con folios de ejemplo:

```bash
# Folio válido de tu RFC
php artisan vucem:test-acuses 0170220LIS5D4

# Ver XML request/response completo
php artisan vucem:test-acuses 0170220LIS5D4 --debug

# Verificar archivos descargados
ls storage/app/acuses/
```
