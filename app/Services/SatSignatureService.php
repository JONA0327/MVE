<?php

namespace App\Services;

use App\Models\Manifestation;
use Illuminate\Support\Facades\Storage;
use Exception;

class SatSignatureService
{
    /**
     * Construye la cadena original concatenando los datos con pipes "|".
     * IMPORTANTE: El orden de los campos debe coincidir estrictamente con el estándar del SAT.
     * Basado en la imagen proporcionada.
     */
    public function buildOriginalString(Manifestation $manifestation)
    {
        // Cargamos relaciones para asegurar que tenemos los datos
        $manifestation->load([
            'importador',
            'coves',
            'adjustments',
            'payments',
            'pedimentos',
            'compensations',
        ]);

        $data = [];

        // --- 1. CABECERA ---
        // Ejemplo de estructura: |RFC|TIPO_FIGURA|...
        $importador = $manifestation->importador;
        $data[] = $manifestation->rfc_solicitante;
        $data[] = $manifestation->razon_social_solicitante;
        $data[] = $manifestation->actividad_economica_solicitante;
        $data[] = $manifestation->pais_solicitante;
        $data[] = $manifestation->codigo_postal_solicitante;
        $data[] = $manifestation->estado_solicitante;
        $data[] = $manifestation->municipio_solicitante;
        $data[] = $manifestation->localidad_solicitante;
        $data[] = $manifestation->colonia_solicitante;
        $data[] = $manifestation->calle_solicitante;
        $data[] = $manifestation->numero_exterior_solicitante;
        $data[] = $manifestation->numero_interior_solicitante;
        $data[] = $manifestation->lada_solicitante;
        $data[] = $manifestation->telefono_solicitante;
        $data[] = $manifestation->correo_solicitante;
        $data[] = $importador?->rfc ?? $manifestation->rfc_importador;
        $data[] = $importador?->razon_social ?? $manifestation->razon_social_importador;
        $data[] = $importador?->registro_nacional_contribuyentes ?? $manifestation->registro_nacional_contribuyentes;
        $data[] = $importador?->domicilio_fiscal;
        $data[] = $this->formatDecimal($manifestation->total_precio_pagado, 2);
        $data[] = $this->formatDecimal($manifestation->total_incrementables, 2);
        $data[] = $this->formatDecimal($manifestation->total_decrementables, 2);
        $data[] = $this->formatDecimal($manifestation->total_valor_aduana, 2);
        $data[] = $this->formatDecimal($manifestation->total_precio_por_pagar, 2);
        $data[] = $this->formatBoolean($manifestation->existe_vinculacion);
        $data[] = $manifestation->descripcion_vinculacion;
        $data[] = $manifestation->metodo_valoracion_global;
        $data[] = $manifestation->incoterm;
        $data[] = $this->formatDate($manifestation->fecha_factura);
        $data[] = $this->formatDate($manifestation->fecha_entrada);
        $data[] = $this->formatDate($manifestation->fecha_pago_pedimento);
        $data[] = $this->formatDate($manifestation->fecha_presentacion);
        $data[] = $manifestation->observaciones_pedimento;
        $data[] = $manifestation->data_source;

        // --- 2. COVES ---
        foreach ($manifestation->coves as $cove) {
            $data[] = $cove->edocument;
            $data[] = $cove->metodo_valoracion;
            $data[] = $cove->numero_factura;
            $data[] = $this->formatDate($cove->fecha_expedicion);
            $data[] = $cove->emisor;
            $data[] = $cove->destinatario;
        }

        // --- 3. PEDIMENTOS ---
        foreach ($manifestation->pedimentos as $ped) {
            $data[] = $ped->numero_pedimento;
            $data[] = $ped->patente;
            $data[] = $ped->aduana_clave;
        }

        // --- 4. INCREMENTABLES / PAGOS ---
        // Iteramos ajustes ordenados si es necesario
        foreach ($manifestation->adjustments as $adj) {
            $data[] = $adj->type;
            $data[] = $adj->concepto;
            $data[] = $this->formatDate($adj->fecha_erogacion);
            $data[] = $this->formatDecimal($adj->importe, 2);
            $data[] = $adj->moneda;
            $data[] = $this->formatDecimal($adj->tipo_cambio, 6);
            $data[] = $this->formatBoolean($adj->a_cargo_importador);
        }

        foreach ($manifestation->payments as $payment) {
            $data[] = $payment->status;
            $data[] = $this->formatDate($payment->fecha);
            $data[] = $this->formatDecimal($payment->importe, 2);
            $data[] = $payment->forma_pago;
            $data[] = $payment->especifique;
            $data[] = $payment->moneda;
            $data[] = $this->formatDecimal($payment->tipo_cambio, 3);
            $data[] = $payment->situacion_pago;
        }

        foreach ($manifestation->compensations as $compensation) {
            $data[] = $this->formatDate($compensation->fecha);
            $data[] = $compensation->forma_pago;
            $data[] = $compensation->especifique;
            $data[] = $compensation->motivo;
            $data[] = $compensation->prestacion_mercancia;
        }
        
        // --- FINALIZAR CADENA ---
        // Debe empezar y terminar con pipes dobles o simples según anexo técnico.
        // Asumiremos formato estándar SAT: ||dato|dato||
        
        $rawString = "|" . implode("|", $data) . "|";

        // CONVERSIÓN CRÍTICA A ISO-8859-1 (Latin1)
        // El SAT no firma en UTF-8, firma en Latin1. Si esto falta, la firma es inválida.
        return mb_convert_encoding($rawString, 'ISO-8859-1', 'UTF-8');
    }

    /**
     * Genera el sello digital (Firma)
     * Algoritmo: SHA256withRSA
     */
    public function signString($cadenaOriginal, $keyFile, $password)
    {
        // 1. Leer el contenido binario del archivo .key
        $derKey = file_get_contents($keyFile->getRealPath());

        if (!$derKey) {
            throw new Exception("No se pudo leer el archivo .key");
        }

        // 2. Convertir llave DER (formato SAT) a PEM (formato OpenSSL)
        $pemKey = $this->convertDerToPem($derKey, $password);

        if (!$pemKey) {
             throw new Exception("Contraseña incorrecta o archivo .key inválido.");
        }

        // 3. Firmar usando SHA256
        $binarySignature = '';
        
        // openssl_sign computa el hash SHA256 de la cadena y luego lo cifra con la llave privada
        if (!openssl_sign($cadenaOriginal, $binarySignature, $pemKey, OPENSSL_ALGO_SHA256)) {
            throw new Exception("Error criptográfico al generar la firma: " . openssl_error_string());
        }

        // 4. Codificar resultado en Base64
        return base64_encode($binarySignature);
    }

    /**
     * Convierte una llave privada en formato DER (binario cifrado) a PEM.
     * Laravel/PHP nativo no lee DER directamente con contraseña fácilmente sin phpseclib,
     * pero este método usa un 'workaround' estándar o requiere OpenSSL instalado en el servidor.
     */
    private function convertDerToPem($derData, $password)
    {
        // Opción A: Usando funciones nativas si la versión de PHP/OpenSSL lo soporta (PHP 8+)
        // Intentamos leerla como PKCS8 cifrada
        
        // NOTA: Las llaves del SAT suelen ser PKCS#8 DER cifradas con DES-EDE3-CBC o AES.
        // A veces es necesario guardarla en disco temporalmente para usar comandos de sistema si PHP falla.
        
        // Intento directo (funciona en servidores modernos):
        $pem = "-----BEGIN ENCRYPTED PRIVATE KEY-----\n" . 
               chunk_split(base64_encode($derData), 64, "\n") . 
               "-----END ENCRYPTED PRIVATE KEY-----";

        $privateKey = openssl_pkey_get_private($pem, $password);

        if ($privateKey) {
            return $privateKey;
        }

        // Si falla, es posible que el formato de cifrado interno del DER requiera conversión externa
        // o el uso de librerías como 'phpseclib'.
        // Para este ejemplo, lanzamos error si falla el método estándar.
        return false;
    }

    private function formatDate($date): string
    {
        if (!$date) {
            return '';
        }

        return $date->format('d/m/Y');
    }

    private function formatDecimal($value, int $decimals): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return number_format((float) $value, $decimals, '.', '');
    }

    private function formatBoolean($value): string
    {
        if ($value === null) {
            return '';
        }

        return $value ? '1' : '0';
    }
}
