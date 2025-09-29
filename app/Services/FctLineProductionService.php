<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FctLineProductionService extends BaseSapService
{
    public function getAll($startDate)
    {
        $routes = [
            '/api/sap_fct_lineproduction/list',
            '/api/sap_fct_lineproduction_semi/list',
            '/api/sap_fct_lineproduction_semi_wip/list',
            '/api/sap_fct_lineproduction_semi_semi_wip/list',
        ];

        $rawData = [];

        foreach ($routes as $route) {
            $response = $this->get($route, [
                'startDate' => $startDate,
            ]);

            $data = $this->normalizeResponse($response, 'Line Production');
            foreach ($data as &$item) {
                $item['item_code'] = $item['item_code'] ?? ($item['Code'] ?? null);
                unset($item['Code']); // hapus 'Code' biar gak dobel
                $item['line_production'] =
                    $item['line_production'] ?? ($item['LineProduction'] ?? null);
                unset($item['LineProduction']); // hapus 'Code' biar gak dobel
                $item['priority'] = $item['priority'] ?? ($item['Priority'] ?? null);
                unset($item['Priority']); // hapus 'Code' biar gak dobel
            }
            $rawData = array_merge($rawData, $data);
        }

        return $rawData;
    }

    private function normalizeResponse($response, $tag = 'SAP')
    {
        if (! is_array($response)) {
            Log::warning("[{$tag}] Response bukan array", ['response' => $response]);

            return [];
        }

        if (array_key_exists('data', $response)) {
            return is_array($response['data']) ? $response['data'] : [];
        }

        return $response;
    }

    public function SyncData()
    {
        $startDate = '2025-06-01';
        $rawData = $this->getAll($startDate);

        $data = array_map(function ($item) {
            return [
                'item_code' => $item['item_code'],
                'line_production' => $item['line_production'],
                'priority' => $item['priority'],
            ];
        }, $rawData);

        // Simpan ke database
        DB::table('sap_fct_lineproductions')->truncate();
        DB::table('sap_fct_lineproductions')->insert($data);

        return response()->json(['message' => 'Data FCTLineproduction berhasil disinkronkan']);
    }
}
