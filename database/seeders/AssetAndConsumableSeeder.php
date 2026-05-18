<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\Asset;
use App\Models\ConsumableCategory;
use App\Models\Consumable;
use App\Models\StockTransaction;
use App\Models\User;

class AssetAndConsumableSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Locations
        $locations = [
            ['name' => 'Main Office'],
            ['name' => 'IT Department'],
            ['name' => 'Server Room'],
            ['name' => 'Warehouse'],
        ];
        foreach ($locations as $loc) {
            AssetLocation::create($loc);
        }

        // 2. Asset Categories
        $assetCats = [
            ['name' => 'Laptops'],
            ['name' => 'Monitors'],
            ['name' => 'Furniture'],
            ['name' => 'Printers'],
        ];
        foreach ($assetCats as $cat) {
            AssetCategory::create($cat);
        }

        // 3. Consumable Categories
        $consCats = [
            ['name' => 'Stationery'],
            ['name' => 'Cleaning Supplies'],
            ['name' => 'Breakroom'],
        ];
        foreach ($consCats as $cat) {
            ConsumableCategory::create($cat);
        }

        // Get some IDs
        $locIds = AssetLocation::pluck('id')->toArray();
        $assetCatIds = AssetCategory::pluck('id')->toArray();
        $consCatIds = ConsumableCategory::pluck('id')->toArray();
        $userIds = User::pluck('id')->toArray();

        if (empty($userIds)) {
            $userIds = [1]; // Fallback if no users exist
        }

        // 4. Assets
        $assets = [
            [
                'name' => 'Dell XPS 15',
                'asset_tag' => 'AST-001',
                'serial_number' => 'SN-123456',
                'category_id' => $assetCatIds[0],
                'status' => 'assigned',
                'location_id' => $locIds[1],
                'assigned_to_user_id' => $userIds[0],
                'purchase_date' => '2025-01-15',
                'purchase_cost' => 1500.00,
                'warranty_expiry' => '2027-01-15',
                'notes' => 'Developer machine',
            ],
            [
                'name' => 'MacBook Pro 16',
                'asset_tag' => 'AST-002',
                'serial_number' => 'SN-789012',
                'category_id' => $assetCatIds[0],
                'status' => 'in_stock',
                'location_id' => $locIds[1],
                'assigned_to_user_id' => null,
                'purchase_date' => '2025-02-20',
                'purchase_cost' => 2500.00,
                'warranty_expiry' => '2028-02-20',
                'notes' => 'Spare laptop',
            ],
            [
                'name' => 'LG UltraWide Monitor',
                'asset_tag' => 'AST-003',
                'serial_number' => 'SN-345678',
                'category_id' => $assetCatIds[1],
                'status' => 'assigned',
                'location_id' => $locIds[1],
                'assigned_to_user_id' => $userIds[0],
                'purchase_date' => '2025-03-05',
                'purchase_cost' => 400.00,
                'warranty_expiry' => '2026-03-05',
                'notes' => 'Dual monitor setup',
            ],
            [
                'name' => 'Ergonomic Office Chair',
                'asset_tag' => 'AST-004',
                'serial_number' => null,
                'category_id' => $assetCatIds[2],
                'status' => 'in_stock',
                'location_id' => $locIds[0],
                'assigned_to_user_id' => null,
                'purchase_date' => '2024-12-01',
                'purchase_cost' => 200.00,
                'warranty_expiry' => null,
                'notes' => 'Comfortable chair',
            ],
        ];

        foreach ($assets as $asset) {
            Asset::create($asset);
        }

        // 5. Consumables
        $consumables = [
            [
                'name' => 'A4 Paper Ream',
                'sku' => 'PAP-A4',
                'category_id' => $consCatIds[0],
                'current_stock' => 50,
                'min_stock' => 10,
                'unit' => 'box',
                'reorder_point' => 15,
                'location_id' => $locIds[3],
            ],
            [
                'name' => 'Black Ballpoint Pens (Pack of 10)',
                'sku' => 'PEN-BLK',
                'category_id' => $consCatIds[0],
                'current_stock' => 5,
                'min_stock' => 5,
                'unit' => 'pcs',
                'reorder_point' => 5,
                'location_id' => $locIds[3],
            ],
            [
                'name' => 'Hand Sanitizer 500ml',
                'sku' => 'SAN-500',
                'category_id' => $consCatIds[1],
                'current_stock' => 20,
                'min_stock' => 5,
                'unit' => 'bottle',
                'reorder_point' => 8,
                'location_id' => $locIds[0],
            ],
        ];

        foreach ($consumables as $cons) {
            Consumable::create($cons);
        }

        // 6. Stock Transactions
        $consIds = Consumable::pluck('id')->toArray();
        if (!empty($consIds)) {
            StockTransaction::create([
                'consumable_id' => $consIds[0],
                'type' => 'In',
                'quantity' => 50,
                'user_id' => $userIds[0],
                'notes' => 'Initial stock',
                'reference' => 'PO-2026-001',
            ]);
            StockTransaction::create([
                'consumable_id' => $consIds[1],
                'type' => 'In',
                'quantity' => 5,
                'user_id' => $userIds[0],
                'notes' => 'Initial stock',
                'reference' => 'PO-2026-002',
            ]);
        }
    }
}
