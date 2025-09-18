<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FctInventoryFgService extends BaseSapService
{
    public function getAll($startDate, $itemGroupCodes = '103,168')
    {
        $routes = [
            '/api/sap_fct_inventory_fg/list',
            '/api/sap_fct_inventory_fg_semi/list',
            '/api/sap_fct_inventory_fg_semi_wip/list',
            '/api/sap_fct_inventory_fg_semi_semi_wip/list',
        ];

        $rawData = [];

        foreach ($routes as $route) {
            $response = $this->get($route, [
                'startDate' => $startDate,
                'itemGroupCodes' => $itemGroupCodes
            ]);

            $data = $this->normalizeResponse($response, 'INVENTORY FG');
             foreach ($data as &$item) {
                $item['ItemCode'] = $item['ItemCode'] ?? $item['Code'] ?? null;
                unset($item['Code']); // hapus 'Code' biar gak dobel
            }

            $rawData = array_merge($rawData, $data);
        }

        return $this->transformData($rawData);
    }

    private function transformData(array $data)
    {
        // Step 1: Format QuantityBOM, InStock, ItemGroup
        $data = collect($data)->map(function ($item) {
            if (!is_array($item)) return [];

            $item['StandardTime'] = number_format(floatval($item['StandardTime'] ?? 0), 5, '.', '');
            $item['U_SAFETYSTOCK'] = number_format(floatval($item['U_SAFETYSTOCK'] ?? 0), 0, '.', '');
            $item['U_DAILYLIMIT']  = number_format(floatval($item['U_DAILYLIMIT'] ?? 0), 0, '.', '');
            $item['OnHand']        = number_format(floatval($item['OnHand'] ?? 0), 0, '.', '');
            $item['OnOrder']       = number_format(floatval($item['OnOrder'] ?? 0), 0, '.', '');

            return $item;
        })->filter();

        return $data->values()->all();
    }


    private function normalizeResponse($response, $tag = 'SAP')
    {
        if (!is_array($response)) {
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
        // dd($rawData);
     
        $data = array_map(function ($item) {
             return [
                'item_code'             => $item['ItemCode'],
                'item_code'             => $item['ItemName'],
                'day_set_pps'           => $item['U_DAYSETPPS'],
                'setup_time'            => $item['U_STPTIME'],
                'cycle_time'            => $item['StandardTime'],
                'cavity'                => $item['U_KVT'],
                'safety_stock'          => $item['U_SAFETYSTOCK'],
                'daily_limit'           => $item['U_DAILYLIMIT'],
                'stock'                 => $item['OnHand'],
                'total_spk'             => $item['OnOrder'],
                'pair'                  => $item['U_PAIR'],
                'man_power'             => $item['ManPower'],      
                'warehouse'             => $item['ToWH'],
                'process_owner'         => $item['ProcessOwner'],
                'owner_code'            => $item['OwnerCode'],
                'special_condition'     => $item['U_Special_Code'],
                'fg_code_1'             => $item['U_FG_CODE_1'],
                'fg_code_2'             => $item['U_FG_CODE_2'],
                'wip_code'              => $item['U_WIP_CODE_1'],
                'material_percentage'   => $item['U_MATERIAL_PERCENT'],
                'continue_production'   => $item['U_CONTINUE_PROD'],
                'family'                => $item['U_FAMILY'],
                'material_group'        => $item['U_MATGROUPING'],
                'packaging'             => $item['U_Carton'],
                'bom_level'             => $item['BOMLevel'],

            ];
        }, $rawData);

        // Simpan ke database
        DB::table('sap_fct_inventory_fgs')->truncate();
        DB::table('sap_fct_inventory_fgs')->upsert(
            $data,
            ['item_code'], // Key for detecting duplicates
            [ // Columns to update
                'item_name', 'day_set_pps', 'setup_time', 'cycle_time', 'cavity', 'safety_stock',
                'daily_limit', 'stock', 'total_spk', 'pair', 'man_power', 'warehouse',
                'process_owner', 'owner_code', 'special_condition', 'fg_code_1', 'fg_code_2',
                'wip_code', 'material_percentage', 'continue_production', 'family',
                'material_group', 'packaging', 'bom_level'
            ]
        );
        return response()->json(['message' => 'Data FCTInventoryFG berhasil disinkronkan']);
    }
}