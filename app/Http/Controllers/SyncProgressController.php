<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SyncProgressController extends Controller
{
    public function show(string $companyArea)
    {
        $key = "sync_progress_{$companyArea}";
        $payload = Cache::get($key);

        if (!$payload) {
            $payload = [
                "phase" => "idle",
                "processed" => 0,
                "total" => null,
                "percent" => 0,
                "last_range" => null,
                "updated" => now("Asia/Jakarta")->toDateTimeString(),
            ];
        }

        return response()->json($payload);
    }
}
