<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Exception;

class CertificateService
{
    /**
     * Validar que el archivo tenga la extensión correcta
     */
    public function validateFileExtension(UploadedFile $file, string $expectedExtension): bool
    {
        return strtolower($file->getClientOriginalExtension()) === $expectedExtension;
    }

    /**
     * Validar que la llave privada y la contraseña sean correctas
     */
    public function validatePrivateKey(string $keyBinary, string $password): bool
    {
        try {
            // Convertir binario a PEM temporal para validación
            $pemKey = $this->convertDerToPemForValidation($keyBinary, $password);
            return $pemKey !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validar que el certificado sea válido y coincida con la llave privada
     */
    public function validateCertificate(string $certBinary): bool
    {
        try {
            // Convertir DER a PEM para validación
            $pemCert = "-----BEGIN CERTIFICATE-----\n" . 
                      chunk_split(base64_encode($certBinary), 64, "\n") . 
                      "-----END CERTIFICATE-----";

            $certResource = openssl_x509_read($pemCert);
            if (!$certResource) {
                return false;
            }

            // Verificar que el certificado esté vigente
            $certData = openssl_x509_parse($certResource);
            $now = time();
            
            return $certData['validFrom_time_t'] <= $now && $certData['validTo_time_t'] >= $now;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validar que la llave privada coincida con el certificado
     */
    public function validateKeyPairMatch(string $certBinary, string $keyBinary, string $password): bool
    {
        try {
            // Convertir certificado a PEM
            $pemCert = "-----BEGIN CERTIFICATE-----\n" . 
                      chunk_split(base64_encode($certBinary), 64, "\n") . 
                      "-----END CERTIFICATE-----";

            // Convertir llave a PEM
            $pemKey = $this->convertDerToPemForValidation($keyBinary, $password);
            if (!$pemKey) {
                return false;
            }

            // Verificar que las llaves coincidan
            return openssl_x509_check_private_key($pemCert, $pemKey);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Cargar certificados al perfil del usuario
     */
    public function uploadCertificatesToUser(
        User $user, 
        UploadedFile $certificateFile, 
        UploadedFile $privateKeyFile, 
        string $password
    ): array {
        // Validar extensiones de archivos
        if (!$this->validateFileExtension($certificateFile, 'cer')) {
            return ['success' => false, 'message' => 'El archivo del certificado debe tener extensión .cer'];
        }

        if (!$this->validateFileExtension($privateKeyFile, 'key')) {
            return ['success' => false, 'message' => 'El archivo de la llave privada debe tener extensión .key'];
        }

        // Leer contenido binario de los archivos
        $certBinary = file_get_contents($certificateFile->getRealPath());
        $keyBinary = file_get_contents($privateKeyFile->getRealPath());

        if (!$certBinary || !$keyBinary) {
            return ['success' => false, 'message' => 'Error al leer los archivos'];
        }

        // Validar certificado
        if (!$this->validateCertificate($certBinary)) {
            return ['success' => false, 'message' => 'El certificado no es válido o ha expirado'];
        }

        // Validar llave privada y contraseña
        if (!$this->validatePrivateKey($keyBinary, $password)) {
            return ['success' => false, 'message' => 'La contraseña de la llave privada es incorrecta'];
        }

        // Validar que la llave y el certificado coincidan
        if (!$this->validateKeyPairMatch($certBinary, $keyBinary, $password)) {
            return ['success' => false, 'message' => 'La llave privada no corresponde al certificado'];
        }

        // Guardar en el usuario (se cifran automáticamente por el modelo)
        $user->setCertificateFromBinary($certBinary);
        $user->setPrivateKeyFromBinary($keyBinary);
        $user->setFielPassword($password);
        $user->use_system_certificates = true;
        $user->save();

        return ['success' => true, 'message' => 'Certificados cargados exitosamente'];
    }

    /**
     * Eliminar certificados del usuario
     */
    public function removeCertificatesFromUser(User $user): void
    {
        $user->clearFielCertificates();
        $user->use_system_certificates = false;
        $user->save();
    }

    /**
     * Obtener información del certificado del usuario
     */
    public function getCertificateInfo(User $user): ?array
    {
        if (!$user->hasFielCertificates()) {
            return null;
        }

        try {
            $certBinary = $user->getDecryptedCertificate();
            $pemCert = "-----BEGIN CERTIFICATE-----\n" . 
                      chunk_split(base64_encode($certBinary), 64, "\n") . 
                      "-----END CERTIFICATE-----";

            $certData = openssl_x509_parse($pemCert);
            
            return [
                'subject' => $certData['subject'] ?? [],
                'issuer' => $certData['issuer'] ?? [],
                'valid_from' => date('d/m/Y', $certData['validFrom_time_t']),
                'valid_to' => date('d/m/Y', $certData['validTo_time_t']),
                'is_valid' => $certData['validFrom_time_t'] <= time() && $certData['validTo_time_t'] >= time(),
                'uploaded_at' => $user->fiel_uploaded_at ? $user->fiel_uploaded_at->format('d/m/Y H:i:s') : null,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Convierte una llave DER a PEM para validación
     */
    private function convertDerToPemForValidation(string $derData, string $password): mixed
    {
        try {
            $pem = "-----BEGIN ENCRYPTED PRIVATE KEY-----\n" . 
                   chunk_split(base64_encode($derData), 64, "\n") . 
                   "-----END ENCRYPTED PRIVATE KEY-----";

            return openssl_pkey_get_private($pem, $password);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Crear archivos temporales para uso en firma (solo durante la ejecución)
     */
    public function createTemporaryFiles(User $user): ?array
    {
        if (!$user->hasFielCertificates()) {
            return null;
        }

        try {
            $tempDir = sys_get_temp_dir();
            $tempCertPath = $tempDir . '/cert_' . $user->id . '_' . time() . '.cer';
            $tempKeyPath = $tempDir . '/key_' . $user->id . '_' . time() . '.key';

            // Escribir archivos temporales
            file_put_contents($tempCertPath, $user->getDecryptedCertificate());
            file_put_contents($tempKeyPath, $user->getDecryptedPrivateKey());

            return [
                'certificate_path' => $tempCertPath,
                'private_key_path' => $tempKeyPath,
                'password' => $user->getDecryptedPassword()
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Limpiar archivos temporales
     */
    public function cleanupTemporaryFiles(array $filePaths): void
    {
        foreach ($filePaths as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
}