<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\Consumable;
use App\Models\ConsumableCategory;
use App\Models\AssetComponent;
use App\Models\ComponentType;
use App\Models\AssetServiceRecord;
use App\Models\MaintenanceChecklistGroup;
use App\Models\MaintenanceChecklistItem;
use App\Models\AssetMaintenanceReport;
use App\Models\MaintenanceReportDetail;
use App\Infrastructure\Persistence\Eloquent\Models\Department;

class MigrateLegacyInventorySeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            \Illuminate\Database\Eloquent\Model::unguard();

            // 1. Migrate Categories & Consumables
            $legacyStockTypes = DB::table('stock_type')->get();
            foreach ($legacyStockTypes as $type) {
                ConsumableCategory::updateOrCreate(['id' => $type->id], ['name' => $type->name]);
            }

            $legacyStocks = DB::table('master_stock')->get();
            foreach ($legacyStocks as $stock) {
                Consumable::updateOrCreate(
                    ['sku' => $stock->stock_code],
                    [
                        'name' => $stock->stock_description,
                        'category_id' => $stock->stock_type_id,
                        'current_stock' => $stock->stock_quantity,
                    ]
                );
            }

            // 2. Migrate Master Inventory -> Assets
            $legacyInventories = DB::table('master_inventories')->get();
            foreach ($legacyInventories as $inv) {
                // Resolve category (type)
                $category = AssetCategory::firstOrCreate(['name' => $inv->type ?? 'Other']);
                
                // Parse department
                $deptId = null;
                if ($inv->dept) {
                    $dept = Department::where('name', 'like', '%' . $inv->dept . '%')->first();
                    $deptId = $dept?->id;
                }

                Asset::updateOrCreate(
                    ['id' => $inv->id],
                    [
                        'name' => 'Legacy Device - ' . ($inv->username ?? 'Unassigned'),
                        'brand' => $inv->brand,
                        'asset_tag' => $inv->no_inventaris ?? ('IT-LEG-' . str_pad((string)$inv->id, 4, '0', STR_PAD_LEFT)),
                        'serial_number' => null, // Not tracked in legacy header
                        'category_id' => $category->id,
                        'status' => 'in_stock',
                        'notes' => $inv->description,
                        'ip_address' => $inv->ip_address,
                        'username' => $inv->username,
                        'purpose' => $inv->purpose,
                        'os' => $inv->os,
                        'position_image' => $inv->position_image,
                        'department_id' => $deptId,
                    ]
                );
            }

            // 3. Migrate Hardware/Software Types -> Component Types
            $legacyHwTypes = DB::table('hardware_type_inventories')->get();
            foreach ($legacyHwTypes as $type) {
                ComponentType::firstOrCreate(['category' => 'hardware', 'name' => $type->name]);
            }

            $legacySwTypes = DB::table('software_type_inventories')->get();
            foreach ($legacySwTypes as $type) {
                ComponentType::firstOrCreate(['category' => 'software', 'name' => $type->name]);
            }

            // 4. Migrate DetailHardware -> Asset Components
            $legacyHwDetails = DB::table('detail_hardwares')->get();
            foreach ($legacyHwDetails as $detail) {
                // Ensure parent asset exists
                if (!Asset::find($detail->master_inventory_id)) continue;

                $typeName = DB::table('hardware_type_inventories')->where('id', $detail->hardware_id)->value('name') ?? 'Hardware';

                AssetComponent::create([
                    'asset_id' => $detail->master_inventory_id,
                    'component_type' => 'hardware',
                    'type_name' => $typeName,
                    'brand' => $detail->brand,
                    'name' => $detail->hardware_name,
                    'serial_number' => null,
                    'remark' => $detail->remark,
                ]);
            }

            // 5. Migrate DetailSoftware -> Asset Components
            $legacySwDetails = DB::table('detail_softwares')->get();
            foreach ($legacySwDetails as $detail) {
                if (!Asset::find($detail->master_inventory_id)) continue;

                $typeName = DB::table('software_type_inventories')->where('id', $detail->software_id)->value('name') ?? 'Software';

                AssetComponent::create([
                    'asset_id' => $detail->master_inventory_id,
                    'component_type' => 'software',
                    'type_name' => $typeName,
                    'brand' => $detail->software_brand,
                    'name' => $detail->software_name,
                    'license' => $detail->license,
                    'remark' => $detail->remark,
                ]);
            }

            // 6. Migrate Repair History -> Service Records
            $legacyRepairs = DB::table('inventory_repair_histories')->get();
            foreach ($legacyRepairs as $repair) {
                if (!Asset::find($repair->master_id)) continue;

                AssetServiceRecord::create([
                    'asset_id' => $repair->master_id,
                    'requested_by' => $repair->request_name ?? 'Legacy System',
                    'action' => $repair->action === 'replacement' ? 'replacement' : ($repair->action === 'installation' ? 'installation' : 'repair'),
                    'component_type' => $repair->type === 'hardware' ? 'hardware' : 'software',
                    'old_part' => $repair->old_part,
                    'new_brand' => $repair->item_brand,
                    'new_name' => $repair->item_name,
                    'action_date' => $repair->action_date,
                    'remark' => $repair->remark,
                ]);
            }

            // 7. Migrate Maintenance Checklist Groups & Items
            $legacyGroups = DB::table('group_maintenance_inventory_reports')->get();
            foreach ($legacyGroups as $group) {
                MaintenanceChecklistGroup::updateOrCreate(['id' => $group->id], ['name' => $group->name]);
            }

            $legacyCats = DB::table('category_maintenance_inventory_reports')->get();
            foreach ($legacyCats as $cat) {
                MaintenanceChecklistItem::updateOrCreate(
                    ['id' => $cat->id],
                    [
                        'group_id' => $cat->group_id,
                        'name' => $cat->name
                    ]
                );
            }

            // 8. Migrate Maintenance Reports (Headers & Details)
            $legacyHeaders = DB::table('header_maintenance_inventory_reports')->get();
            foreach ($legacyHeaders as $header) {
                if (!Asset::find($header->master_id)) continue;

                // Calculate period/year to avoid unique constraint violations
                $date = \Carbon\Carbon::parse($header->created_at);
                $year = $date->year;
                $month = $date->month;
                $period = $month <= 4 ? 1 : ($month <= 8 ? 2 : 3);

                $existingReport = AssetMaintenanceReport::where('asset_id', $header->master_id)
                    ->where('period', $period)
                    ->where('year', $year)
                    ->where('id', '!=', $header->id)
                    ->first();

                if ($existingReport) {
                    continue; // Skip duplicates that violate the new unique rule
                }

                $report = AssetMaintenanceReport::updateOrCreate(
                    ['id' => $header->id],
                    [
                        'document_number' => $header->no_dokumen,
                        'asset_id' => $header->master_id,
                        'revision_date' => $header->revision_date,
                        'created_at' => $header->created_at,
                        'updated_at' => $header->updated_at,
                    ]
                );

                $legacyDetails = DB::table('detail_maintenance_inventory_reports')
                    ->where('header_id', $header->id)
                    ->get();

                foreach ($legacyDetails as $detail) {
                    if (!MaintenanceChecklistItem::find($detail->category_id)) continue;

                    MaintenanceReportDetail::create([
                        'report_id' => $report->id,
                        'checklist_item_id' => $detail->category_id,
                        'condition' => in_array($detail->condition, ['good', 'bad']) ? $detail->condition : 'good',
                        'remark' => $detail->remark,
                        'checked_by' => $detail->checked_by ?? 'System',
                    ]);
                }
            }

            \Illuminate\Database\Eloquent\Model::reguard();
        });
    }
}
