# MVE - Sistema de Manifestación de Valor en Efectivo

Sistema web desarrollado en Laravel para la gestión de Manifestaciones de Valor (COVE) ante VUCEM (Ventanilla Única de Comercio Exterior Mexicana).

## 📋 Descripción

MVE permite a los usuarios:
- Crear y gestionar manifestaciones de valor (COVE)
- Consultar eDocuments y acuses de VUCEM
- Integración con Web Services SOAP de VUCEM/SAT
- Firma electrónica con e.firma (FIEL)
- Descarga de acuses en PDF
- Consulta de datos estructurados de COVE

## 🚀 Características Principales

### Integración VUCEM
- ✅ **ConsultaAcusesService**: Descarga de acuses PDF (eDocument y COVE)
- ✅ **ConsultarRespuestaCoveService**: Consulta de datos estructurados de COVE
- 🔒 **WS-Security**: Autenticación con UsernameToken
- 📝 **e.firma**: Firma digital con certificado SAT (.cer + .key)

### Servicios Web Implementados

| Servicio | Propósito | Endpoint |
|----------|-----------|----------|
| **ConsultaAcuses** | Descargar PDF de acuses | `ventanillaunica.gob.mx/ventanilla-acuses-HA/ConsultaAcusesServiceWS` |
| **ConsultarRespuestaCove** | Obtener datos estructurados | `ventanillaunica.gob.mx:8110/.../ConsultarRespuestaCoveService` |

## 📚 Documentación

- [Integración ConsultaAcuses](docs/ACUSES_INTEGRATION.md) - Descarga de acuses PDF
- [Servicio ConsultarRespuestaCove](docs/CONSULTAR_RESPUESTA_COVE.md) - Consulta de datos COVE
- [Documentación General COVE](docs/COVE_DOCUMENTATION.md) - Información general
- [Seguridad COVE](docs/SEGURIDAD_COVE.md) - Controles de seguridad

## 🛠️ Requisitos

- PHP >= 8.1
- Laravel 11.x
- MySQL/MariaDB
- Composer
- Node.js y NPM
- Extensiones PHP: soap, openssl, dom, mbstring

## 📦 Instalación

```bash
# Clonar repositorio
git clone <repository-url>
cd MVE

# Instalar dependencias PHP
composer install

# Instalar dependencias JavaScript
npm install

# Configurar archivo .env
cp .env.example .env
php artisan key:generate

# Ejecutar migraciones
php artisan migrate

# Compilar assets
npm run build
```

## ⚙️ Configuración VUCEM

### 1. Credenciales de Usuario

En la tabla `users`, cada usuario debe tener:
```php
$user->rfc = 'NET070608EM9';              // RFC registrado en VUCEM
$user->webservice_key = 'clave_64_chars'; // Encriptada
```

### 2. Archivos e.firma

Colocar archivos en `pruebaEfirma/`:
```
pruebaEfirma/
├── 00001000000716248795.cer              # Certificado
├── Claveprivada_FIEL_NET070608EM9_*.key  # Llave privada
└── CONTRASEÑA.txt                         # Contraseña
```

### 3. Configuración en .env

```env
# Credenciales VUCEM (por usuario en BD)
VUCEM_RFC=NET070608EM9

# Endpoints (opcional, hay defaults)
VUCEM_CONSULTA_ACUSES_ENDPOINT=https://www.ventanillaunica.gob.mx/ventanilla-acuses-HA/ConsultaAcusesServiceWS
VUCEM_CONSULTAR_COVE_ENDPOINT=https://www.ventanillaunica.gob.mx:8110/ventanilla/ConsultarRespuestaCoveService

# e.firma
E_FIRMA_PATH=pruebaEfirma

# Seguridad
COVE_RECIBIR_ENABLED=false  # ⚠️ Deshabilitar RecibirCove en producción
```

## 🧪 Comandos Artisan de Prueba

### Probar ConsultaAcuses (descargar PDF)
```bash
# Con folio eDocument
php artisan vucem:test-consulta-acuses 0170220LIS5D4 --user=1

# Con folio COVE
php artisan vucem:test-consulta-acuses COVE214KNPVU4 --user=1 --tipo=COVE
```

### Probar ConsultarRespuestaCove (datos estructurados)
```bash
# Con número de operación
php artisan vucem:test-consultar-cove 1234567890 --user=1

# Con salida verbose (muestra XML)
php artisan vucem:test-consultar-cove 1234567890 -v
```

## 🧪 Tests

```bash
# Ejecutar todos los tests
php artisan test

# Tests específicos
php artisan test --filter=ConsultarRespuestaCoveServiceTest
php artisan test tests/Unit/Services/Vucem/
```

## 📁 Estructura del Proyecto

```
app/
├── Console/Commands/
│   ├── TestConsultaAcusesCommand.php          # Test descarga acuses
│   └── TestConsultarRespuestaCoveCommand.php  # Test consulta COVE
├── Http/Controllers/
│   ├── AcuseController.php                    # Descarga acuses web
│   └── ManifestationController.php            # Gestión COVE
├── Models/
│   ├── Manifestation.php                      # Modelo COVE
│   └── User.php                               # Usuario con credenciales
└── Services/Vucem/
    ├── ConsultaAcusesService.php              # Descarga PDF acuses
    ├── ConsultarRespuestaCoveService.php      # Consulta datos COVE
    └── EFirmaService.php                      # Firma electrónica

config/
└── vucem.php                                   # Configuración VUCEM

docs/
├── ACUSES_INTEGRATION.md                      # Doc ConsultaAcuses
├── CONSULTAR_RESPUESTA_COVE.md                # Doc ConsultarRespuesta
├── COVE_DOCUMENTATION.md                      # Doc general COVE
└── SEGURIDAD_COVE.md                          # Seguridad

wsdl/vucem/
├── ACUSES/
│   ├── ConsultaAcusesServiceWS.wsdl
│   └── xsd1.xsd, xsd2.xsd
└── COVE/
    ├── ConsultarRespuestaCoveService.wsdl
    └── ConsultarRespuestaCove_xsd1.xsd

tests/
└── Unit/Services/Vucem/
    └── ConsultarRespuestaCoveServiceTest.php
```

## 🔐 Seguridad

### ⚠️ IMPORTANTE: Control de RecibirCove

El servicio `RecibirCove` genera trámites **REALES** ante el SAT. Para evitar envíos accidentales:

```php
// En config/vucem.php
'cove_recibir_enabled' => env('COVE_RECIBIR_ENABLED', false),
```

**Recomendaciones:**
- ❌ Mantener `COVE_RECIBIR_ENABLED=false` en producción
- ✅ Solo habilitar cuando sea necesario generar COVEs
- ✅ Validar exhaustivamente datos antes de enviar
- ✅ Registrar logs de todos los envíos

### Cifrado de Credenciales

```php
// Las credenciales se cifran automáticamente en BD
$user->webservice_key = 'clave_en_texto_plano';
$user->save(); // Se cifra automáticamente

// Se descifran al usar
$key = $user->getDecryptedWebserviceKey();
```

## 📊 Logs

Los servicios generan logs detallados en `storage/logs/laravel.log`:

```
[CONSULTA-ACUSES] Iniciando consulta de acuse
[CONSULTA-ACUSES] Request SOAP enviado (xxx bytes)
[CONSULTA-ACUSES] Respuesta recibida (xxx bytes)
[CONSULTA-ACUSES] Acuse guardado: storage/app/acuses/xxx.pdf

[CONSULTAR-RESPUESTA-COVE] Iniciando consulta
[E-FIRMA] Generando firma electrónica
[E-FIRMA] Firma generada exitosamente
[CONSULTAR-RESPUESTA-COVE] Respuesta procesada exitosamente
```

## 🐛 Solución de Problemas

### Error: "SOAP Fault: Unauthorized"
**Causa:** Credenciales incorrectas
**Solución:** Verificar RFC y webservice_key del usuario

### Error: "Error generando firma electrónica"
**Causa:** Archivos e.firma incorrectos
**Solución:** Verificar archivos .cer, .key y CONTRASEÑA.txt

### Error: "HTTP 500" en VUCEM
**Causa:** Error del servidor o XML mal formado
**Solución:** Revisar logs, verificar estructura XML

### Error: "Número de operación no encontrado"
**Causa:** numeroOperacion inválido o no pertenece al RFC
**Solución:** Verificar que el COVE fue enviado exitosamente

## 🤝 Contribución

Para contribuir al proyecto:

1. Fork el repositorio
2. Crea una rama: `git checkout -b feature/nueva-funcionalidad`
3. Commit: `git commit -m 'Agregar nueva funcionalidad'`
4. Push: `git push origin feature/nueva-funcionalidad`
5. Abre un Pull Request

## 📄 Licencia

Este proyecto es privado y confidencial.

---

## 🔄 Historial de Cambios

### v1.2.0 (Diciembre 2025)
- ✅ Implementado ConsultarRespuestaCoveService
- ✅ Soporte para firma electrónica con e.firma
- ✅ Comando de prueba para ConsultarRespuestaCove
- ✅ Tests unitarios
- ✅ Documentación completa

### v1.1.0 (Diciembre 2025)
- ✅ Implementado ConsultaAcusesService
- ✅ Descarga de acuses PDF (eDocument y COVE)
- ✅ Parser MTOM para respuestas multipart
- ✅ Componente Blade para descarga de acuses
- ✅ Caché de acuses

### v1.0.0
- ✅ Sistema base de gestión de manifestaciones
- ✅ Autenticación y usuarios
- ✅ CRUD de manifestaciones

---

**Última actualización:** 29 de diciembre de 2025
