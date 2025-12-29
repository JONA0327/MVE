# 🚀 GUÍA RÁPIDA: Solución ConsultarEdocument VUCEM

## 📋 ARCHIVOS CREADOS/ACTUALIZADOS

### ✅ Archivos Nuevos

1. **`docs/ANALISIS_CONSULTAREDOCUMENT.md`**
   - Análisis técnico completo
   - Validación de estructura XML vs WSDL/XSD
   - Errores comunes y soluciones
   - Checklist de validación

2. **`wsdl/vucem/COVE/edocument/RecibirCove.xsd`**
   - XSD faltante que define `FirmaElectronica`
   - Requerido por `ConsultarEdocument.xsd`

3. **`app/Console/Commands/TestConsultarEdocumentCommand.php`**
   - Comando para probar y validar la configuración
   - Análisis automático del XML generado
   - Debug detallado con sugerencias

4. **`app/Services/Vucem/ConsultarEdocumentServiceV2.php`**
   - Versión mejorada con control total de namespaces
   - Construcción manual del XML para garantizar estructura correcta
   - Mejor manejo de errores

5. **`docs/GUIA_RAPIDA_CONSULTAREDOCUMENT.md`** (este archivo)
   - Instrucciones de uso inmediato

---

## 🔧 PASOS PARA PROBAR

### 1️⃣ Validar Configuración (SIN llamada real a VUCEM)

```bash
php artisan vucem:test-edocument --validate-only
```

**Esto verificará:**
- ✅ Usuario con RFC configurado
- ✅ Clave webservice presente
- ✅ Archivos e.firma (.cer, .key, contraseña)
- ✅ Archivos WSDL/XSD presentes
- ✅ Configuración de endpoints
- ✅ Generación de firma electrónica

**Resultado esperado:**
```
═══════════════════════════════════════════════════════════
✅ VALIDACIÓN COMPLETA - Todo configurado correctamente
═══════════════════════════════════════════════════════════
```

---

### 2️⃣ Consultar eDocument Real (CON llamada a VUCEM)

```bash
php artisan vucem:test-edocument COVE123456789 --debug
```

**Parámetros:**
- `COVE123456789` - Reemplazar con tu eDocument real
- `--debug` - Muestra XML request/response completo + análisis automático
- `--rfc=RFC123` - Opcional: especificar RFC del usuario

**Resultado esperado (éxito):**
```
✅ CONSULTA EXITOSA
═══════════════════════════════════════════════════════════

Mensaje: Consulta exitosa

📦 Datos del COVE:
┌─────────────────┬─────────────────┐
│ Campo           │ Valor           │
├─────────────────┼─────────────────┤
│ eDocument       │ COVE123456789   │
│ Tipo Operación  │ IMPORT          │
│ Número Factura  │ FAC-123456      │
└─────────────────┴─────────────────┘
```

**Resultado esperado (error):**
```
❌ CONSULTA FALLIDA
═══════════════════════════════════════════════════════════

Mensaje: eDocument no encontrado

💡 Sugerencias:
  • Verifica que el eDocument existe en el portal web
  • Confirma que tu RFC tiene permisos para consultar ese COVE
  • Revisa que el número sea exacto (sin espacios)
```

---

### 3️⃣ Usar Servicio V2 en Código

#### Opción A: Reemplazar servicio actual

Editar `config/app.php` o crear un Service Provider:

```php
// En tu código
use App\Services\Vucem\ConsultarEdocumentServiceV2;

$service = app(ConsultarEdocumentServiceV2::class);
$result = $service->consultarEdocument('COVE123456789');

if ($result['success']) {
    echo "✅ Éxito: " . $result['message'];
    print_r($result['cove_data']);
} else {
    echo "❌ Error: " . $result['message'];
}

// Ver XML enviado/recibido
$debug = $service->getDebugInfo();
echo $debug['last_request'];
```

#### Opción B: Exportar XML para validación externa

```php
$service = app(ConsultarEdocumentServiceV2::class);
$xml = $service->exportRequestXml('COVE123456789');

// Guardar para validar con xmllint u otra herramienta
file_put_contents('request.xml', $xml);
```

---

## 🐛 ANÁLISIS DE ERRORES COMUNES

### Error: "eDocument no encontrado"

**Causas:**
1. El RFC que consulta no tiene permisos para ver ese COVE
2. El eDocument no existe o está mal escrito
3. Endpoint incorrecto (producción vs pruebas)

**Solución:**
```bash
# 1. Verificar en portal web primero
https://www.ventanillaunica.gob.mx/

# 2. Verificar que tu RFC está relacionado con el COVE
# (emisor, destinatario, agente aduanal, etc.)

# 3. Verificar endpoint
php artisan tinker
config('vucem.edocument.endpoint')
# Debe ser: https://www.ventanillaunica.gob.mx/ventanilla/ConsultarEdocument
```

---

### Error: "401 Unauthorized" o "403 Forbidden"

**Causas:**
1. RFC incorrecto en WS-Security
2. Contraseña de portal en vez de clave webservice
3. Clave webservice vencida

**Solución:**
```bash
php artisan tinker

$user = App\Models\User::where('rfc', 'TU_RFC')->first();
echo $user->rfc;  # Verificar RFC exacto
echo $user->getDecryptedWebserviceKey();  # Verificar clave WS

# Si está vacía o incorrecta, actualizar:
$user->webservice_key = encrypt('CLAVE_WEBSERVICE_CORRECTA');
$user->save();
```

**⚠️ IMPORTANTE:**
- NO uses la contraseña del portal web
- Usa la **CLAVE WEBSERVICE** que se genera en VUCEM → Configuración → Servicios Web

---

### Error: "Firma inválida"

**Causas:**
1. Certificado e.firma vencido
2. Certificado no coincide con RFC
3. Contraseña de la llave privada incorrecta
4. Formato de cadena original incorrecto

**Solución:**
```bash
# 1. Verificar archivos e.firma
php artisan tinker
$efirma = app(\App\Services\Vucem\EFirmaService::class);
$status = $efirma->verificarArchivos();
print_r($status);

# 2. Probar generación de firma
$firma = $efirma->generarFirmaElectronica('TEST123', 'TU_RFC');
print_r($firma);

# Verificar formato de cadena original:
# Debe ser: |TEST123|TU_RFC|
echo $firma['cadenaOriginal'];
```

---

### Error: Nodo `<request>` faltante

**Este es el error MÁS COMÚN.**

El XSD requiere:
```xml
<ConsultarEdocumentRequest>
  <request>  <!-- ⚠️ OBLIGATORIO -->
    <firmaElectronica>...</firmaElectronica>
    <criterioBusqueda>...</criterioBusqueda>
  </request>
</ConsultarEdocumentRequest>
```

**Solución:**
- El servicio V2 (`ConsultarEdocumentServiceV2`) ya lo incluye correctamente
- Usar el comando con `--debug` para verificar el XML:

```bash
php artisan vucem:test-edocument COVE123 --debug
```

En la sección "🔍 ANÁLISIS AUTOMÁTICO" debe mostrar:
```
✅ Nodo <request> wrapper presente
```

---

## 📊 ESTRUCTURA XML CORRECTA

### Request Completo

```xml
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope 
    xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" 
    xmlns:ns1="http://www.ventanillaunica.gob.mx/ConsultarEdocument/"
    xmlns:oxml="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
    
  <SOAP-ENV:Header>
    <wsse:Security 
        xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"
        xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
      <wsse:UsernameToken wsu:Id="UsernameToken-1">
        <wsse:Username>RFC123456789ABC</wsse:Username>
        <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">
          CLAVE_WEBSERVICE_AQUI
        </wsse:Password>
      </wsse:UsernameToken>
    </wsse:Security>
  </SOAP-ENV:Header>
  
  <SOAP-ENV:Body>
    <ns1:ConsultarEdocumentRequest>
      <ns1:request>                              <!-- ⚠️ CRÍTICO -->
        
        <oxml:firmaElectronica>                  <!-- ⚠️ Namespace oxml -->
          <oxml:certificado>BASE64...</oxml:certificado>
          <oxml:cadenaOriginal>|COVE123|RFC123|</oxml:cadenaOriginal>
          <oxml:firma>BASE64...</oxml:firma>
        </oxml:firmaElectronica>
        
        <ns1:criterioBusqueda>                   <!-- ⚠️ Namespace ns1 -->
          <ns1:eDocument>COVE123456789</ns1:eDocument>
        </ns1:criterioBusqueda>
        
      </ns1:request>
    </ns1:ConsultarEdocumentRequest>
  </SOAP-ENV:Body>
  
</SOAP-ENV:Envelope>
```

### Validación de Namespaces

| Elemento | Namespace Correcto | Prefijo |
|----------|-------------------|---------|
| `ConsultarEdocumentRequest` | `http://www.ventanillaunica.gob.mx/ConsultarEdocument/` | `ns1` |
| `request` | `http://www.ventanillaunica.gob.mx/ConsultarEdocument/` | `ns1` |
| `criterioBusqueda` | `http://www.ventanillaunica.gob.mx/ConsultarEdocument/` | `ns1` |
| `eDocument` | `http://www.ventanillaunica.gob.mx/ConsultarEdocument/` | `ns1` |
| `firmaElectronica` | `http://www.ventanillaunica.gob.mx/cove/ws/oxml/` | `oxml` |
| `certificado` | `http://www.ventanillaunica.gob.mx/cove/ws/oxml/` | `oxml` |
| `cadenaOriginal` | `http://www.ventanillaunica.gob.mx/cove/ws/oxml/` | `oxml` |
| `firma` | `http://www.ventanillaunica.gob.mx/cove/ws/oxml/` | `oxml` |

---

## ✅ CHECKLIST PRE-LLAMADA

Antes de hacer una llamada real a VUCEM:

- [ ] Usuario tiene RFC configurado
- [ ] Usuario tiene CLAVE WEBSERVICE (no contraseña portal)
- [ ] Archivos e.firma presentes y válidos (.cer, .key, contraseña)
- [ ] Certificado e.firma vigente y del mismo RFC
- [ ] WSDL/XSD descargados correctamente
- [ ] Endpoint es de PRODUCCIÓN
- [ ] eDocument existe en portal web
- [ ] RFC tiene permisos para consultar ese eDocument
- [ ] SOAP 1.1 configurado
- [ ] Nodo `<request>` wrapper presente en XML
- [ ] Namespaces correctos (ConsultarEdocument/ y oxml/)
- [ ] Cadena original formato: `|eDocument|RFC|`
- [ ] WS-Security header con UsernameToken

---

## 🔄 COMPARACIÓN: Servicio Original vs V2

| Característica | Original | V2 |
|----------------|----------|-----|
| Construcción XML | Array PHP → SoapClient serializa | XML manual con control total |
| Namespaces | Automático (puede fallar) | Explícitos y correctos |
| Nodo `<request>` | ✅ Presente | ✅ Presente |
| Namespace oxml | ⚠️ Puede faltar | ✅ Garantizado |
| Debug | ✅ Completo | ✅ Completo |
| Validación | Básica | Avanzada |
| Exportar XML | ❌ No | ✅ Sí (`exportRequestXml`) |

**Recomendación:**
1. Probar primero con comando: `php artisan vucem:test-edocument --debug`
2. Si funciona: usar servicio original
3. Si falla por namespaces: usar V2

---

## 📞 SOPORTE

### Si todo falla después de validar

1. **Guardar evidencia:**
```bash
php artisan vucem:test-edocument COVE123 --debug > debug_output.txt
```

2. **Contactar Mesa de Ayuda VUCEM:**
   - Tel: 55-8526-6000
   - Email: mesadeserviciosvucem@sat.gob.mx
   - Horario: Lunes a Viernes 9:00-18:00

3. **Proporcionar:**
   - RFC del usuario
   - eDocument consultado
   - Fecha/hora del intento
   - Archivo `debug_output.txt`
   - Captura del portal web mostrando que el COVE sí existe

---

## 📚 DOCUMENTOS DE REFERENCIA

1. **`docs/ANALISIS_CONSULTAREDOCUMENT.md`** - Análisis técnico completo
2. **`docs/COVE_INTEGRATION.md`** - Integración COVE general
3. **`docs/SEGURIDAD_COVE.md`** - Seguridad y diferencias entre servicios
4. **`wsdl/vucem/COVE/edocument/ConsultarEdocument.wsdl`** - WSDL oficial
5. **`wsdl/vucem/COVE/edocument/ConsultarEdocument.xsd`** - XSD oficial

---

## 🎯 SIGUIENTE PASO

**Ejecuta esto AHORA:**

```bash
php artisan vucem:test-edocument --validate-only
```

Si todo está ✅, entonces:

```bash
php artisan vucem:test-edocument TU_EDOCUMENT_REAL --debug
```

**¡Analiza el output y compártelo si necesitas más ayuda!**

---

**Última actualización:** 26 de Diciembre de 2025  
**Autor:** GitHub Copilot con Claude Sonnet 4.5  
**Versión:** 1.0
