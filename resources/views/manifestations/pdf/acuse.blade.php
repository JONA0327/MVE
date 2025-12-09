<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Acuse de Recibo - Manifestación de Valor</title>
    <style>
        @page {
            margin: 100px 25px 60px 25px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }
        /* Header fijo */
        header {
            position: fixed;
            top: -70px;
            left: 0px;
            right: 0px;
            height: 60px;
            border-bottom: 2px solid #1E40AF; /* Azul E&I */
            padding-bottom: 5px;
        }
        /* Footer fijo */
        footer {
            position: fixed;
            bottom: -40px;
            left: 0px;
            right: 0px;
            height: 30px;
            text-align: center;
            font-size: 8pt;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }
        .header-logo {
            float: left;
            width: 150px;
        }
        /* Logo simulado con texto si no carga la imagen, o imagen real */
        .logo-text {
            font-size: 16pt;
            font-weight: bold;
            color: #0F172A; /* Slate 900 */
            letter-spacing: -1px;
        }
        .header-info {
            float: right;
            text-align: right;
            font-size: 9pt;
            color: #475569;
        }
        .doc-title {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            color: #0F172A; /* Slate 900 */
            margin-top: 20px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .doc-subtitle {
            text-align: center;
            font-size: 11pt;
            color: #1E40AF; /* Azul Acento */
            margin-bottom: 30px;
            font-weight: bold;
        }
        
        /* Secciones */
        .section-header {
            background-color: #f8fafc;
            color: #0F172A;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 10pt;
            border-left: 5px solid #1E40AF; /* Borde Azul Corporativo */
            margin-top: 15px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        /* Tablas */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        td {
            padding: 6px 8px;
            vertical-align: top;
            font-size: 9pt;
            border-bottom: 1px solid #e2e8f0;
        }
        .label {
            font-weight: bold;
            color: #475569;
            width: 25%;
            background-color: #f8fafc;
        }
        .value {
            color: #0f172a;
            width: 25%;
        }
        .value-wide {
            width: 75%;
        }

        /* Cajas Técnicas */
        .technical-box {
            font-family: 'Courier New', Courier, monospace;
            font-size: 8pt;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 10px;
            word-wrap: break-word;
            text-align: justify;
            color: #334155;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .qr-area {
            float: right;
            width: 120px;
            height: 120px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #94a3b8;
            font-size: 8pt;
            margin-left: 20px;
            background-color: #fff;
        }
        .qr-placeholder {
            padding-top: 45px;
            color: #cbd5e1;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-logo">
            <!-- Si tienes la imagen en base64 o ruta absoluta del servidor, úsala aquí. -->
            <!-- Por compatibilidad simple en PDF, usamos texto estilizado -->
            <div class="logo-text">E&I <span style="font-size: 10pt; color: #1E40AF;">Comercio Exterior</span></div>
        </div>
        <div class="header-info">
            <strong>Folio UUID:</strong> {{ substr($m->uuid, 0, 8) }}...<br>
            <strong>Emisión:</strong> {{ $fecha_impresion }}
        </div>
    </header>

    <footer>
        <p>E&I Comercio Exterior, Logística y Tecnología - Documento generado electrónicamente.<br> 
           Este acuse es comprobante de la transmisión de información al sistema.</p>
    </footer>

    <div class="doc-title">Acuse de Recibo</div>
    <div class="doc-subtitle">Manifestación de Valor Electrónica</div>

    <!-- Sección 1 -->
    <div class="section-header">1. Información del Contribuyente</div>
    <table>
        <tr>
            <td class="label">RFC Importador:</td>
            <td class="value"><strong>{{ $m->rfc_importador }}</strong></td>
            <td class="label">RFC Solicitante:</td>
            <td class="value">{{ $m->rfc_solicitante }}</td>
        </tr>
        <tr>
            <td class="label">Razón Social:</td>
            <td class="value value-wide" colspan="3">{{ $m->razon_social_importador }}</td>
        </tr>
         <tr>
            <td class="label">Solicitante:</td>
            <td class="value value-wide" colspan="3">
                {{ $m->nombre }} {{ $m->apellido_paterno }} {{ $m->apellido_materno }}
            </td>
        </tr>
    </table>

    <!-- Sección 2 -->
    <div class="section-header">2. Valores Declarados (MXN)</div>
    <table>
        <tr>
            <td class="label" style="background-color: #eff6ff; color: #1e40af;">Valor en Aduana:</td>
            <td class="value" style="background-color: #eff6ff; font-size: 11pt;"><strong>${{ number_format($m->total_valor_aduana, 2) }}</strong></td>
            <td class="label">INCOTERM:</td>
            <td class="value">{{ $m->incoterm ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Precio Pagado:</td>
            <td class="value">${{ number_format($m->total_precio_pagado, 2) }}</td>
            <td class="label">Precio Por Pagar:</td>
            <td class="value">${{ number_format($m->total_precio_por_pagar, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Incrementables:</td>
            <td class="value">${{ number_format($m->total_incrementables, 2) }}</td>
            <td class="label">Decrementables:</td>
            <td class="value">${{ number_format($m->total_decrementables, 2) }}</td>
        </tr>
    </table>

    <!-- Sección 3 -->
    <div style="margin-top: 30px;">
        <div class="qr-area">
            <div class="qr-placeholder">QR CODE</div>
        </div>
        
        <div style="margin-right: 140px;">
            <div class="section-header" style="margin-top: 0;">3. Cadena Original</div>
            <div class="technical-box">
                {{ $m->cadena_original ?? '|| Sin cadena generada ||' }}
            </div>

            <div class="section-header">4. Sello Digital</div>
            <div class="technical-box">
                {{ $m->sello_digital ?? 'Firma pendiente' }}
            </div>
        </div>
    </div>
</body>
</html>