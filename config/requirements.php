<?php

return [
    'mime_presets' => [
        'pdf' => [
            'label' => 'PDF',
            'apps' => 'Adobe Acrobat, Chrome',
            'mimes' => ['application/pdf'],
        ],
        'images' => [
            'label' => 'Images',
            'apps' => 'Photos, Preview',
            'mimes' => ['image/png', 'image/jpeg', 'image/gif', 'image/webp'],
        ],
        'word' => [
            'label' => 'Word Docs',
            'apps' => 'Microsoft Word, Google Docs',
            'mimes' => ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        ],
        'excel' => [
            'label' => 'Spreadsheets',
            'apps' => 'Microsoft Excel, Google Sheets',
            'mimes' => ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'],
        ],
        'ppt' => [
            'label' => 'Presentations',
            'apps' => 'PowerPoint, Google Slides',
            'mimes' => ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        ],
        'text' => [
            'label' => 'Text',
            'apps' => 'Notepad, VS Code',
            'mimes' => ['text/plain'],
        ],
        'zip' => [
            'label' => 'Archives',
            'apps' => 'WinZip, 7-Zip',
            'mimes' => ['application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed'],
        ],
        'visio' => [
            'label' => 'Visio',
            'apps' => 'Microsoft Visio',
            'mimes' => ['application/vnd.visio'], // often .vsd/.vsdx served as this or app/octet
        ],
        'cad' => [
            'label' => 'CAD',
            'apps' => 'AutoCAD, DraftSight',
            'mimes' => ['image/vnd.dwg', 'image/vnd.dxf', 'application/acad'], // varies across servers
        ],
    ],
];
