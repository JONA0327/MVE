# Función processMFileForCove - Documentación

## Descripción

La función `processMFileForCove(string $filePath)` ha sido implementada en el servicio `MFileParserService` para procesar archivos M de pedimento y obtener/consultar COVEs automáticamente.

## Ubicación

- **Servicio**: `app/Services/MFileParserService.php`
- **Controlador**: `app/Http\Controllers\MFileCoveController.php`
- **Comando de prueba**: `app/Console/Commands/TestMFileCove.php`

## Funcionalidad

### ✅ Características implementadas:

1. **Parsing seguro del archivo M**: Lee línea por línea, maneja errores de archivo
2. **Extracción de datos por operación**: Agrupa datos por número de operación
3. **Detección de COVE existente**: Si la línea 505 ya tiene COVE, no consulta webservice
4. **Consulta automática de webservice**: Si no tiene COVE, consulta `ConsultarRespuestaCove`
5. **Uso de rutas relativas**: Usa `base_path('wsdl/vucem/COVE/ConsultarRespuestaCove.wsdl')`
6. **Manejo de excepciones**: Logs detallados y manejo de errores
7. **Construcción automática de folio COVE**: Formato de 15 dígitos según especificación

### 📋 Datos extraídos por operación:

Del archivo M se extraen:
- **Registro 500**: patente, numeroOperacion, sección
- **Registro 505**: ejercicio (de fecha), COVE si existe
- **Registro 801**: código de aduana

### 🔄 Lógica de procesamiento:

1. **Parsear archivo M** → agrupar por número de operación
2. **Para cada operación**:
   - Si tiene COVE en línea 505 → `estatusCove: 'encontrado'`
   - Si NO tiene COVE → consultar webservice:
     - Si webservice devuelve COVE → `estatusCove: 'encontrado'`
     - Si webservice falla → `estatusCove: 'error_ws'`
     - Si no se encuentra → `estatusCove: 'no_encontrado'`

## Formato de respuesta

```php
[
    [
        'aduana' => int|null,           // Del registro 801
        'seccion' => string|null,       // Del registro 500 (campo 4)
        'patente' => string,            // Del registro 500 (campo 2)
        'ejercicio' => int|null,        // Del registro 505 (año de fecha)
        'numeroOperacion' => string,    // Del registro 500 (campo 3)
        'folioCove' => string|null,     // COVE encontrado o consultado
        'estatusCove' => 'encontrado' | 'no_encontrado' | 'error_ws',
    ],
    // ... más operaciones
]
```

## Ejemplos de uso

### 1. Uso directo del servicio:

```php
use App\Services\MFileParserService;

$parser = new MFileParserService();
$resultados = $parser->processMFileForCove('/ruta/al/archivo.m');

foreach ($resultados as $operacion) {
    echo "Operación: " . $operacion['numeroOperacion'];
    echo " - COVE: " . ($operacion['folioCove'] ?? 'No encontrado');
    echo " - Estado: " . $operacion['estatusCove'] . PHP_EOL;
}
```

### 2. Uso desde controlador:

```php
// POST /api/mfile/procesar-cove
// Subir archivo M y procesarlo automáticamente
```

### 3. Comando de prueba:

```bash
php artisan test:mfile-cove "storage/app/archivo_ejemplo.txt"
```

## Configuración WSDL

El sistema usa **rutas relativas** configuradas en `config/vucem.php`:

```php
'wsdl_path' => base_path('wsdl/vucem/COVE/ConsultarRespuestaCove.wsdl'),
```

### ✅ Rutas correctas (usadas):
- `base_path('wsdl/vucem/COVE/ConsultarRespuestaCove.wsdl')`
- `__DIR__ . '/../wsdl/vucem/COVE/ConsultarRespuestaCove.wsdl'`

### ❌ Rutas incorrectas (evitadas):
- `C:\Users\Sistemas\Downloads\PROYECTOS EI\MVE\...`
- Rutas absolutas locales

## Estructura SOAP

La función utiliza el servicio `ConsultarCoveService` existente que implementa la estructura SOAP requerida:

```xml
<soapenv:Envelope>
    <soapenv:Header>
        <wsse:Security>
            <wsse:UsernameToken>
                <wsse:Username>{RFC_USUARIO}</wsse:Username>
                <wsse:Password>{CLAVE_WEBSERVICE}</wsse:Password>
            </wsse:UsernameToken>
        </wsse:Security>
    </soapenv:Header>
    <soapenv:Body>
        <tem:solicitarConsultarRespuestaCoveServicio>
            <tem:numeroOperacion>{FOLIO_15_DIGITOS}</tem:numeroOperacion>
            <tem:firmaElectronica>
                <tem:certificado>{CERTIFICADO_BASE64}</tem:certificado>
                <tem:cadenaOriginal>{CADENA_ORIGINAL}</tem:cadenaOriginal>
                <tem:firma>{FIRMA_BASE64}</tem:firma>
            </tem:firmaElectronica>
        </tem:solicitarConsultarRespuestaCoveServicio>
    </soapenv:Body>
</soapenv:Envelope>
```

## Logs y depuración

La función genera logs detallados en `storage/logs/laravel.log`:

- Inicio de procesamiento
- Datos extraídos por operación
- COVEs encontrados/no encontrados
- Errores de webservice
- Estadísticas finales

## Pruebas realizadas

✅ Parsing correcto del archivo M  
✅ Extracción de datos por operación  
✅ Detección de COVE existente  
✅ Intento de consulta webservice  
✅ Manejo de errores  
✅ Uso de rutas relativas  
✅ Formato de respuesta correcto  

## Requisitos del sistema

- PHP 8+
- Laravel 10+
- Usuario autenticado (necesario para credenciales VUCEM)
- Extensión SOAP habilitada
- Archivos WSDL en `wsdl/vucem/COVE/`