<?php

namespace App\Services\Vucem;

use Exception;
use Illuminate\Support\Facades\Log;

class EFirmaService
{
    private string $efirmaPath;
    private string $certFile;
    private string $keyFile;
    private string $passwordFile;
    private ?string $keyPassword = null;

    public function __construct()
    {
        $this->efirmaPath = config('vucem.efirma.path');
        $this->certFile = config('vucem.efirma.cert_file', 'certificado.cer');
        $this->keyFile = config('vucem.efirma.key_file', 'llave.key');
        $this->passwordFile = config('vucem.efirma.password_file', 'CONTRASEÑA.txt');
        
        // La contraseña se lee dinámicamente cuando se necesita
    }

    /**
     * Lee la contraseña desde el archivo de contraseña
     *
     * @return string
     * @throws Exception
     */
    private function getKeyPassword(): string
    {
        if ($this->keyPassword !== null) {
            return $this->keyPassword;
        }

        $passwordPath = base_path($this->efirmaPath . DIRECTORY_SEPARATOR . $this->passwordFile);
        
        if (!file_exists($passwordPath)) {
            throw new Exception("Archivo de contraseña no encontrado: {$passwordPath}");
        }

        $password = file_get_contents($passwordPath);
        if ($password === false) {
            throw new Exception("No se pudo leer el archivo de contraseña: {$passwordPath}");
        }

        // Limpiar espacios y saltos de línea
        $this->keyPassword = trim($password);
        
        if (empty($this->keyPassword)) {
            throw new Exception("El archivo de contraseña está vacío: {$passwordPath}");
        }

        return $this->keyPassword;
    }

    /**
     * Genera la firma electrónica para consulta COVE
     *
     * @param string $numeroOperacion
     * @param string $rfc
     * @return array ['certificado' => base64, 'cadenaOriginal' => string, 'firma' => base64]
     * @throws Exception
     */
    public function generarFirmaElectronica(string $numeroOperacion, string $rfc): array
    {
        try {
            Log::info("[E-FIRMA] Iniciando generación de firma", [
                'numero_operacion' => $numeroOperacion,
                'rfc' => $rfc
            ]);

            // 1. Leer y convertir certificado a Base64
            $certificadoBase64 = $this->getCertificadoBase64();

            // 2. Construir cadena original con formato exacto
            $cadenaOriginal = "|{$numeroOperacion}|{$rfc}|";

            // 3. Generar firma de la cadena
            $firmaBase64 = $this->firmarCadena($cadenaOriginal);

            Log::info("[E-FIRMA] Firma generada exitosamente", [
                'cadena_original' => $cadenaOriginal,
                'certificado_length' => strlen($certificadoBase64),
                'firma_length' => strlen($firmaBase64)
            ]);

            return [
                'certificado' => $certificadoBase64,
                'cadenaOriginal' => $cadenaOriginal,
                'firma' => $firmaBase64
            ];

        } catch (Exception $e) {
            Log::error("[E-FIRMA] Error generando firma electrónica: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lee el certificado .cer y lo convierte a Base64
     *
     * @return string
     * @throws Exception
     */
    public function getCertificadoBase64(): string
    {
        $certPath = base_path($this->efirmaPath . DIRECTORY_SEPARATOR . $this->certFile);
        
        if (!file_exists($certPath)) {
            throw new Exception("Archivo certificado no encontrado: {$certPath}");
        }

        $certContent = file_get_contents($certPath);
        if ($certContent === false) {
            throw new Exception("No se pudo leer el archivo certificado: {$certPath}");
        }

        // Si el archivo ya está en formato PEM, extraer solo el contenido Base64
        if (strpos($certContent, '-----BEGIN CERTIFICATE-----') !== false) {
            $certContent = str_replace(['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', "\n", "\r"], '', $certContent);
        } else {
            // Si es archivo .cer binario, convertir a Base64
            $certContent = base64_encode($certContent);
        }

        return trim($certContent);
    }

    /**
     * Firma la cadena original con la llave privada
     *
     * @param string $cadenaOriginal
     * @return string Base64 de la firma
     * @throws Exception
     */
    private function firmarCadena(string $cadenaOriginal): string
    {
        $keyPath = base_path($this->efirmaPath . DIRECTORY_SEPARATOR . $this->keyFile);
        
        if (!file_exists($keyPath)) {
            throw new Exception("Archivo llave privada no encontrado: {$keyPath}");
        }

        $password = $this->getKeyPassword();
        
        // Leer el contenido de la llave privada
        $keyContent = file_get_contents($keyPath);
        if ($keyContent === false) {
            throw new Exception("No se pudo leer el archivo de llave privada");
        }
        
        // Intentar cargar la llave privada (formato DER encriptado del SAT)
        $privateKey = $this->loadPrivateKey($keyContent, $password);

        if (!$privateKey) {
            throw new Exception("Error cargando llave privada: " . openssl_error_string());
        }

        // Firmar la cadena
        $signature = '';
        $success = openssl_sign(
            $cadenaOriginal,
            $signature,
            $privateKey,
            config('vucem.efirma.signature_algorithm', OPENSSL_ALGO_SHA256)
        );

        // Liberar recursos
        openssl_free_key($privateKey);

        if (!$success) {
            throw new Exception("Error firmando cadena: " . openssl_error_string());
        }

        return base64_encode($signature);
    }

    /**
     * Firma una cadena y devuelve la firma en Base64
     *
     * @param string $cadenaOriginal
     * @return string
     * @throws Exception
     */
    public function firmarCadenaBase64(string $cadenaOriginal): string
    {
        return $this->firmarCadena($cadenaOriginal);
    }

    /**
     * Verifica que los archivos de e.firma estén presentes y sean válidos
     *
     * @return array Status de verificación
     */
    public function verificarArchivos(): array
    {
        $status = [
            'cert_exists' => false,
            'key_exists' => false,
            'password_file_exists' => false,
            'cert_readable' => false,
            'key_readable' => false,
            'password_readable' => false,
            'password_valid' => false,
            'errors' => []
        ];

        $certPath = base_path($this->efirmaPath . DIRECTORY_SEPARATOR . $this->certFile);
        $keyPath = base_path($this->efirmaPath . DIRECTORY_SEPARATOR . $this->keyFile);
        $passwordPath = base_path($this->efirmaPath . DIRECTORY_SEPARATOR . $this->passwordFile);

        // Verificar certificado
        $status['cert_exists'] = file_exists($certPath);
        if ($status['cert_exists']) {
            $status['cert_readable'] = is_readable($certPath);
        } else {
            $status['errors'][] = "Certificado no encontrado: {$certPath}";
        }

        // Verificar llave privada
        $status['key_exists'] = file_exists($keyPath);
        if ($status['key_exists']) {
            $status['key_readable'] = is_readable($keyPath);
        } else {
            $status['errors'][] = "Llave privada no encontrada: {$keyPath}";
        }

        // Verificar archivo de contraseña
        $status['password_file_exists'] = file_exists($passwordPath);
        if ($status['password_file_exists']) {
            $status['password_readable'] = is_readable($passwordPath);
            if ($status['password_readable']) {
                try {
                    $password = $this->getKeyPassword();
                    $status['password_valid'] = !empty($password);
                } catch (\Exception $e) {
                    $status['errors'][] = "Error leyendo contraseña: " . $e->getMessage();
                }
            }
        } else {
            $status['errors'][] = "Archivo de contraseña no encontrado: {$passwordPath}";
        }

        return $status;
    }

    /**
     * Carga una llave privada del SAT en formato DER encriptado
     *
     * @param string $keyContent Contenido binario de la llave
     * @param string $password Contraseña de la llave
     * @return resource|false Recurso de la llave privada o false en caso de error
     */
    private function loadPrivateKey(string $keyContent, string $password)
    {
        // Método 1: Intentar cargar directamente (formato binario DER)
        $privateKey = openssl_pkey_get_private($keyContent, $password);
        if ($privateKey) {
            return $privateKey;
        }

        // Método 2: Intentar convertir a formato PEM ENCRYPTED PRIVATE KEY
        $keyBase64 = base64_encode($keyContent);
        $keyPem = "-----BEGIN ENCRYPTED PRIVATE KEY-----\n" . 
                  chunk_split($keyBase64, 64, "\n") . 
                  "-----END ENCRYPTED PRIVATE KEY-----\n";
        
        $privateKey = openssl_pkey_get_private($keyPem, $password);
        if ($privateKey) {
            return $privateKey;
        }

        // Método 3: Intentar con formato PRIVATE KEY sin encriptar
        $keyPem = "-----BEGIN PRIVATE KEY-----\n" . 
                  chunk_split($keyBase64, 64, "\n") . 
                  "-----END PRIVATE KEY-----\n";
        
        $privateKey = openssl_pkey_get_private($keyPem, $password);
        if ($privateKey) {
            return $privateKey;
        }

        // Método 4: Si todo falla, intentar detectar si es formato PEM ya
        if (strpos($keyContent, '-----BEGIN') !== false) {
            $privateKey = openssl_pkey_get_private($keyContent, $password);
            if ($privateKey) {
                return $privateKey;
            }
        }

        return false;
    }
}