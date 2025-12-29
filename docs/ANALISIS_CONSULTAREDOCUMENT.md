# 🔍 Análisis Técnico: Consulta ConsultarEdocument de VUCEM

## 📋 Resumen del Problema

Consulta al Web Service de VUCEM que no funciona correctamente, aunque la información sí aparece en el portal web.

---

## ✅ VALIDACIÓN 1: ESTRUCTURA XML vs WSDL/XSD

### **WSDL Analizado**
- **Archivo**: `wsdl/vucem/COVE/edocument/ConsultarEdocument.wsdl`
- **Endpoint**: `https://www.ventanillaunica.gob.mx/ventanilla/ConsultarEdocument`
- **SOAPAction**: `http://www.ventanillaunica.gob.mx/cove/ws/service/ConsultarEdocument`
- **Versión SOAP**: SOAP 1.1 ✅
- **Binding**: Document/Literal

### **XSD Analizado**
- **Archivo**: `wsdl/vucem/COVE/edocument/ConsultarEdocument.xsd`
- **Namespace Target**: `http://www.ventanillaunica.gob.mx/ConsultarEdocument/`
- **Namespace Firma**: `http://www.ventanillaunica.gob.mx/cove/ws/oxml/`

### **Estructura Requerida según XSD**

```xml
<ConsultarEdocumentRequest xmlns="http://www.ventanillaunica.gob.mx/ConsultarEdocument/">
  <request>                           <!-- ⚠️ NODO WRAPPER OBLIGATORIO -->
    <firmaElectronica xmlns="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
      <certificado>BASE64_CERT</certificado>
      <cadenaOriginal>|EDOCUMENT|RFC|</cadenaOriginal>
      <firma>BASE64_SIGNATURE</firma>
    </firmaElectronica>
    <criterioBusqueda>
      <eDocument>TU_EDOCUMENT_AQUI</eDocument>
      <numeroAdenda>OPCIONAL</numeroAdenda>  <!-- OPCIONAL -->
    </criterioBusqueda>
  </request>
</ConsultarEdocumentRequest>
```

### **⚠️ ERRORES COMUNES DETECTADOS**

#### 1. **Falta del nodo `<request>` wrapper**
```xml
<!-- ❌ INCORRECTO -->
<ConsultarEdocumentRequest>
  <firmaElectronica>...</firmaElectronica>
  <criterioBusqueda>...</criterioBusqueda>
</ConsultarEdocumentRequest>

<!-- ✅ CORRECTO -->
<ConsultarEdocumentRequest>
  <request>
    <firmaElectronica>...</firmaElectronica>
    <criterioBusqueda>...</criterioBusqueda>
  </request>
</ConsultarEdocumentRequest>
```

#### 2. **Namespaces Incorrectos**
```xml
<!-- ❌ INCORRECTO - Namespace mixto -->
<ns1:ConsultarEdocumentRequest xmlns:ns1="http://www.ventanillaunica.gob.mx/cove/ws/service/">

<!-- ✅ CORRECTO - Namespace del XSD -->
<ns1:ConsultarEdocumentRequest xmlns:ns1="http://www.ventanillaunica.gob.mx/ConsultarEdocument/">
```

#### 3. **firmaElectronica sin namespace propio**
```xml
<!-- ❌ INCORRECTO -->
<firmaElectronica>
  <certificado>...</certificado>
</firmaElectronica>

<!-- ✅ CORRECTO -->
<oxml:firmaElectronica xmlns:oxml="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
  <oxml:certificado>...</oxml:certificado>
  <oxml:cadenaOriginal>...</oxml:cadenaOriginal>
  <oxml:firma>...</oxml:firma>
</oxml:firmaElectronica>
```

---

## ✅ VALIDACIÓN 2: WS-SECURITY (Autenticación)

### **Configuración Actual (CORRECTA según WSDL)**

El WSDL especifica:
```xml
<sp:UsernameToken sp:IncludeToken=".../AlwaysToRecipient">
  <wsp:Policy>
    <sp:WssUsernameToken10 />
  </wsp:Policy>
</sp:UsernameToken>
```

### **Header WS-Security Implementado**
```xml
<wsse:Security 
  xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"
  xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
  <wsse:UsernameToken wsu:Id="UsernameToken-1">
    <wsse:Username>TU_RFC</wsse:Username>
    <wsse:Password Type="...#PasswordText">CLAVE_WEBSERVICE</wsse:Password>
  </wsse:UsernameToken>
</wsse:Security>
```

### **⚠️ ERRORES COMUNES EN AUTENTICACIÓN**

| Error | Causa | Solución |
|-------|-------|----------|
| **401 Unauthorized** | RFC incorrecto o no registrado | Verificar RFC en portal VUCEM |
| **403 Forbidden** | Contraseña de portal en vez de clave WS | Usar **CLAVE WEBSERVICE** no la del portal |
| **Invalid credentials** | RFC no tiene permisos para consultar ese COVE | El RFC debe ser el titular o autorizado |
| **Password expired** | Clave WS vencida | Renovar en portal VUCEM |

---

## ✅ VALIDACIÓN 3: FIRMA ELECTRÓNICA

### **Formato de Cadena Original**
```
|{eDocument}|{RFC}|
```

**Ejemplo:**
```
|COVE123456789|RFC123456789ABC|
```

### **⚠️ ERRORES COMUNES**

#### 1. **RFC Diferente**
```
❌ Usuario autenticado: RFC123456789
   Firma con: RFC987654321
   
✅ Ambos deben ser el mismo RFC
```

#### 2. **eDocument Incorrecto**
```
❌ Consultar: COVE123456
   Firmar:    COVE789012
   
✅ Debe ser exactamente el mismo valor
```

#### 3. **Formato de Cadena Incorrecto**
```
❌ RFC123|COVE123              (sin pipes iniciales/finales)
❌ |RFC123|COVE123             (orden invertido)
❌ ||COVE123||RFC123||         (pipes duplicados)

✅ |COVE123|RFC123|            (formato correcto)
```

#### 4. **Certificado Incorrecto**
```
❌ Certificado de otra persona
❌ Certificado vencido
❌ Certificado no registrado en VUCEM

✅ Certificado e.firma vigente del RFC que consulta
```

---

## ✅ VALIDACIÓN 4: REGLAS DE NEGOCIO VUCEM

### **¿Quién puede consultar un eDocument?**

1. **El RFC que generó el COVE** (emisor/importador)
2. **El RFC destinatario** (si está registrado en el COVE)
3. **Agente aduanal autorizado** (patente registrada)
4. **RFC con permisos especiales** otorgados por el titular

### **⚠️ ERRORES COMUNES**

| Mensaje de Error | Causa Probable | Solución |
|-----------------|----------------|----------|
| `eDocument no encontrado` | RFC no autorizado para ver ese COVE | Verificar que tu RFC esté relacionado con el COVE |
| `Acceso denegado` | RFC sin permisos | Solicitar autorización al titular |
| `eDocument inválido` | Formato incorrecto o no existe | Verificar el número en portal web primero |
| `Firma inválida` | Certificado no coincide con RFC | Usar certificado correcto |

---

## 📋 CHECKLIST DE VALIDACIÓN

### **Antes de Enviar Request**

- [ ] WSDL cargado correctamente desde archivo local
- [ ] Endpoint es de **PRODUCCIÓN** no de pruebas
- [ ] SOAP 1.1 configurado (no 1.2)
- [ ] WS-Security header presente
- [ ] Username = RFC del usuario
- [ ] Password = CLAVE WEBSERVICE (no contraseña portal)
- [ ] Nodo `<request>` wrapper presente
- [ ] Namespace `ConsultarEdocument/` en request
- [ ] Namespace `oxml/` en firmaElectronica
- [ ] Cadena original formato: `|eDocument|RFC|`
- [ ] Certificado e.firma en base64 (sin headers PEM)
- [ ] Firma generada con algoritmo SHA256
- [ ] RFC de firma = RFC de WS-Security
- [ ] eDocument existe en portal VUCEM
- [ ] RFC tiene permisos para consultar ese eDocument

### **Al Recibir Error**

- [ ] Verificar `__getLastRequest()` para ver XML enviado
- [ ] Verificar `__getLastResponse()` para ver respuesta exacta
- [ ] Revisar logs del servidor
- [ ] Probar en portal web primero
- [ ] Validar XML contra XSD con herramienta externa

---

## 🔧 CONFIGURACIÓN ACTUAL DEL PROYECTO

### **Archivos Clave**

```
app/Services/Vucem/ConsultarEdocumentService.php  - Servicio principal
app/Services/Vucem/EFirmaService.php              - Generación de firma
wsdl/vucem/COVE/edocument/ConsultarEdocument.wsdl - WSDL
wsdl/vucem/COVE/edocument/ConsultarEdocument.xsd  - XSD
wsdl/vucem/COVE/edocument/RecibirCove.xsd         - XSD firma (NUEVO)
config/vucem.php                                   - Configuración
```

### **Variables de Entorno Necesarias**

```env
# RFC del usuario (usado en WS-Security y firma)
# Este se obtiene del perfil del usuario autenticado

# Clave Webservice (NO la contraseña del portal)
# Se guarda encriptada en la BD del usuario
# $user->webservice_key

# Endpoint de Producción
VUCEM_EDOCUMENT_ENDPOINT=https://www.ventanillaunica.gob.mx/ventanilla/ConsultarEdocument

# SOAPAction
VUCEM_EDOCUMENT_ACTION=http://www.ventanillaunica.gob.mx/cove/ws/service/ConsultarEdocument

# Archivos e.firma
VUCEM_EFIRMA_PATH=pruebaEfirma
VUCEM_EFIRMA_CERT=certificado.cer
VUCEM_EFIRMA_KEY=llave.key
VUCEM_EFIRMA_PASSWORD_FILE=CONTRASEÑA.txt
```

---

## 🚀 EJEMPLO DE XML CORRECTO

### **Estructura Completa SOAP**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope 
    xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" 
    xmlns:ns1="http://www.ventanillaunica.gob.mx/ConsultarEdocument/"
    xmlns:oxml="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
    
  <!-- WS-SECURITY HEADER -->
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
  
  <!-- SOAP BODY -->
  <SOAP-ENV:Body>
    <ns1:ConsultarEdocumentRequest>
      <ns1:request>
        
        <!-- FIRMA ELECTRÓNICA -->
        <oxml:firmaElectronica>
          <oxml:certificado>MIIFuzCCA6OgAwIBAgIUMjAwMDEwMDAwMDA0MDAwMDI0MzgwDQYJ...</oxml:certificado>
          <oxml:cadenaOriginal>|COVE123456789|RFC123456789ABC|</oxml:cadenaOriginal>
          <oxml:firma>iVBORw0KGgoAAAANSUhEUgAABQAAAALQCAYAAADPfd...</oxml:firma>
        </oxml:firmaElectronica>
        
        <!-- CRITERIO DE BÚSQUEDA -->
        <ns1:criterioBusqueda>
          <ns1:eDocument>COVE123456789</ns1:eDocument>
          <!-- numeroAdenda es OPCIONAL -->
        </ns1:criterioBusqueda>
        
      </ns1:request>
    </ns1:ConsultarEdocumentRequest>
  </SOAP-ENV:Body>
  
</SOAP-ENV:Envelope>
```

---

## 🐛 DEBUGGING

### **Capturar XML Real**

El servicio ya incluye logging automático:

```php
$debugInfo = $service->getDebugInfo();
echo $debugInfo['last_request'];    // XML enviado
echo $debugInfo['last_response'];   // XML recibido
```

### **Logs en Laravel**

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log | grep EDOCUMENT
```

Los mensajes incluyen:
- `[EDOCUMENT] Iniciando consulta`
- `[EDOCUMENT] Generando firma electrónica`
- `[EDOCUMENT] SOAP Request enviado`
- `[EDOCUMENT] SOAP Response recibido`
- `[EDOCUMENT] Consulta exitosa`

### **Validar XML contra XSD**

```bash
# Usando xmllint (requiere libxml2)
xmllint --schema wsdl/vucem/COVE/edocument/ConsultarEdocument.xsd request.xml --noout

# Respuesta esperada:
# request.xml validates
```

---

## ✅ PRUEBA PASO A PASO

### **1. Verificar Configuración**

```bash
php artisan tinker
```

```php
$user = Auth::user();
echo $user->rfc;  // Debe tener un RFC
echo $user->getDecryptedWebserviceKey();  // Debe tener clave WS
```

### **2. Verificar e.firma**

```bash
php artisan tinker
```

```php
$efirma = app(\App\Services\Vucem\EFirmaService::class);
$status = $efirma->verificarArchivos();
print_r($status);
// Todos deben ser true, errores debe estar vacío
```

### **3. Consultar eDocument Real**

```bash
php artisan tinker
```

```php
$service = app(\App\Services\Vucem\ConsultarEdocumentService::class);
$result = $service->consultarEdocument('TU_EDOCUMENT_AQUI');
print_r($result);

// Ver XML enviado
$debug = $service->getDebugInfo();
echo $debug['last_request'];
echo $debug['last_response'];
```

---

## 📞 CONTACTO VUCEM

Si todo está correcto y sigue fallando:

**Mesa de Ayuda VUCEM:**
- Tel: 55-8526-6000
- Email: mesadeserviciosvucem@sat.gob.mx
- Horario: Lunes a Viernes 9:00-18:00

**Información requerida para reporte:**
- RFC del usuario
- Fecha y hora del intento
- eDocument consultado
- XML Request completo (censurar passwords)
- XML Response completo
- Mensaje de error exacto

---

## 📚 REFERENCIAS

- [Portal VUCEM](https://www.ventanillaunica.gob.mx/)
- [Documentación Oficial WS VUCEM](https://www.ventanillaunica.gob.mx/vucem/AdmServicios.html)
- [WS-Security 1.0 Spec](http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0.pdf)
- [SOAP 1.1 Spec](https://www.w3.org/TR/2000/NOTE-SOAP-20000508/)

---

**Última actualización:** 26 de Diciembre de 2025  
**Versión del documento:** 1.0
