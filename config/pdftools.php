<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PDF Processing Tools Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración de herramientas para procesamiento de PDF requeridas
    | para la validación y conversión de documentos VUCEM
    |
    */

    'ghostscript' => env('GHOSTSCRIPT_PATH', 'gs'),
    'pdfimages' => env('PDFIMAGES_PATH', 'pdfimages'),
    
    /*
    |--------------------------------------------------------------------------
    | VUCEM Requirements
    |--------------------------------------------------------------------------
    |
    | Especificaciones técnicas requeridas por VUCEM
    |
    */
    'vucem' => [
        'pdf_version' => '1.4',
        'dpi' => 300,
        'max_size_mb' => 3,
        'color_mode' => 'grayscale',
        'encryption' => false,
    ],
];