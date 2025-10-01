<?php

return [
    'base_url' => env('SAP_BASE_URL', 'http://192.168.6.149:9001'),
    'auth_path' => env('SAP_AUTH_PATH', '/auth/token'),
    'company_db' => env('SAP_COMPANY_DB', 'LIVE_DATABASE'),
    'username' => env('SAP_USERNAME', 'it02'),
    'password' => env('SAP_PASSWORD', '123it'),
];
