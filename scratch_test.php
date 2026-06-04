<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1bmlxdWVfbmFtZSI6Iml0MDIiLCJyb2xlIjoidXNlciIsImNvbXBhbnlkYiI6IlRFU1RJTkdfMjAyNiIsIm5iZiI6MTc3NDQ5MDczMSwiZXhwIjoxNzc0NDk0MzMxLCJpYXQiOjE3NzQ0OTA3MzF9.XHodOaXqZWTpIawGMWNn0ybu9cMTpGPoGryxh84F0Kw';
$url = 'http://192.168.6.149:9001/api/sap_forecast/list';
$params = ['startDate' => '2026-06-01'];

echo "Test 1: withToken (Bearer token)\n";
try {
    $res1 = Http::withToken($token)->get($url, $params);
    echo "Status: " . $res1->status() . "\n";
    echo "Body: " . $res1->body() . "\n\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "Test 2: withHeaders Authorization => token (No Bearer prefix)\n";
try {
    $res2 = Http::withHeaders(['Authorization' => $token])->get($url, $params);
    echo "Status: " . $res2->status() . "\n";
    echo "Body: " . $res2->body() . "\n\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "Test 3: withHeaders Authorization => Bearer token\n";
try {
    $res3 = Http::withHeaders(['Authorization' => 'Bearer ' . $token])->get($url, $params);
    echo "Status: " . $res3->status() . "\n";
    echo "Body: " . $res3->body() . "\n\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}
