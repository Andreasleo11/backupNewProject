<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMaintenanceReport;
use App\Models\MaintenanceChecklistGroup;
use App\Models\MaintenanceChecklistItem;
use App\Models\MaintenanceReportDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AssetMaintenanceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_load_maintenance_report_manager()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\App\Livewire\Assets\AssetMaintenanceReportManager::class)
            ->assertStatus(200);
    }

    public function test_can_create_maintenance_report_with_checklist()
    {
        $user = User::factory()->create(['name' => 'vicky']);
        $this->actingAs($user);

        $category = AssetCategory::create(['name' => 'Laptops']);
        $asset = Asset::create([
            'name' => 'Test Laptop',
            'brand' => 'Dell',
            'asset_tag' => 'IT-LAP-2026-001',
            'category_id' => $category->id,
            'status' => 'in_stock',
        ]);

        $group = MaintenanceChecklistGroup::create(['name' => 'Hardware']);
        $item = MaintenanceChecklistItem::create([
            'group_id' => $group->id,
            'name' => 'Monitor',
        ]);

        // Access the component and simulate form submission
        Livewire::test(\App\Livewire\Assets\AssetMaintenanceReportManager::class)
            ->call('showAddForm', $asset->id)
            ->set('checklist.' . $item->id, [
                'checked' => true,
                'condition' => 'good',
                'remark' => 'Clean screen',
                'checked_by' => 'vicky',
            ])
            ->call('store')
            ->assertHasNoErrors();

        // Calculate expected period
        $month = now()->month;
        $year = now()->year;
        $period = ($month >= 1 && $month <= 4) ? 1 : (($month >= 5 && $month <= 8) ? 2 : 3);

        $this->assertDatabaseHas('asset_maintenance_reports', [
            'asset_id' => $asset->id,
            'period' => $period,
            'year' => $year,
        ]);

        $report = AssetMaintenanceReport::first();

        $this->assertDatabaseHas('maintenance_report_details', [
            'report_id' => $report->id,
            'checklist_item_id' => $item->id,
            'condition' => 'good',
            'remark' => 'Clean screen',
            'checked_by' => 'vicky',
        ]);
    }

    public function test_cannot_duplicate_report_in_same_period()
    {
        $user = User::factory()->create(['name' => 'vicky']);
        $this->actingAs($user);

        $category = AssetCategory::create(['name' => 'Laptops']);
        $asset = Asset::create([
            'name' => 'Test Laptop',
            'brand' => 'Dell',
            'asset_tag' => 'IT-LAP-2026-001',
            'category_id' => $category->id,
            'status' => 'in_stock',
        ]);

        $month = now()->month;
        $year = now()->year;
        $period = ($month >= 1 && $month <= 4) ? 1 : (($month >= 5 && $month <= 8) ? 2 : 3);

        // Pre-create a report
        $report = AssetMaintenanceReport::create([
            'document_number' => 'MIR/260715/001',
            'asset_id' => $asset->id,
            'period' => $period,
            'year' => $year,
        ]);

        // Attempting to create another report for the same asset in the same period should fail validation
        Livewire::test(\App\Livewire\Assets\AssetMaintenanceReportManager::class)
            ->call('showAddForm', $asset->id)
            ->call('store')
            ->assertHasErrors(['assetId']);
    }

    public function test_can_add_custom_checklist_item_dynamically()
    {
        $user = User::factory()->create(['name' => 'vicky']);
        $this->actingAs($user);

        $category = AssetCategory::create(['name' => 'Laptops']);
        $asset = Asset::create([
            'name' => 'Test Laptop',
            'brand' => 'Dell',
            'asset_tag' => 'IT-LAP-2026-001',
            'category_id' => $category->id,
            'status' => 'in_stock',
        ]);

        $group = MaintenanceChecklistGroup::create(['name' => 'Hardware']);

        Livewire::test(\App\Livewire\Assets\AssetMaintenanceReportManager::class)
            ->call('showAddForm', $asset->id)
            ->call('addNewChecklistItem', $group->id)
            ->set('newItems.0.name', 'Webcam')
            ->set('newItems.0.condition', 'bad')
            ->set('newItems.0.remark', 'Blurry')
            ->set('newItems.0.checked_by', 'vicky')
            ->call('store')
            ->assertHasNoErrors();

        // Check if custom item was persisted in checklist items table
        $this->assertDatabaseHas('maintenance_checklist_items', [
            'group_id' => $group->id,
            'name' => 'Webcam',
        ]);

        $item = MaintenanceChecklistItem::where('name', 'Webcam')->first();
        $report = AssetMaintenanceReport::first();

        // Check if detail record is mapped
        $this->assertDatabaseHas('maintenance_report_details', [
            'report_id' => $report->id,
            'checklist_item_id' => $item->id,
            'condition' => 'bad',
            'remark' => 'Blurry',
            'checked_by' => 'vicky',
        ]);
    }

    public function test_can_update_existing_report()
    {
        $user = User::factory()->create(['name' => 'vicky']);
        $this->actingAs($user);

        $category = AssetCategory::create(['name' => 'Laptops']);
        $asset = Asset::create([
            'name' => 'Test Laptop',
            'brand' => 'Dell',
            'asset_tag' => 'IT-LAP-2026-001',
            'category_id' => $category->id,
            'status' => 'in_stock',
        ]);

        $group = MaintenanceChecklistGroup::create(['name' => 'Hardware']);
        $item = MaintenanceChecklistItem::create([
            'group_id' => $group->id,
            'name' => 'Monitor',
        ]);

        $month = now()->month;
        $year = now()->year;
        $period = ($month >= 1 && $month <= 4) ? 1 : (($month >= 5 && $month <= 8) ? 2 : 3);

        $report = AssetMaintenanceReport::create([
            'document_number' => 'MIR/260715/001',
            'asset_id' => $asset->id,
            'period' => $period,
            'year' => $year,
        ]);

        $detail = MaintenanceReportDetail::create([
            'report_id' => $report->id,
            'checklist_item_id' => $item->id,
            'condition' => 'good',
            'remark' => 'Initial check',
            'checked_by' => 'vicky',
        ]);

        // Simulating the edit and update action
        Livewire::test(\App\Livewire\Assets\AssetMaintenanceReportManager::class)
            ->call('edit', $report->id)
            ->set('checklist.' . $item->id . '.condition', 'bad')
            ->set('checklist.' . $item->id . '.remark', 'Now screen is cracked')
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('maintenance_report_details', [
            'report_id' => $report->id,
            'checklist_item_id' => $item->id,
            'condition' => 'bad',
            'remark' => 'Now screen is cracked',
            'checked_by' => 'vicky',
        ]);
    }

    public function test_can_migrate_legacy_data_correctly()
    {
        // 1. Setup legacy tables structure & dummy data
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

        $department = \App\Infrastructure\Persistence\Eloquent\Models\Department::create([
            'name' => 'IT Department',
            'code' => 'IT',
            'dept_no' => '123',
            'is_office' => true
        ]);
        
        \DB::table('stock_type')->insert([
            'id' => 1,
            'name' => 'Legacy Ink Category',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('master_stock')->insert([
            'id' => 1,
            'stock_code' => 'T-001',
            'stock_description' => 'Legacy Black Ink',
            'stock_type_id' => 1,
            'dept_id' => $department->id,
            'stock_quantity' => 12,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('master_inventories')->insert([
            'id' => 10,
            'username' => 'legacy-op',
            'ip_address' => '192.168.99.99',
            'type' => 'Legacy Server',
            'brand' => 'Dell Legacy',
            'dept' => 'IT Department',
            'description' => 'Legacy server notes',
            'os' => 'CentOS 7',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('hardware_type_inventories')->insert([
            'id' => 5,
            'name' => 'Legacy CPU',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('detail_hardwares')->insert([
            'id' => 1,
            'master_inventory_id' => 10,
            'hardware_id' => 5,
            'hardware_name' => 'Intel Xeon 4 Core',
            'brand' => 'Intel',
            'remark' => 'Legacy CPU remark',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('software_type_inventories')->insert([
            'id' => 6,
            'name' => 'Legacy AntiVirus',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('detail_softwares')->insert([
            'id' => 1,
            'master_inventory_id' => 10,
            'software_id' => 6,
            'software_name' => 'McAfee Enterprise',
            'software_brand' => 'Intel Security',
            'license' => 'LIC-LEG-99',
            'remark' => 'Legacy AV remark',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('inventory_repair_histories')->insert([
            'id' => 1,
            'master_id' => 10,
            'request_name' => 'Legacy Requestor',
            'action' => 'repair',
            'type' => 'hardware',
            'item_type' => 'RAM',
            'old_part' => 'Failed Core',
            'item_brand' => 'Intel',
            'item_name' => 'Xeon replacement',
            'action_date' => '2026-07-10',
            'remark' => 'Fixed overheating',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('group_maintenance_inventory_reports')->insert([
            'id' => 15,
            'name' => 'Legacy System Checklist Group',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('category_maintenance_inventory_reports')->insert([
            'id' => 20,
            'group_id' => 15,
            'name' => 'Legacy Checklist Item',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('header_maintenance_inventory_reports')->insert([
            'id' => 30,
            'no_dokumen' => 'MIR/260710/099',
            'master_id' => 10,
            'revision_date' => '2026-07-11',
            'created_at' => '2026-07-10 10:00:00',
            'updated_at' => '2026-07-10 10:00:00',
        ]);

        \DB::table('detail_maintenance_inventory_reports')->insert([
            'id' => 1,
            'header_id' => 30,
            'category_id' => 20,
            'condition' => 'bad',
            'remark' => 'Failed testing',
            'checked_by' => 'legacy-checker',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // 2. Run Seeder
        $seeder = new \Database\Seeders\MigrateLegacyInventorySeeder();
        $seeder->run();

        // 3. Verify mappings in new tables
        $this->assertDatabaseHas('consumable_categories', ['id' => 1, 'name' => 'Legacy Ink Category']);
        $this->assertDatabaseHas('consumables', [
            'sku' => 'T-001',
            'name' => 'Legacy Black Ink',
            'category_id' => 1,
            'current_stock' => 12
        ]);

        $this->assertDatabaseHas('assets', [
            'id' => 10,
            'username' => 'legacy-op',
            'ip_address' => '192.168.99.99',
            'brand' => 'Dell Legacy',
            'notes' => 'Legacy server notes',
            'os' => 'CentOS 7',
        ]);

        $this->assertDatabaseHas('component_types', ['category' => 'hardware', 'name' => 'Legacy CPU']);
        $this->assertDatabaseHas('component_types', ['category' => 'software', 'name' => 'Legacy AntiVirus']);

        $this->assertDatabaseHas('asset_components', [
            'asset_id' => 10,
            'component_type' => 'hardware',
            'type_name' => 'Legacy CPU',
            'brand' => 'Intel',
            'name' => 'Intel Xeon 4 Core',
            'remark' => 'Legacy CPU remark'
        ]);

        $this->assertDatabaseHas('asset_components', [
            'asset_id' => 10,
            'component_type' => 'software',
            'type_name' => 'Legacy AntiVirus',
            'brand' => 'Intel Security',
            'name' => 'McAfee Enterprise',
            'license' => 'LIC-LEG-99',
            'remark' => 'Legacy AV remark'
        ]);

        $this->assertDatabaseHas('asset_service_records', [
            'asset_id' => 10,
            'requested_by' => 'Legacy Requestor',
            'action' => 'repair',
            'component_type' => 'hardware',
            'old_part' => 'Failed Core',
            'new_brand' => 'Intel',
            'new_name' => 'Xeon replacement',
            'action_date' => '2026-07-10',
            'remark' => 'Fixed overheating'
        ]);

        $this->assertDatabaseHas('maintenance_checklist_groups', ['id' => 15, 'name' => 'Legacy System Checklist Group']);
        $this->assertDatabaseHas('maintenance_checklist_items', ['id' => 20, 'group_id' => 15, 'name' => 'Legacy Checklist Item']);

        $this->assertDatabaseHas('asset_maintenance_reports', [
            'id' => 30,
            'document_number' => 'MIR/260710/099',
            'asset_id' => 10,
            'period' => 2, // July is period 2
            'year' => 2026,
            'revision_date' => '2026-07-11'
        ]);

        $this->assertDatabaseHas('maintenance_report_details', [
            'report_id' => 30,
            'checklist_item_id' => 20,
            'condition' => 'bad',
            'remark' => 'Failed testing',
            'checked_by' => 'legacy-checker'
        ]);
    }
}

