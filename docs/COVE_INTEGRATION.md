# 🔍 Sistema de Consulta COVE - VUCEM Integration

## 📋 Resumen
Sistema completo para consultar Comprobantes de Valor Electrónico (COVE) desde VUCEM usando SOAP Web Services, integrado en el paso 1 de Manifestación de Valor.

## 🏗️ Arquitectura Implementada

### Backend
- **ConsultarCoveService**: Servicio SOAP con WS-Security
- **CoveController**: API REST para frontend
- **Configuración**: Credenciales seguras desde .env
- **Tests**: Unitarios y de integración

### Frontend
- **JavaScript integrado**: En step1.blade.php
- **Modal de errores**: Diseño consistente con el sistema
- **UX mejorada**: Spinner, validaciones, notificaciones

## 📁 Archivos Creados/Modificados

### 🆕 Archivos Nuevos
```
app/Services/Vucem/ConsultarCoveService.php     - Servicio SOAP principal
app/Http/Controllers/Api/CoveController.php     - Controlador API
config/vucem.php                               - Configuración VUCEM
app/Console/Commands/TestCoveConfiguration.php  - Comando de pruebas
tests/Unit/Vucem/ConsultarCoveServiceTest.php  - Tests unitarios
tests/Feature/Api/CoveControllerTest.php       - Tests de API
```

### ✏️ Archivos Modificados
```
routes/web.php                                - Rutas API COVE
resources/views/manifestations/step1.blade.php - JavaScript + Modal
app/Models/User.php                           - Métodos webservice_key
.env                                          - Variables VUCEM
```

## ⚙️ Configuración Requerida

### 1. Variables de Entorno (.env)
```bash
# OBLIGATORIAS
VUCEM_RFC=RFC123456789
VUCEM_WS_PASSWORD=tu_clave_webservice_vucem

# OPCIONALES (tienen valores por defecto)
VUCEM_CONSULTAR_COVE_ENDPOINT=http://www.ventanillaunica.gob.mx/ConsultarRespuestaCoveService
VUCEM_SOAP_TIMEOUT=30
VUCEM_LOG_SOAP=false
```

### 2. Verificar Dependencias
```bash
# Extensión PHP SOAP debe estar habilitada
php -m | grep soap

# WSDL debe existir
ls wsdl/vucem/ConsultarRespuestaCove.wsdl
```

## 🚀 Uso del Sistema

### Para Usuarios Finales
1. **Ir a Manifestación → Step 1 → Tab "COVEs"**
2. **Escribir folio de COVE** en el campo correspondiente
3. **Hacer clic en la lupa** 🔍 de esa fila
4. **El sistema rellena automáticamente**:
   - Método de valoración
   - Número de factura
   - Fecha de expedición
   - Emisor

### Comportamiento en Errores
- **COVE no encontrado** → Modal con sugerencias
- **Credenciales incorrectas** → Modal con instrucciones
- **Error de red** → Modal con recomendaciones
- **Error de servidor** → Modal informativo

## 🧪 Pruebas y Debugging

### 1. Comando de Prueba
```bash
# Probar configuración básica
php artisan cove:test-config

# Probar con COVE específico
php artisan cove:test-config --cove=ABC123456
```

### 2. Endpoints de Debug (solo en modo debug)
```bash
# Verificar configuración
GET /api/coves/check-config

# Probar conectividad
POST /api/coves/test-connection
```

### 3. Ejecutar Tests
```bash
# Tests unitarios
php artisan test tests/Unit/Vucem/ConsultarCoveServiceTest.php

# Tests de API
php artisan test tests/Feature/Api/CoveControllerTest.php

# Todos los tests
php artisan test
```

## 🔧 API Reference

### POST /api/coves/consultar
Consulta información de un COVE por folio.

#### Request
```json
{
    "cove": "ABC123456"
}
```

#### Response (Éxito)
```json
{
    "success": true,
    "message": "COVE consultado exitosamente",
    "data": {
        "cove": "ABC123456",
        "metodo_valoracion": "1",
        "numero_factura": "FAC-789012",
        "fecha_expedicion": "2025-12-22",
        "emisor": "Empresa Emisor",
        "edocument": "EDOC123"
    }
}
```

#### Response (Error)
```json
{
    "success": false,
    "message": "El COVE no existe o no está asociado al RFC configurado",
    "error_type": "cove_not_found"
}
```

## 🛡️ Seguridad Implementada

### WS-Security
- **UsernameToken** con PasswordText
- **RFC del usuario** como username
- **Clave webservice** cifrada en BD

### Validaciones
- **Formato de COVE**: Solo alfanuméricos, guiones, guiones bajos
- **Longitud**: 1-50 caracteres
- **Autenticación requerida**: Usuario debe estar logueado

### Logging Seguro
- **NO se loguean passwords**
- **Solo IDs y errores necesarios**
- **Información sensible filtrada**

## 🐛 Troubleshooting

### Error: "Extensión SOAP no habilitada"
```bash
# Ubuntu/Debian
sudo apt-get install php-soap
sudo systemctl reload apache2

# Windows XAMPP
# Descomentar ;extension=soap en php.ini
```

### Error: "WSDL no encontrado"
```bash
# Verificar que existe el archivo
ls -la wsdl/vucem/ConsultarRespuestaCove.wsdl

# Si no existe, descargar desde VUCEM
```

### Error: "Credenciales no configuradas"
```bash
# Verificar .env
cat .env | grep VUCEM

# Limpiar cache de config
php artisan config:clear
```

### Error: "COVE no encontrado"
- Verificar que el folio sea correcto
- Confirmar que el COVE esté asociado al RFC configurado
- Probar con otro COVE conocido

### Error SOAP Fault
- Verificar credenciales en VUCEM
- Comprobar conectividad a internet
- Revisar logs: `storage/logs/laravel.log`

## 📈 Monitoreo y Logs

### Logs Importantes
```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log | grep COVE

# Buscar errores SOAP
grep "SOAP Fault" storage/logs/laravel.log

# Ver consultas exitosas
grep "COVE consultado exitosamente" storage/logs/laravel.log
```

### Métricas Sugeridas
- Número de COVEs consultados por día
- Tasa de éxito/error por tipo
- Tiempo promedio de respuesta
- COVEs más consultados

## 🔄 Mantenimiento

### Actualizaciones del WSDL
1. Descargar nuevo WSDL desde VUCEM
2. Reemplazar en `wsdl/vucem/ConsultarRespuestaCove.wsdl`
3. Ejecutar `php artisan cove:test-config`
4. Probar en staging antes de producción

### Rotación de Credenciales
1. Obtener nuevas credenciales de VUCEM
2. Actualizar `VUCEM_WS_PASSWORD` en .env
3. Ejecutar `php artisan config:clear`
4. Probar con comando: `php artisan cove:test-config`

## 🎯 Próximos Pasos

### Mejoras Sugeridas
- [ ] Cache de resultados de COVE (Redis/Database)
- [ ] Rate limiting para evitar spam
- [ ] Métricas y dashboard de uso
- [ ] Notificaciones de errores críticos
- [ ] Soporte para múltiples RFC por usuario
- [ ] Integración con otros servicios VUCEM

### Optimizaciones
- [ ] Pool de conexiones SOAP
- [ ] Compresión de requests/responses
- [ ] Timeout inteligente por ambiente
- [ ] Retry automático en errores temporales

---

## 📞 Soporte

Para problemas o preguntas sobre esta integración:

1. **Verificar logs**: `storage/logs/laravel.log`
2. **Ejecutar diagnósticos**: `php artisan cove:test-config`
3. **Revisar configuración**: `/api/coves/check-config`
4. **Consultar documentación VUCEM**

**¡Sistema listo para producción!** 🚀