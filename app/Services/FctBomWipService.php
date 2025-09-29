<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FctBomWipService extends BaseSapService
{
    public function getBomWip($startDate, $itemGroupCode = '103,168')
    {
        $response = $this->get('/api/sap_fct_bom_wip/list', [
            'startDate' => $startDate,
            'itemGroupCodes' => $itemGroupCode,
        ]);

        $data = $this->normalizeResponse($response, 'BOM WIP');

        $result = [];
        foreach ($data as $item) {
            $item['ItmsGrpCod'] = 103;
            $result[] = $item;
        }

        return $result;
    }

    public function getSemi($startDate, $itemGroupCode = '103,168')
    {
        $response = $this->get('/api/sap_fct_bom_wip_semi/list', [
            'startDate' => $startDate,
            'itemGroupCodes' => $itemGroupCode,
        ]);

        $data = $this->normalizeResponse($response, 'BOM WIP SEMI');

        $result = [];
        foreach ($data as $item) {
            $result[] = [
                'Father' => $item['FGCode'] ?? '',
                'WIP1' => $item['SemiCode'] ?? '',
                'WIP2' => $item['WIPCode'] ?? '',
                'WIP3' => $item['WIP3'] ?? '',
                'Level' => $item['Level'] ?? '',
                'Quantity' => $item['Quantity'] ?? 0,
                'ItmsGrpCod' => 103,
            ];
        }

        return $result;
    }

    public function getSemiSemi($startDate, $itemGroupCode = '103,168')
    {
        $response = $this->get('/api/sap_fct_bom_wip_semi_semi/list', [
            'startDate' => $startDate,
            'itemGroupCodes' => $itemGroupCode,
        ]);

        $data = $this->normalizeResponse($response, 'BOM WIP SEMI-SEMI');

        $result = [];
        foreach ($data as $item) {
            $result[] = [
                'Father' => $item['FGCode'] ?? '',
                'WIP1' => $item['SemiCode'] ?? '',
                'WIP2' => $item['WIPCode'] ?? '',
                'WIP3' => $item['WIP3'] ?? '',
                'Level' => $item['Level'] ?? '',
                'Quantity' => $item['Quantity'] ?? 0,
                'ItmsGrpCod' => 103,
            ];
        }

        return $result;
    }

    public function getAllCombined($startDate, $itemGroupCode = '103,168')
    {
        $bom = $this->getBomWip($startDate, $itemGroupCode);
        $semi = $this->getSemi($startDate, $itemGroupCode);
        $semiSemi = $this->getSemiSemi($startDate, $itemGroupCode);

        return array_merge($bom, $semi, $semiSemi);
    }

    public function getDistinctItemCodes($startDate, $itemGroupCode = '103,168')
    {
        // return collect($this->getAllCombined($startDate, $itemGroupCode))
        //     ->pluck('Father')
        //     ->filter() // hilangkan null
        //     ->unique()
        //     ->values()
        //     ->all();

        $combined = $this->getAllCombined($startDate, $itemGroupCode);

        $fatherCodes = [];
        foreach ($combined as $item) {
            if (! empty($item['Father']) && ! in_array($item['Father'], $fatherCodes)) {
                $fatherCodes[] = $item['Father'];
            }
        }

        return array_values($fatherCodes);
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
        $startDate = '2025-03-01';

        $first = $this->getBomWip($startDate); // udah array
        $second = $this->getSemi($startDate); // udah array
        $third = $this->getSemiSemi($startDate); // udah array
        $bomWip = $this->getAllCombined($startDate); // udah array
        $fgCode = $this->getDistinctItemCodes($startDate); // udah array

        // dd($bomWip);

        DB::table('sap_fct_bom_wip')->truncate();
        DB::table('sap_fct_bom_wip_fgcode')->truncate();
        DB::table('sap_fct_bom_wip_first')->truncate();
        DB::table('sap_fct_bom_wip_second')->truncate();
        DB::table('sap_fct_bom_wip_third')->truncate();

        foreach ($bomWip as $row) {
            DB::table('sap_fct_bom_wip')->insert([
                'fg_code' => $row['Father'],
                'semi_first' => $row['WIP1'],
                'semi_second' => $row['WIP2'],
                'semi_third' => $row['WIP3'],
                'level' => $row['Level'],
                'bom_quantity' => $row['Quantity'],
                'item_group' => $row['ItmsGrpCod'],
            ]);
        }

        foreach ($fgCode as $code) {
            DB::table('sap_fct_bom_wip_fgcode')->insert([
                'FinishG_Code' => $code,
            ]);
        }

        foreach ($first as $row) {
            DB::table('sap_fct_bom_wip_first')->insert([
                'fg_code' => $row['Father'],
                'semi_first' => $row['WIP1'],
                'semi_second' => $row['WIP2'],
                'semi_third' => $row['WIP3'],
                'level' => $row['Level'],
                'bom_quantity' => $row['Quantity'],
                'item_group' => $row['ItmsGrpCod'],
            ]);
        }

        foreach ($second as $row) {
            DB::table('sap_fct_bom_wip_second')->insert([
                'fg_code' => $row['Father'],
                'semi_first' => $row['WIP1'],
                'semi_second' => $row['WIP2'],
                'semi_third' => $row['WIP3'],
                'level' => $row['Level'],
                'bom_quantity' => $row['Quantity'],
                'item_group' => $row['ItmsGrpCod'],
            ]);
        }

        foreach ($third as $row) {
            DB::table('sap_fct_bom_wip_third')->insert([
                'fg_code' => $row['Father'],
                'semi_first' => $row['WIP1'],
                'semi_second' => $row['WIP2'],
                'semi_third' => $row['WIP3'],
                'level' => $row['Level'],
                'bom_quantity' => $row['Quantity'],
                'item_group' => $row['ItmsGrpCod'],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data BOM WIP berhasil disinkronkan',
            'data' => [
                'bom_wip' => $first,
                'semi' => $second,
                'semi_semi' => $third,
                'combined' => $bomWip,
                'distinct_father_item_codes' => $fgCode,
            ],
        ]);
    }
}
