<?php

use App\Domain\Inventory\Services\InventoryManagementService;
use App\Models\DetailHardware;
use App\Models\DetailSoftware;
use App\Models\HardwareTypeInventory;
use App\Models\MasterInventory;
use App\Models\SoftwareTypeInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new InventoryManagementService;
    Storage::fake('public');
});

test('it can get inventory items with pagination', function () {
    MasterInventory::factory()->count(15)->create();

    $result = $this->service->getInventoryItems([], 10);

    expect($result)->toHaveCount(10);
    expect($result->total())->toBe(15);
});

test('it can filter inventory items by department', function () {
    MasterInventory::factory()->count(3)->create(['dept' => 'IT']);
    MasterInventory::factory()->count(2)->create(['dept' => 'HR']);

    $result = $this->service->getInventoryItems(['dept' => 'IT'], 10);

    expect($result)->toHaveCount(3);
});

test('it can get all items when pagination is all', function () {
    MasterInventory::factory()->count(15)->create();

    $result = $this->service->getInventoryItems([], 'all');

    expect($result)->toHaveCount(15);
});

test('it can store inventory with image', function () {
    $image = UploadedFile::fake()->image('position.jpg');
    $hardwareType = HardwareTypeInventory::factory()->create();

    $data = [
        'ip_address' => '192.168.1.100',
        'username' => 'john_doe',
        'dept' => 'IT',
        'type' => 'Desktop',
        'purpose' => 'Development',
        'brand' => 'Dell',
        'os' => 'Windows 11',
        'description' => 'Developer workstation',
        'hardwares' => [
            [
                'type' => $hardwareType->id,
                'brand' => 'Intel',
                'hardware_name' => 'i7-12700K',
                'remark' => 'CPU',
            ],
        ],
    ];

    $inventory = $this->service->storeInventory($data, $image);

    $this->assertDatabaseHas('master_inventories', [
        'ip_address' => '192.168.1.100',
        'username' => 'john_doe',
    ]);
    Storage::disk('public')->assertExists('masterinventory/' . $image->getClientOriginalName());
    $this->assertDatabaseHas('detail_hardwares', [
        'master_inventory_id' => $inventory->id,
        'hardware_name' => 'i7-12700K',
    ]);
});

test('it can update inventory and sync hardwares', function () {
    $inventory = MasterInventory::factory()->create();
    $hardwareType = HardwareTypeInventory::factory()->create();
    DetailHardware::factory()->create([
        'master_inventory_id' => $inventory->id,
        'hardware_name' => 'Old Hardware',
    ]);

    $data = [
        'ip_address' => '192.168.1.101',
        'username' => 'updated_user',
        'dept' => 'IT',
        'type' => 'Laptop',
        'purpose' => 'Testing',
        'brand' => 'HP',
        'os' => 'Ubuntu',
        'description' => 'Test machine',
        'hardwares' => [
            [
                'type' => $hardwareType->id,
                'brand' => 'AMD',
                'hardware_name' => 'Ryzen 9',
                'remark' => 'New CPU',
            ],
        ],
    ];

    $updated = $this->service->updateInventory($inventory->id, $data, null);

    expect($updated->ip_address)->toBe('192.168.1.101');
    $this->assertDatabaseHas('detail_hardwares', [
        'master_inventory_id' => $inventory->id,
        'hardware_name' => 'Ryzen 9',
    ]);
    $this->assertDatabaseMissing('detail_hardwares', [
        'master_inventory_id' => $inventory->id,
        'hardware_name' => 'Old Hardware',
    ]);
});

test('it can delete inventory and related details', function () {
    $inventory = MasterInventory::factory()->create();
    DetailHardware::factory()->count(2)->create(['master_inventory_id' => $inventory->id]);
    DetailSoftware::factory()->count(2)->create(['master_inventory_id' => $inventory->id]);

    $this->service->deleteInventory($inventory->id);

    $this->assertDatabaseMissing('master_inventories', ['id' => $inventory->id]);
    $this->assertDatabaseCount('detail_hardwares', 0);
    $this->assertDatabaseCount('detail_softwares', 0);
});

test('it can add hardware type', function () {
    $type = $this->service->addHardwareType('Graphics Card');

    $this->assertDatabaseHas('hardware_type_inventories', [
        'name' => 'Graphics Card',
    ]);
    expect($type)->toBeInstanceOf(HardwareTypeInventory::class);
});

test('it can add software type', function () {
    $type = $this->service->addSoftwareType('Operating System');

    $this->assertDatabaseHas('software_type_inventories', [
        'name' => 'Operating System',
    ]);
    expect($type)->toBeInstanceOf(SoftwareTypeInventory::class);
});

test('it can delete hardware type', function () {
    $type = HardwareTypeInventory::factory()->create();

    $result = $this->service->deleteType($type->id, 'hardware');

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('hardware_type_inventories', ['id' => $type->id]);
});

test('it can delete software type', function () {
    $type = SoftwareTypeInventory::factory()->create();

    $result = $this->service->deleteType($type->id, 'software');

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('software_type_inventories', ['id' => $type->id]);
});

test('it returns false when deleting non existent type', function () {
    $result = $this->service->deleteType(999, 'hardware');

    expect($result)->toBeFalse();
});
