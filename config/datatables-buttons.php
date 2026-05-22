<?php

return [
    /*
     * Namespaces used by the generator.
     */
    'namespace' => [
        /*
         * Base namespace/directory to create the new file.
         * This is appended on default Laravel namespace.
         * Usage: php artisan datatables:make User
         * Output: App\DataTables\UserDataTable
         * With Model: App\User (default model)
         * Export filename: users_timestamp
         */
        'base' => 'DataTables',

        /*
         * Base namespace/directory where your model's are located.
         * This is appended on default Laravel namespace.
         * Usage: php artisan datatables:make Post --model
         * Output: App\DataTables\PostDataTable
         * With Model: App\Post
         * Export filename: posts_timestamp
         */
        'model' => '',
    ],

    /*
     * Set Custom stub folder
     */
    // 'stub' => '/resources/custom_stub',

    /*
     * PDF generator to be used when converting the table to pdf.
     * Available generators: excel, dompdf
     * Dompdf package: barryvdh/laravel-dompdf (pure PHP, Docker-friendly)
     * Excel package: maatwebsite/excel
     *
     * Deprecated & removed: snappy + wkhtmltopdf binaries (legacy, not Docker compatible).
     */
    'pdf_generator' => 'dompdf',

    /*
     * Dompdf PDF options (replaces the old snappy/wkhtmltopdf configuration).
     */
    'dompdf' => [
        'options' => [
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ],
        'orientation' => 'landscape',
    ],

    /*
     * Default html builder parameters.
     */
    'parameters' => [
        'dom' => 'Bfrtip',
        'order' => [[0, 'desc']],
        'buttons' => ['excel', 'csv', 'pdf', 'print', 'reset', 'reload'],
    ],

    /*
     * Generator command default options value.
     */
    'generator' => [
        /*
         * Default columns to generate when not set.
         */
        'columns' => 'id,add your columns,created_at,updated_at',

        /*
         * Default buttons to generate when not set.
         */
        'buttons' => 'excel,csv,pdf,print,reset,reload',

        /*
         * Default DOM to generate when not set.
         */
        'dom' => 'Bfrtip',
    ],
];
