<?php

declare(strict_types=1);

namespace App\Services\Sap;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Throwable;

final class SapSyncService
{
    private string $baseUrl;
    private string $authUrl;
    private string $companyDb;
    private string $username;
    private string $password;
    private ?string $token = null;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.sap.base_url'), '/');
        $this->authUrl = config('services.sap.auth_url');
        $this->companyDb = config('services.sap.company_db');
        $this->username = config('services.sap.username');
        $this->password = config('services.sap.password');
    }

    /**
     * Get the dynamic startDate (always 1st day of the current month).
     */
    public function getStartDate(): string
    {
        return Carbon::now()->startOfMonth()->format('Y-m-d');
    }

    /**
     * Get the definitions of the 16 endpoints.
     */
    public function getEndpoints(): array
    {
        return [
            // BOM WIP Group - column_map: API field name => DB column name
            'sap_fct_bom_wip' => [
                'path' => 'sap_fct_bom_wip/list',
                'tables' => ['sap_fct_bom_wip_first'],
                'params' => ['itemGroupCodes' => '103,168'],
                'convert_168_to_103' => true,
                'column_map' => [
                    'Father'      => 'fg_code',
                    'WIP1'   => 'semi_first',
                    'WIP2'  => 'semi_second',
                    'WIP3'   => 'semi_third',
                    'Level'       => 'level',
                    'Quantity' => 'bom_quantity',
                    'ItmsGrpCod'   => 'item_group',
                ],
            ],
            'sap_fct_bom_wip_semi' => [
                'path' => 'sap_fct_bom_wip_semi/list',
                'tables' => ['sap_fct_bom_wip_second'],
                'params' => ['itemGroupCodes' => '103,168'],
                'convert_168_to_103' => true,
                'column_map' => [
                    'FGCode'      => 'fg_code',
                    'SemiCode'   => 'semi_first',
                    'WIPCode'  => 'semi_second',
                    'WIP3'   => 'semi_third',
                    'Level'       => 'level',
                    'Quantity' => 'bom_quantity',
                    'ItmsGrpCod'   => 'item_group',
                ],
            ],
            'sap_fct_bom_wip_semi_semi' => [
                'path' => 'sap_fct_bom_wip_semi_semi/list',
                'tables' => ['sap_fct_bom_wip_third'],
                'params' => ['itemGroupCodes' => '103,168'],
                'convert_168_to_103' => true,
                'column_map' => [
                    'FGCode'      => 'fg_code',
                    'SemiCode'   => 'semi_first',
                    'SemiSemiCode'  => 'semi_second',
                    'WIPCode'   => 'semi_third',
                    'Level'       => 'level',
                    'Quantity' => 'bom_quantity',
                    'ItmsGrpCod'   => 'item_group',
                ],
            ],

            // Inventory MTR Group
            'sap_fct_inventory_mtr' => [
                'path' => 'sap_fct_inventory_mtr/list',
                'tables' => ['sap_fct_inventory_mtr'],
                'params' => ['itemGroupCodes' => '104,167'],
                'column_map' => [
                    'FGCode'            => 'fg_code',
                    'MaterialCode'      => 'material_code',
                    'MaterialName'      => 'material_name',
                    'QuantityBOM'       => 'material_quantity',
                    'InStock'           => 'in_stock',
                    'ItemGroup'         => 'item_group',
                    'VendorCode'        => 'vendor_code',
                    'VendorName'        => 'vendor_name',
                    'UOM'               => 'Measure',
                ],
            ],

            // Inventory FG Group
            'sap_fct_inventory_fg' => [
                'path' => 'sap_fct_inventory_fg/list',
                'tables' => ['sap_fct_inventory_fgs'],
                'params' => ['itemGroupCodes' => '103,168'],
            ],

            // Line Production Group
            'sap_fct_lineproduction' => [
                'path' => 'sap_fct_lineproduction/list',
                'tables' => ['sap_fct_lineproductions'],
                'params' => [],
            ],

            // Forecast Group
            'sap_forecast' => [
                'path' => 'sap_forecast/list',
                'tables' => ['sap_forecast'],
                'params' => [],
                'column_map' => [
                    'Code'         => 'forecast_code',
                    'Name'         => 'forecast_name',
                    'ItemCode'     => 'item_no',
                    'ForecastDate' => 'forecast_date',
                    'Quantity'     => 'quantity',
                ],
            ],
        ];
    }

    /**
     * Authenticate with the SAP authentication endpoint to retrieve a fresh token.
     */
    public function login(): bool
    {
        Log::info("SAP Sync: Authenticating with auth endpoint...", [
            'url' => $this->authUrl,
            'db' => $this->companyDb,
            'username' => $this->username,
        ]);

        try {
            $response = Http::timeout(30)
                ->asJson()
                ->post($this->authUrl, [
                    'CompanyDB' => $this->companyDb,
                    'Username' => $this->username,
                    'Password' => $this->password,
                ]);

            if (!$response->successful()) {
                Log::error("SAP Sync Auth Error: Authentication request failed with status: " . $response->status(), [
                    'body' => $response->body(),
                ]);
                return false;
            }

            $data = $response->json();
            
            if (is_string($data)) {
                $this->token = $data;
            } elseif (isset($data['token'])) {
                $this->token = (string) $data['token'];
            } elseif (isset($data['access_token'])) {
                $this->token = (string) $data['access_token'];
            } else {
                $rawBody = trim($response->body());
                $rawBody = trim($rawBody, '"');
                if (str_contains($rawBody, '.')) {
                    $this->token = $rawBody;
                } else {
                    Log::error("SAP Sync Auth Error: Token not found in response.", ['response' => $data]);
                    return false;
                }
            }

            Log::info("SAP Sync Auth: Successfully authenticated. Token prefix: " . substr($this->token, 0, 15));
            return true;

        } catch (Throwable $e) {
            Log::error("SAP Sync Auth Exception: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    /**
     * Sync a single endpoint by its key.
     */
    public function syncEndpoint(string $key, string $startDate): array
    {
        if ($key === 'sap_fct_inventory_mtr') {
            return $this->syncInventoryMtrUnion($startDate);
        }

        if ($key === 'sap_fct_inventory_fg') {
            return $this->syncInventoryFgUnion($startDate);
        }

        if ($key === 'sap_fct_lineproduction') {
            return $this->syncLineProductionUnion($startDate);
        }

        if ($this->token === null) {
            if (!$this->login()) {
                return [
                    'success' => false,
                    'message' => "Authentication failed. Cannot run sync.",
                ];
            }
        }

        $endpoints = $this->getEndpoints();

        if (!isset($endpoints[$key])) {
            return [
                'success' => false,
                'message' => "Endpoint key '{$key}' is not defined.",
            ];
        }

        $config = $endpoints[$key];
        $url = $this->baseUrl . '/' . $config['path'];

        $queryParams = array_merge([
            'startDate' => $startDate,
        ], $config['params']);

        Log::info("SAP Sync: Fetching endpoint '{$key}'", [
            'url' => $url,
            'params' => $queryParams,
        ]);

        try {
            $response = Http::withToken($this->token ?? '')
                ->timeout(60)
                ->retry(3, 1000)
                ->get($url, $queryParams);

            if (!$response->successful()) {
                $errorMsg = "API request failed with status: {$response->status()}";
                Log::error("SAP Sync Error: {$errorMsg}", ['body' => $response->body()]);
                return ['success' => false, 'message' => $errorMsg];
            }

            $body = $response->json();
            $rows = isset($body['data']) ? $body['data'] : $body;

            if (!is_array($rows)) {
                $rows = [];
            }

            Log::info("SAP Sync: Endpoint '{$key}' fetched successfully. Row count: " . count($rows));

            // Populate the target tables
            $columnMap = $config['column_map'] ?? [];
            $convert168To103 = $config['convert_168_to_103'] ?? false;

            foreach ($config['tables'] as $table) {
                $this->populateTable(
                    $table,
                    $rows,
                    $columnMap,
                    $convert168To103
                );
            }

            return [
                'success' => true,
                'message' => "Synced '{$key}' successfully. Loaded " . count($rows) . " rows.",
            ];

        } catch (Throwable $e) {
            Log::error("SAP Sync Exception on '{$key}': " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'success' => false,
                'message' => "Exception: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Perform the BOM WIP post-processing steps:
     * - Union sap_fct_bom_wip_first, second, third into sap_fct_bom_wip
     * - Extract distinct fg_code from sap_fct_bom_wip and save as FinishG_Code in sap_fct_bom_wip_fgcode
     */
    public function processBomWipUnion(): array
    {
        try {
            Log::info("SAP Sync: Processing BOM WIP union and distinct FG Code mapping.");

            if (!Schema::hasTable('sap_fct_bom_wip')) {
                return ['success' => false, 'message' => "Table 'sap_fct_bom_wip' does not exist."];
            }

            // Clear sap_fct_bom_wip
            DB::table('sap_fct_bom_wip')->truncate();

            // Query existing first, second, third tables and insert combined data
            $sources = ['sap_fct_bom_wip_first', 'sap_fct_bom_wip_second', 'sap_fct_bom_wip_third'];
            $activeSources = array_filter($sources, fn($table) => Schema::hasTable($table));

            if (empty($activeSources)) {
                return ['success' => false, 'message' => "None of the BOM WIP level tables exist."];
            }

            // Construct UNION ALL query
            $unionSql = array_map(function($table) {
                return "SELECT fg_code, semi_first, semi_second, semi_third, level, bom_quantity, item_group FROM {$table}";
            }, $activeSources);

            $sql = "INSERT INTO sap_fct_bom_wip (fg_code, semi_first, semi_second, semi_third, level, bom_quantity, item_group) " . implode(" UNION ALL ", $unionSql);
            DB::statement($sql);

            $insertedCount = DB::table('sap_fct_bom_wip')->count();
            Log::info("SAP Sync: Union successfully populated 'sap_fct_bom_wip' with {$insertedCount} records.");

            // Clear and populate sap_fct_bom_wip_fgcode
            if (Schema::hasTable('sap_fct_bom_wip_fgcode')) {
                DB::table('sap_fct_bom_wip_fgcode')->truncate();

                DB::statement("
                    INSERT INTO sap_fct_bom_wip_fgcode (FinishG_Code)
                    SELECT DISTINCT fg_code FROM sap_fct_bom_wip WHERE fg_code IS NOT NULL AND fg_code != ''
                ");

                $fgCount = DB::table('sap_fct_bom_wip_fgcode')->count();
                Log::info("SAP Sync: Distinct FG codes populated in 'sap_fct_bom_wip_fgcode'. Count: {$fgCount}");
            } else {
                Log::warning("Table 'sap_fct_bom_wip_fgcode' does not exist. Skipping distinct FG code step.");
            }

            return [
                'success' => true,
                'message' => "Processed BOM WIP union and distinct FG codes successfully.",
            ];

        } catch (Throwable $e) {
            Log::error("SAP Sync Exception in BOM WIP post-processing: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Post-processing exception: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Populate a database table safely, applying optional column remapping then
     * filtering to only columns that exist in the DB schema.
     *
     * @param array<string,string> $columnMap  API field name => DB column name
     */
    private function populateTable(
        string $tableName,
        array $rows,
        array $columnMap = [],
        bool $convert168To103 = false
    ): void
    {
        if (!Schema::hasTable($tableName)) {
            Log::warning("SAP Sync: Target table '{$tableName}' does not exist in database. Skipping.");
            return;
        }

        DB::table($tableName)->truncate();

        if (empty($rows)) {
            Log::info("SAP Sync: No data to insert for table '{$tableName}'.");
            return;
        }

        $columns    = Schema::getColumnListing($tableName);
        $columnFlip = array_flip($columns);

        $insertData = [];
        foreach ($rows as $row) {
            $rowArray = (array) $row;

            // Apply explicit column map: rename API keys to DB column names
            if (!empty($columnMap)) {
                $remapped = [];
                foreach ($rowArray as $apiKey => $value) {
                    $dbKey           = $columnMap[$apiKey] ?? $apiKey;
                    $remapped[$dbKey] = $value;
                }
                $rowArray = $remapped;
            }
            // Khusus endpoint BOM:
            // item_group 168 dianggap sama dengan 103
            if (
                $convert168To103 &&
                isset($rowArray['item_group']) &&
                (int) $rowArray['item_group'] === 168
            ) {
                $rowArray['item_group'] = 103;
            }

            // Khusus table sap_forecast
            if ($tableName === 'sap_forecast') {
                if (isset($rowArray['forecast_date']) && !empty($rowArray['forecast_date'])) {
                    try {
                        $rowArray['forecast_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $rowArray['forecast_date'])->format('Y-m-d');
                    } catch (\Throwable $e) {
                        try {
                            $rowArray['forecast_date'] = \Carbon\Carbon::parse($rowArray['forecast_date'])->format('Y-m-d');
                        } catch (\Throwable $ex) {
                            $rowArray['forecast_date'] = null;
                        }
                    }
                }
                if (isset($rowArray['quantity'])) {
                    $rowArray['quantity'] = is_numeric($rowArray['quantity']) ? (int) $rowArray['quantity'] : 0;
                }
            }

            $filtered     = array_intersect_key($rowArray, $columnFlip);
            $insertData[] = $filtered;
        }

        foreach (array_chunk($insertData, 500) as $chunk) {
            DB::table($tableName)->insert($chunk);
        }

        Log::info("SAP Sync: Loaded " . count($insertData) . " records into table '{$tableName}'.");
    }

    /**
     * Sync the sap_fct_inventory_mtr endpoint by unioning data from 4 API endpoints.
     */
    private function syncInventoryMtrUnion(string $startDate): array
    {
        if ($this->token === null) {
            if (!$this->login()) {
                return [
                    'success' => false,
                    'message' => "Authentication failed. Cannot run sync.",
                ];
            }
        }

        $endpoints = [
            'sap_fct_inventory_mtr' => 'sap_fct_inventory_mtr/list',
            'sap_fct_inventory_mtr_semi' => 'sap_fct_inventory_mtr_semi/list',
            'sap_fct_inventory_mtr_semi_wip' => 'sap_fct_inventory_mtr_semi_wip/list',
            'sap_fct_inventory_mtr_semi_semi_wip' => 'sap_fct_inventory_mtr_semi_semi_wip/list',
        ];

        $allRows = [];
        $columnMap = [
            'FGCode'            => 'fg_code',
            'MaterialCode'      => 'material_code',
            'MaterialName'      => 'material_name',
            'QuantityBOM'       => 'material_quantity',
            'InStock'           => 'in_stock',
            'ItemGroup'         => 'item_group',
            'VendorCode'        => 'vendor_code',
            'VendorName'        => 'vendor_name',
            'UOM'               => 'Measure',
        ];

        foreach ($endpoints as $name => $path) {
            $url = $this->baseUrl . '/' . $path;
            $queryParams = [
                'startDate' => $startDate,
                'itemGroupCodes' => '104,167',
            ];

            Log::info("SAP Sync (Inventory MTR Union): Fetching '{$name}'", [
                'url' => $url,
                'params' => $queryParams,
            ]);

            try {
                $response = Http::withToken($this->token ?? '')
                    ->timeout(60)
                    ->retry(3, 1000)
                    ->get($url, $queryParams);

                if (!$response->successful()) {
                    $errorMsg = "API request for '{$name}' failed with status: {$response->status()}";
                    Log::error("SAP Sync Error: {$errorMsg}", ['body' => $response->body()]);
                    return ['success' => false, 'message' => $errorMsg];
                }

                $body = $response->json();
                $rows = isset($body['data']) ? $body['data'] : $body;

                if (is_array($rows)) {
                    $allRows = array_merge($allRows, $rows);
                    Log::info("SAP Sync (Inventory MTR Union): '{$name}' fetched. Rows: " . count($rows));
                }
            } catch (Throwable $e) {
                Log::error("SAP Sync Exception on '{$name}': " . $e->getMessage());
                return [
                    'success' => false,
                    'message' => "Exception on '{$name}': " . $e->getMessage(),
                ];
            }
        }

        // Fetch vendor mappings from purchasing_contacts
        try {
            $contacts = DB::table('purchasing_contacts')
                ->whereNotNull('vendor_name')
                ->where('vendor_name', '!=', '')
                ->whereNotNull('vendor_code')
                ->where('vendor_code', '!=', '')
                ->select('vendor_name', 'vendor_code')
                ->get();
            
            $vendorMap = [];
            foreach ($contacts as $contact) {
                $vendorMap[strtolower(trim($contact->vendor_name))] = $contact->vendor_code;
            }
        } catch (Throwable $e) {
            Log::warning("SAP Sync (Inventory MTR Union): Failed to fetch purchasing_contacts mapping. " . $e->getMessage());
            $vendorMap = [];
        }

        $tableName = 'sap_fct_inventory_mtr';
        if (!Schema::hasTable($tableName)) {
            return [
                'success' => false,
                'message' => "Table '{$tableName}' does not exist.",
            ];
        }

        $columns = Schema::getColumnListing($tableName);
        $columnFlip = array_flip($columns);

        $insertData = [];
        foreach ($allRows as $row) {
            $rowArray = (array) $row;

            // Apply explicit column map: rename API keys to DB column names
            $remapped = [];
            foreach ($rowArray as $apiKey => $value) {
                $dbKey = $columnMap[$apiKey] ?? $apiKey;
                $remapped[$dbKey] = $value;
            }
            $rowArray = $remapped;

            // Round material_quantity and in_stock to 5 decimal places
            if (isset($rowArray['material_quantity'])) {
                $rowArray['material_quantity'] = is_numeric($rowArray['material_quantity'])
                    ? round((float) $rowArray['material_quantity'], 5)
                    : null;
            }
            if (isset($rowArray['in_stock'])) {
                $rowArray['in_stock'] = is_numeric($rowArray['in_stock'])
                    ? round((float) $rowArray['in_stock'], 5)
                    : null;
            }

            // Force item_group to 104
            $rowArray['item_group'] = 104;

            // Replace vendor_code based on matching vendor_name with purchasing_contacts
            if (isset($rowArray['vendor_name']) && is_string($rowArray['vendor_name'])) {
                $vendorNameLower = strtolower(trim($rowArray['vendor_name']));
                if (isset($vendorMap[$vendorNameLower])) {
                    $rowArray['vendor_code'] = $vendorMap[$vendorNameLower];
                }
            }

            $filtered = array_intersect_key($rowArray, $columnFlip);
            $insertData[] = $filtered;
        }

        try {
            DB::table($tableName)->truncate();

            foreach (array_chunk($insertData, 500) as $chunk) {
                DB::table($tableName)->insert($chunk);
            }

            return [
                'success' => true,
                'message' => "Synced '{$tableName}' successfully with union. Loaded " . count($insertData) . " rows.",
            ];
        } catch (Throwable $e) {
            Log::error("SAP Sync Exception during DB populate: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "DB Insert Exception: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Sync the sap_fct_inventory_fg endpoint by unioning data from 4 API endpoints.
     */
    private function syncInventoryFgUnion(string $startDate): array
    {
        if ($this->token === null) {
            if (!$this->login()) {
                return [
                    'success' => false,
                    'message' => "Authentication failed. Cannot run sync.",
                ];
            }
        }

        $endpoints = [
            'sap_fct_inventory_fg' => [
                'path' => 'sap_fct_inventory_fg/list',
                'is_first' => true,
            ],
            'sap_fct_inventory_fg_semi' => [
                'path' => 'sap_fct_inventory_fg_semi/list',
                'is_first' => false,
            ],
            'sap_fct_inventory_fg_semi_wip' => [
                'path' => 'sap_fct_inventory_fg_semi_wip/list',
                'is_first' => false,
            ],
            'sap_fct_inventory_fg_semi_semi_wip' => [
                'path' => 'sap_fct_inventory_fg_semi_semi_wip/list',
                'is_first' => false,
            ],
        ];

        $columnMapFirst = [
            'ItemCode'            => 'item_code',
            'ItemName'            => 'item_name',
            'U_DAYSETPPS'         => 'day_set_pps',
            'U_STPTIME'           => 'setup_time',
            'StandardTime'        => 'cycle_time',
            'U_KVT'               => 'cavity',
            'U_SAFETYSTOCK'       => 'safety_stock',
            'U_DAILYLIMIT'        => 'daily_limit',
            'OnHand'              => 'stock',
            'OnOrder'             => 'total_spk',
            'U_PAIR'              => 'pair',
            'ManPower'            => 'man_power',
            'ToWH'                => 'warehouse',
            'ProcessOwner'        => 'process_owner',
            'OwnerCode'           => 'owner_code',
            'U_Special_Code'      => 'special_condition',
            'U_FG_CODE_1'         => 'fg_code_1',
            'U_FG_CODE_2'         => 'fg_code_2',
            'U_WIP_CODE_1'        => 'wip_code',
            'U_MATERIAL_PERCENT'  => 'material_percentage',
            'U_CONTINUE_PROD'     => 'continue_production',
            'U_FAMILY'            => 'family',
            'U_MATGROUPING'       => 'material_group',
            'U_Carton'            => 'packaging',
            'BOMLevel'            => 'bom_level',
        ];

        $columnMapOthers = array_merge($columnMapFirst, [
            'Code' => 'item_code',
        ]);
        unset($columnMapOthers['ItemCode']);

        $allRows = [];

        foreach ($endpoints as $name => $meta) {
            $url = $this->baseUrl . '/' . $meta['path'];
            $queryParams = [
                'startDate' => $startDate,
                'itemGroupCodes' => '103,168',
            ];

            Log::info("SAP Sync (Inventory FG Union): Fetching '{$name}'", [
                'url' => $url,
                'params' => $queryParams,
            ]);

            try {
                $response = Http::withToken($this->token ?? '')
                    ->timeout(60)
                    ->retry(3, 1000)
                    ->get($url, $queryParams);

                if (!$response->successful()) {
                    $errorMsg = "API request for '{$name}' failed with status: {$response->status()}";
                    Log::error("SAP Sync Error: {$errorMsg}", ['body' => $response->body()]);
                    return ['success' => false, 'message' => $errorMsg];
                }

                $body = $response->json();
                $rows = isset($body['data']) ? $body['data'] : $body;

                if (is_array($rows)) {
                    $map = $meta['is_first'] ? $columnMapFirst : $columnMapOthers;
                    foreach ($rows as $row) {
                        $allRows[] = [
                            'row' => $row,
                            'map' => $map,
                        ];
                    }
                    Log::info("SAP Sync (Inventory FG Union): '{$name}' fetched. Rows: " . count($rows));
                }
            } catch (Throwable $e) {
                Log::error("SAP Sync Exception on '{$name}': " . $e->getMessage());
                return [
                    'success' => false,
                    'message' => "Exception on '{$name}': " . $e->getMessage(),
                ];
            }
        }

        $tableName = 'sap_fct_inventory_fgs';
        if (!Schema::hasTable($tableName)) {
            return [
                'success' => false,
                'message' => "Table '{$tableName}' does not exist.",
            ];
        }

        $columns = Schema::getColumnListing($tableName);
        $columnFlip = array_flip($columns);

        $insertData = [];
        foreach ($allRows as $item) {
            $rowArray = (array) $item['row'];
            $map = $item['map'];

            // Apply explicit column map: rename API keys to DB column names
            $remapped = [];
            foreach ($rowArray as $apiKey => $value) {
                $dbKey = $map[$apiKey] ?? $apiKey;
                $remapped[$dbKey] = $value;
            }
            $rowArray = $remapped;

            // Round cycle_time (decimal 5)
            if (isset($rowArray['cycle_time'])) {
                $rowArray['cycle_time'] = is_numeric($rowArray['cycle_time'])
                    ? round((float) $rowArray['cycle_time'], 5)
                    : null;
            }

            // Convert safety_stock, daily_limit, stock, total_spk to int (no decimal)
            $intFields = ['safety_stock', 'daily_limit', 'stock', 'total_spk'];
            foreach ($intFields as $field) {
                if (isset($rowArray[$field])) {
                    $rowArray[$field] = is_numeric($rowArray[$field])
                        ? (int) $rowArray[$field]
                        : null;
                }
            }

            $filtered = array_intersect_key($rowArray, $columnFlip);

            // Skip rows that don't have a valid item_code
            if (!isset($filtered['item_code']) || $filtered['item_code'] === '' || $filtered['item_code'] === null) {
                continue;
            }

            $insertData[] = $filtered;
        }

        try {
            DB::table($tableName)->truncate();

            $uniqueBy = ['item_code'];
            $updateColumns = array_diff($columns, $uniqueBy);

            foreach (array_chunk($insertData, 500) as $chunk) {
                DB::table($tableName)->upsert($chunk, $uniqueBy, $updateColumns);
            }

            return [
                'success' => true,
                'message' => "Synced '{$tableName}' successfully with union. Loaded " . count($insertData) . " rows.",
            ];
        } catch (Throwable $e) {
            Log::error("SAP Sync Exception during DB populate for {$tableName}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "DB Insert Exception: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Sync the sap_fct_lineproduction endpoint by unioning data from 4 API endpoints.
     */
    private function syncLineProductionUnion(string $startDate): array
    {
        if ($this->token === null) {
            if (!$this->login()) {
                return [
                    'success' => false,
                    'message' => "Authentication failed. Cannot run sync.",
                ];
            }
        }

        $endpoints = [
            'sap_fct_lineproduction' => [
                'path' => 'sap_fct_lineproduction/list',
                'is_first' => true,
            ],
            'sap_fct_lineproduction_semi' => [
                'path' => 'sap_fct_lineproduction_semi/list',
                'is_first' => false,
            ],
            'sap_fct_lineproduction_semi_wip' => [
                'path' => 'sap_fct_lineproduction_semi_wip/list',
                'is_first' => false,
            ],
            'sap_fct_lineproduction_semi_semi_wip' => [
                'path' => 'sap_fct_lineproduction_semi_semi_wip/list',
                'is_first' => false,
            ],
        ];

        $columnMapFirst = [
            'item_code'        => 'item_code',
            'line_production'  => 'line_production',
            'priority'         => 'priority',
        ];

        $columnMapOthers = [
            'Code'             => 'item_code',
            'LineProduction'   => 'line_production',
            'Priority'         => 'priority',
        ];

        $allRows = [];

        foreach ($endpoints as $name => $meta) {
            $url = $this->baseUrl . '/' . $meta['path'];
            $queryParams = [
                'startDate' => $startDate,
            ];

            Log::info("SAP Sync (Line Production Union): Fetching '{$name}'", [
                'url' => $url,
                'params' => $queryParams,
            ]);

            try {
                $response = Http::withToken($this->token ?? '')
                    ->timeout(60)
                    ->retry(3, 1000)
                    ->get($url, $queryParams);

                if (!$response->successful()) {
                    $errorMsg = "API request for '{$name}' failed with status: {$response->status()}";
                    Log::error("SAP Sync Error: {$errorMsg}", ['body' => $response->body()]);
                    return ['success' => false, 'message' => $errorMsg];
                }

                $body = $response->json();
                $rows = isset($body['data']) ? $body['data'] : $body;

                if (is_array($rows)) {
                    $map = $meta['is_first'] ? $columnMapFirst : $columnMapOthers;
                    foreach ($rows as $row) {
                        $allRows[] = [
                            'row' => $row,
                            'map' => $map,
                        ];
                    }
                    Log::info("SAP Sync (Line Production Union): '{$name}' fetched. Rows: " . count($rows));
                }
            } catch (Throwable $e) {
                Log::error("SAP Sync Exception on '{$name}': " . $e->getMessage());
                return [
                    'success' => false,
                    'message' => "Exception on '{$name}': " . $e->getMessage(),
                ];
            }
        }

        $tableName = 'sap_fct_lineproductions';
        if (!Schema::hasTable($tableName)) {
            return [
                'success' => false,
                'message' => "Table '{$tableName}' does not exist.",
            ];
        }

        $columns = Schema::getColumnListing($tableName);
        $columnFlip = array_flip($columns);

        $insertData = [];
        foreach ($allRows as $item) {
            $rowArray = (array) $item['row'];
            $map = $item['map'];

            // Apply explicit column map: rename API keys to DB column names
            $remapped = [];
            foreach ($rowArray as $apiKey => $value) {
                $dbKey = $map[$apiKey] ?? $apiKey;
                $remapped[$dbKey] = $value;
            }
            $rowArray = $remapped;

            // Convert priority to integer
            if (isset($rowArray['priority'])) {
                $rowArray['priority'] = is_numeric($rowArray['priority'])
                    ? (int) $rowArray['priority']
                    : 0;
            }

            $filtered = array_intersect_key($rowArray, $columnFlip);

            // Skip rows that don't have a valid item_code
            if (!isset($filtered['item_code']) || $filtered['item_code'] === '' || $filtered['item_code'] === null) {
                continue;
            }

            $insertData[] = $filtered;
        }

        try {
            DB::table($tableName)->truncate();

            foreach (array_chunk($insertData, 500) as $chunk) {
                DB::table($tableName)->insert($chunk);
            }

            return [
                'success' => true,
                'message' => "Synced '{$tableName}' successfully with union. Loaded " . count($insertData) . " rows.",
            ];
        } catch (Throwable $e) {
            Log::error("SAP Sync Exception during DB populate for {$tableName}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "DB Insert Exception: " . $e->getMessage(),
            ];
        }
    }
}
