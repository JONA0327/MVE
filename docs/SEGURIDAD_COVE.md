# 🛡️ SEGURIDAD COVE - Guía de Servicios VUCEM

## ⚠️ IMPORTANTE: Diferencias Críticas entre Servicios

### 🔍 **CONSULTA** (SEGURO para pruebas)

#### `ConsultarRespuestaCoveService`
- ✅ **Solo consulta** información existente
- ✅ **NO genera** trámites nuevos
- ✅ **NO modifica** nada en VUCEM  
- ✅ **Seguro** para desarrollo y pruebas
- ✅ **Uso**: Verificar si existe COVE para un número de operación

**Flujo seguro:**
```
numeroOperacion (ej: 5000745) + firmaElectronica 
    ↓
Solo LEE el eDocument (COVE) y estatus existente
    ↓
No hay riesgo de crear trámites
```

---

### ⚠️ **GENERACIÓN** (PELIGROSO - Uso restringido)

#### `RecibirCoveService` 
- ❌ **Genera COVEs nuevos** en sistema SAT
- ❌ **Crea trámites REALES**
- ❌ **Queda registrado** ante autoridades
- ❌ **Es un acto oficial**
- ❌ **NO usar** en desarrollo casual

**Riesgos:**
- Crea registros oficiales ante SAT
- Puede afectar estatus fiscales reales
- Irreversible una vez procesado

---

## 🔒 Configuración de Seguridad Implementada

### Bandera de Control
```env
# .env
COVE_RECIBIR_ENABLED=false  # ⚠️ Deshabilita RecibirCove por seguridad
```

### Validación en Código
```php
// El sistema automáticamente bloquea RecibirCove cuando está deshabilitado
if (!config('vucem.cove_recibir_enabled')) {
    throw new Exception("RecibirCove deshabilitado por seguridad");
}
```

---

## ✅ Flujo de Trabajo Recomendado

### 1. Desarrollo y Pruebas (Modo Seguro)
```bash
# .env
COVE_RECIBIR_ENABLED=false
```

**Usar SOLO:**
- ✅ `ConsultarRespuestaCove` para verificar COVEs existentes
- ✅ Parseo de archivos M con COVE en línea 505
- ✅ Pruebas de e.firma y autenticación

### 2. Producción Controlada (Solo cuando sea necesario)
```bash
# .env  
COVE_RECIBIR_ENABLED=true  # ⚠️ Solo para trámites reales autorizados
```

**Usar con extrema precaución:**
- ⚠️ `RecibirCove` solo para operaciones oficiales autorizadas
- ⚠️ Validar datos exhaustivamente antes de enviar
- ⚠️ Documentar cada uso

---

## 📋 Checklist de Seguridad

### Antes de Desplegar
- [ ] Verificar `COVE_RECIBIR_ENABLED=false` en desarrollo
- [ ] Confirmar que solo personal autorizado puede cambiar la bandera
- [ ] Probar ConsultarRespuestaCove funciona correctamente
- [ ] Validar que RecibirCove se bloquea cuando está deshabilitado

### Antes de Habilitar RecibirCove
- [ ] Autorización explícita del responsable del proyecto
- [ ] Validación completa de datos de prueba
- [ ] Confirmar que es ambiente de pruebas SAT
- [ ] Backup de configuración actual
- [ ] Plan de rollback definido

---

## 🚨 Comandos por Entorno

### Desarrollo (Seguro)
```bash
# Consultar COVE existente (seguro)
php artisan cove:consultar 5000745

# Parsear archivo M (seguro)  
php artisan mfile:parse archivo.m
```

### Producción (Solo cuando sea necesario)
```bash
# ⚠️ Solo con autorización y datos validados
php artisan cove:generar --autorizado --validado
```

---

## 🔧 Troubleshooting

### "RecibirCove deshabilitado en este entorno"
- **Causa**: Bandera de seguridad activada
- **Solución**: Solo cambiar si es necesario crear trámites reales
- **Precaución**: Verificar dos veces antes de habilitar

### Error de conectividad
- **Para ConsultarRespuestaCove**: Usar endpoint de consulta
- **Para RecibirCove**: Verificar endpoint de generación (solo si está habilitado)

---

## 📞 Contacto y Responsabilidades

En caso de dudas sobre el uso de servicios COVE:
1. Consultar con el responsable del proyecto
2. Validar con el área fiscal/legal si corresponde
3. Documentar cualquier uso de RecibirCove

**Recordatorio**: ConsultarRespuestaCove es siempre la opción segura para desarrollo y pruebas.