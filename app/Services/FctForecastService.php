<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FctForecastService extends BaseSapService
{
    public function getAll($startDate)
    {
        $routes = ['/api/sap_forecast/list'];

        $rawData = [];

        foreach ($routes as $route) {
            $response = $this->get($route, [
                'startDate' => $startDate,
            ]);

            $data = $this->normalizeResponse($response, 'Forecast');
            $rawData = array_merge($rawData, $data);
        }

        return $this->transformData($rawData);
    }

    private function transformData(array $data)
    {
        // Step 1: Format ForecastDate dan Quantity
        $data = collect($data)
            ->map(function ($item) {
                if (! is_array($item)) {
                    return [];
                }

                $item['ForecastDate'] = Carbon::createFromFormat(
                    'd/m/Y',
                    $item['ForecastDate'],
                )->format('Y-m-d');
                $item['Quantity'] = (string) ((int) $item['Quantity']);

                return $item;
            })
            ->filter();

        return $data->values()->all();
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
        $startDate = request('forecastDate', '2025-08-01'); // Default to June 1, 2025 if not provided
        $forecasts = $this->getAll($startDate);
        // Hapus data lama
        DB::table('sap_forecast')->truncate();

        // Simpan data baru
        foreach ($forecasts as $row) {
            DB::table('sap_forecast')->insert([
                'forecast_code' => $row['Code'],
                'forecast_name' => $row['Name'],
                'item_no' => $row['ItemCode'],
                'forecast_date' => $row['ForecastDate'],
                'quantity' => $row['Quantity'],
            ]);
        }

        return response()->json(['message' => 'Data forecast berhasil disinkronkan']);
    }
}
