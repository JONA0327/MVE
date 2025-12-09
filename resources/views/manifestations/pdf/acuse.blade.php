<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Acuse de Recibo - Manifestación de Valor</title>
    <style>
        @page {
            margin: 100px 25px 60px 25px; /* Margen superior amplio para header */
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }
        /* Header fijo en cada página */
        header {
            position: fixed;
            top: -70px;
            left: 0px;
            right: 0px;
            height: 60px;
            border-bottom: 2px solid #555;
            padding-bottom: 5px;
        }
        /* Footer fijo con paginación */
        footer {
            position: fixed;
            bottom: -40px;
            left: 0px;
            right: 0px;
            height: 30px;
            text-align: center;
            font-size: 8pt;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        .header-logo {
            float: left;
            font-size: 14pt;
            font-weight: bold;
            color: #444;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .header-info {
            float: right;
            text-align: right;
            font-size: 9pt;
            color: #555;
        }
        .doc-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            color: #1a202c;
            margin-top: 20px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .doc-subtitle {
            text-align: center;
            font-size: 10pt;
            color: #718096;
            margin-bottom: 25px;
        }
        
        /* Estilos de Secciones */
        .section-header {
            background-color: #f1f5f9;
            color: #1e293b;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 10pt;
            border-left: 4px solid #3b82f6; /* Borde azul oficial */
            margin-top: 15px;
            margin-bottom: 8px;
            text-transform: uppercase;
            page-break-after: avoid;
        }

        /* Tablas de Datos */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        td {
            padding: 5px 8px;
            vertical-align: top;
            font-size: 9pt;
            border-bottom: 1px solid #e2e8f0;
        }
        .label {
            font-weight: bold;
            color: #64748b;
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

        /* Cajas de Texto Técnico (Cadenas y Sellos) */
        .technical-box {
            font-family: 'Courier New', Courier, monospace;
            font-size: 7pt;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 8px;
            word-wrap: break-word;
            text-align: justify;
            color: #334155;
            margin-bottom: 10px;
        }
        
        /* Área de QR */
        .qr-area {
            float: right;
            width: 110px;
            height: 110px;
            border: 1px dashed #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #94a3b8;
            font-size: 8pt;
            margin-left: 15px;
            background-color: #fdfdfd;
        }
        .qr-content {
            padding-top: 40px; /* Simula centrado vertical básico para PDF */
        }
    </style>
</head>
<body>
    <header>
        <div class="header-logo">
            HACIENDA <br> <span style="font-size: 10pt; font-weight: normal;">Secretaría de Hacienda y Crédito Público</span>
        </div>
        <div class="header-info">
            <strong>Folio UUID:</strong> {{ $m->uuid }}<br>
            <strong>Fecha de Emisión:</strong> {{ $fecha_impresion }}
        </div>
    </header>

    <footer>
        <p>Este documento es una representación impresa de un trámite digital realizado ante el SAT. <br> 
           La autenticidad de este documento puede ser verificada en el portal del SAT.</p>
    </footer>

    <!-- Título Principal -->
    <div class="doc-title">Acuse de Recibo</div>
    <div class="doc-subtitle">Manifestación de Valor Electrónica</div>

    <!-- Sección 1: Datos Generales -->
    <div class="section-header">1. Información del Contribuyente y Solicitante</div>
    <table>
        <tr>
            <td class="label">RFC Importador:</td>
            <td class="value">{{ $m->rfc_importador }}</td>
            <td class="label">RFC Solicitante:</td>
            <td class="value">{{ $m->rfc_solicitante }}</td>
        </tr>
        <tr>
            <td class="label">Razón Social:</td>
            <td class="value value-wide" colspan="3">{{ $m->razon_social_importador }}</td>
        </tr>
         <tr>
            <td class="label">Nombre Solicitante:</td>
            <td class="value value-wide" colspan="3">
                {{ $m->nombre }} {{ $m->apellido_paterno }} {{ $m->apellido_materno }}
            </td>
        </tr>
         <tr>
            <td class="label">CURP Solicitante:</td>
            <td class="value value-wide" colspan="3">{{ $m->curp_solicitante }}</td>
        </tr>
    </table>

    <!-- Sección 2: Resumen de Operación -->
    <div class="section-header">2. Resumen de Valores Declarados</div>
    <table>
        <tr>
            <td class="label">Valor en Aduana Total:</td>
            <td class="value"><strong>${{ number_format($m->total_valor_aduana, 2) }}</strong></td>
            <td class="label">Moneda:</td>
            <td class="value">MXN (Pesos Mexicanos)</td>
        </tr>
        <tr>
            <td class="label">Precio Pagado:</td>
            <td class="value">${{ number_format($m->total_precio_pagado, 2) }}</td>
            <td class="label">Precio Por Pagar:</td>
            <td class="value">${{ number_format($m->total_precio_por_pagar, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Total Incrementables:</td>
            <td class="value">${{ number_format($m->total_incrementables, 2) }}</td>
            <td class="label">Total Decrementables:</td>
            <td class="value">${{ number_format($m->total_decrementables, 2) }}</td>
        </tr>
    </table>

    <!-- Sección 3: Datos Técnicos y Sellos -->
    <div style="margin-top: 25px;">
        <!-- Placeholder para QR -->
        <div class="qr-area">
            <div class="qr-content">Espacio para<br>Código QR</div>
        </div>
        
        <!-- Cadenas Digitales -->
        <div style="margin-right: 130px;">
            <div class="section-header" style="margin-top: 0;">3. Cadena Original del Documento</div>
            <div class="technical-box">
                {{ $m->cadena_original }}
            </div>

            <div class="section-header">4. Sello Digital (Firma Electrónica)</div>
            <div class="technical-box">
                {{ $m->sello_digital }}
            </div>
        </div>
    </div>
    
    <div style="clear: both;"></div>

</body>
</html>