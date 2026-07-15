<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\User;
use App\Models\AssetComponent;
use App\Models\ComponentType;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AssetManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_asset_with_it_fields()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = AssetCategory::create(['name' => 'Server Hardware']);
        $location = AssetLocation::create(['name' => 'Datacenter A']);
        $department = Department::create([
            'name' => 'IT Department',
            'code' => 'IT',
            'dept_no' => '123',
            'is_office' => true
        ]);

        Livewire::test(\App\Livewire\Assets\AssetManager::class)
            ->set('name', 'IT Web Server')
            ->set('brand', 'Dell')
            ->set('category_id', $category->id)
            ->set('status', 'in_stock')
            ->set('location_id', $location->id)
            ->set('ip_address', '192.168.1.100')
            ->set('username', 'web-admin')
            ->set('purpose', 'Web Server hosting intranet')
            ->set('os', 'Ubuntu 22.04 LTS')
            ->set('department_id', $department->id)
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('assets', [
            'name' => 'IT Web Server',
            'ip_address' => '192.168.1.100',
            'username' => 'web-admin',
            'department_id' => $department->id,
        ]);
    }

    public function test_can_manage_asset_components_and_service_records()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = AssetCategory::create(['name' => 'Laptops']);
        $asset = Asset::create([
            'name' => 'IT Laptop',
            'brand' => 'Lenovo',
            'asset_tag' => 'IT-LAP-2026-001',
            'category_id' => $category->id,
            'status' => 'in_stock',
        ]);

        $compType = ComponentType::create([
            'category' => 'hardware',
            'name' => 'Memory',
        ]);

        // Test AssetShow Livewire component
        Livewire::test(\App\Livewire\Assets\AssetShow::class, ['id' => $asset->id])
            ->set('componentType', 'hardware')
            ->set('componentTypeName', 'Memory')
            ->set('componentName', '16GB DDR4')
            ->set('componentBrand', 'Crucial')
            ->call('saveComponent')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('asset_components', [
            'asset_id' => $asset->id,
            'component_type' => 'hardware',
            'type_name' => 'Memory',
            'name' => '16GB DDR4',
        ]);

        // Log a service record
        Livewire::test(\App\Livewire\Assets\AssetShow::class, ['id' => $asset->id])
            ->set('serviceRequestedBy', 'John Doe')
            ->set('serviceAction', 'replacement')
            ->set('serviceComponentType', 'hardware')
            ->set('serviceOldPart', '16GB DDR4')
            ->set('serviceNewTypeName', 'Memory')
            ->set('serviceNewName', '32GB DDR4')
            ->set('serviceNewBrand', 'Corsair')
            ->call('saveService')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('asset_service_records', [
            'asset_id' => $asset->id,
            'requested_by' => 'John Doe',
            'action' => 'replacement',
            'old_part' => '16GB DDR4',
            'new_name' => '32GB DDR4',
        ]);

        $record = \App\Models\AssetServiceRecord::first();

        // Apply service record
        Livewire::test(\App\Livewire\Assets\AssetShow::class, ['id' => $asset->id])
            ->call('applyService', $record->id)
            ->assertHasNoErrors();

        // The component should be replaced
        $this->assertDatabaseHas('asset_components', [
            'asset_id' => $asset->id,
            'name' => '32GB DDR4',
            'brand' => 'Corsair',
        ]);

        // The service record should be marked as applied
        $this->assertNotNull($record->refresh()->action_date);
    }
}
