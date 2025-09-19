<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FctInventoryMtrService extends BaseSapService
{
    public function getAll($startDate, $itemGroupCodes = "104,167")
    {
        $routes = [
            "/api/sap_fct_inventory_mtr/list",
            "/api/sap_fct_inventory_mtr_semi/list",
            "/api/sap_fct_inventory_mtr_semi_wip/list",
            "/api/sap_fct_inventory_mtr_semi_semi_wip/list",
        ];

        $rawData = [];

        foreach ($routes as $route) {
            $response = $this->get($route, [
                "startDate" => $startDate,
                "itemGroupCodes" => $itemGroupCodes,
            ]);

            $data = $this->normalizeResponse($response, "INVENTORY MTR");
            $rawData = array_merge($rawData, $data);
        }

        return $this->transformData($rawData);
    }

    private function transformData(array $data)
    {
        // Mapping khusus: VendorName => VendorCode (paksa override)
        $forceVendorMap = [
            "GMAX INT INDONESIA PT." => "VML0000293",
            "NIJES BAYU LESTARI PT." => "VML0000598",
            "SURYA MULTINDO INDUSTRI" => "VML0000618",
            "AMER LUBRICANTS INDONESIA" => "VML0000621",
            "KARYA HASIL OPTIMA PT." => "VML0000622",
        ];

        // Step 1: Format QuantityBOM, InStock, ItemGroup
        $data = collect($data)
            ->map(function ($item) {
                if (!is_array($item)) {
                    return [];
                }

                $item["QuantityBOM"] = number_format(
                    floatval($item["QuantityBOM"] ?? 0),
                    5,
                    ".",
                    "",
                );
                $item["InStock"] = number_format(floatval($item["InStock"] ?? 0), 5, ".", "");
                $item["ItemGroup"] = 104;

                return $item;
            })
            ->filter();

        // Step 2: Build vendor map (prioritizing VML)
        $vendorMap = [];

        foreach ($data as $item) {
            $vendorName = trim($item["VendorName"] ?? "");
            $vendorCode = $item["VendorCode"] ?? "";

            if (!isset($vendorMap[$vendorName])) {
                $vendorMap[$vendorName] = $vendorCode;
            }

            // Prioritaskan VML
            if (str_starts_with($vendorCode, "VML")) {
                $vendorMap[$vendorName] = $vendorCode;
            }
        }

        // Step 3: Apply replacements
        $data = $data->map(function ($item) use ($vendorMap, $forceVendorMap) {
            $vendorName = trim($item["VendorName"] ?? "");
            $originalCode = $item["VendorCode"] ?? "";

            // Paksa override jika ada di daftar
            if (isset($forceVendorMap[$vendorName])) {
                $item["VendorCode"] = $forceVendorMap[$vendorName];
            } elseif (str_starts_with($originalCode, "KML") && isset($vendorMap[$vendorName])) {
                $item["VendorCode"] = $vendorMap[$vendorName]; // ganti ke versi VML
            }

            return $item;
        });

        return $data->values()->all();
    }

    private function normalizeResponse($response, $tag = "SAP")
    {
        if (!is_array($response)) {
            Log::warning("[{$tag}] Response bukan array", ["response" => $response]);
            return [];
        }

        if (array_key_exists("data", $response)) {
            return is_array($response["data"]) ? $response["data"] : [];
        }

        return $response;
    }

    public function SyncData()
    {
        $startDate = "2025-03-01";
        $rawData = $this->getAll($startDate);
        $data = array_map(function ($item) {
            return [
                "fg_code" => $item["FGCode"],
                "material_code" => $item["MaterialCode"],
                "material_name" => $item["MaterialName"],
                "material_quantity" => $item["QuantityBOM"],
                "in_stock" => $item["InStock"],
                "item_group" => $item["ItemGroup"],
                "vendor_code" => $item["VendorCode"],
                "vendor_name" => $item["VendorName"],
                "Measure" => $item["UOM"],
            ];
        }, $rawData);

        // Simpan ke database
        DB::table("sap_fct_inventory_mtr")->truncate();
        DB::table("sap_fct_inventory_mtr")->insert($data);
        return response()->json(["message" => "Data FCTInventoryMTR berhasil disinkronkan"]);
    }
}
