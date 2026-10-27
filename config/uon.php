<?php

/*
|--------------------------------------------------------------------------
| Configuração específica do website da UON
|--------------------------------------------------------------------------
| Valores técnicos (limites de upload, paginação, etc.). Os conteúdos
| institucionais (contactos, textos, redes sociais) NÃO estão aqui: são
| geridos no CMS (tabela site_settings).
*/

return [

    'uploads' => [
        // Tamanho máximo por ficheiro, em kilobytes.
        'max_image_kb' => (int) env('UON_UPLOAD_MAX_IMAGE_KB', 8192),
        'max_document_kb' => (int) env('UON_UPLOAD_MAX_DOCUMENT_KB', 20480),

        // Imagens: são sempre re-codificadas no servidor (remove metadados e
        // qualquer conteúdo embebido) e redimensionadas até esta largura.
        'image_max_width' => (int) env('UON_IMAGE_MAX_WIDTH', 1920),
        'image_quality' => 82,

        // Tipos permitidos (extensão => MIME detectado pelo servidor via fileinfo).
        'image_types' => [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'webp' => ['image/webp'],
            'gif' => ['image/gif'],
        ],
        'document_types' => [
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword', 'application/x-ole-storage', 'application/CDFV2'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
            'xls' => ['application/vnd.ms-excel', 'application/x-ole-storage', 'application/CDFV2'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
            'ppt' => ['application/vnd.ms-powerpoint', 'application/x-ole-storage', 'application/CDFV2'],
            'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip'],
            'odt' => ['application/vnd.oasis.opendocument.text', 'application/zip'],
            'ods' => ['application/vnd.oasis.opendocument.spreadsheet', 'application/zip'],
            'txt' => ['text/plain'],
            'csv' => ['text/plain', 'text/csv'],
        ],
    ],

    'pagination' => [
        'public' => 9,
        'admin' => 20,
    ],

    // Credenciais do primeiro superadministrador (usadas apenas pelo seeder
    // quando definidas no .env). Nunca colocar valores reais no repositório.
    'initial_admin' => [
        'name' => env('UON_ADMIN_NAME'),
        'email' => env('UON_ADMIN_EMAIL'),
        'password' => env('UON_ADMIN_PASSWORD'),
    ],
];
