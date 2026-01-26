<?php

namespace Tests\Unit\Domain\Inventory;

use App\Domain\Inventory\Services\InventoryManagementService;
use App\Models\Department;
use App\Models\DetailHardware;
use App\Models\DetailSoftware;
use App\Models\HardwareTypeInventory;
use App\Models\MasterInventory;
use App\Models\SoftwareTypeInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InventoryManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    private InventoryManagementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InventoryManagementService();
        Storage::fake('public');
    }

    /** @test */
    public function it_can_get_inventory_items_with_pagination()
    {
        MasterInventory::factory()->count(15)->create();

        $result = $this->service->getInventoryItems([], 10);

        $this->assertCount(10, $result);
        $this->assertEquals(15, $result->total());
    }

    /** @test */
    public function it_can_filter_inventory_items_by_department()
    {
        MasterInventory::factory()->count(3)->create(['dept' => 'IT']);
        MasterInventory::factory()->count(2)->create(['dept' => 'HR']);

        $result = $this->service->getInventoryItems(['dept' => 'IT'], 10);

        $this->assertCount(3, $result);
    }

    /** @test */
    public function it_can_get_all_items_when_pagination_is_all()
    {
        MasterInventory::factory()->count(15)->create();

        $result = $this->service->getInventoryItems([], 'all');

        $this->assertCount(15, $result);
    }

    /** @test */
    public function it_can_store_inventory_with_image()
    {
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
                    'remark' => 'CPU'
                ]
            ]
        ];

        $inventory = $this->service->storeInventory($data, $image);

        $this->assertDatabaseHas('master_inventories', [
            'ip_address' => '192.168.1.100',
            'username' => 'john_doe'
        ]);
        Storage::disk('public')->assertExists('masterinventory/' . $image->getClientOriginalName());
        $this->assertDatabaseHas('detail_hardwares', [
            'master_inventory_id' => $inventory->id,
            'hardware_name' => 'i7-12700K'
        ]);
    }

    /** @test */
    public function it_can_update_inventory_and_sync_hardwares()
    {
        $inventory = MasterInventory::factory()->create();
        $hardwareType = HardwareTypeInventory::factory()->create();
        DetailHardware::factory()->create([
            'master_inventory_id' => $inventory->id,
            'hardware_name' => 'Old Hardware'
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
                    'remark' => 'New CPU'
                ]
            ]
        ];

        $updated = $this->service->updateInventory($inventory->id, $data, null);

        $this->assertEquals('192.168.1.101', $updated->ip_address);
        $this->assertDatabaseHas('detail_hardwares', [
            'master_inventory_id' => $inventory->id,
            'hardware_name' => 'Ryzen 9'
        ]);
        $this->assertDatabaseMissing('detail_hardwares', [
            'master_inventory_id' => $inventory->id,
            'hardware_name' => 'Old Hardware'
        ]);
    }

    /** @test */
    public function it_can_delete_inventory_and_related_details()
    {
        $inventory = MasterInventory::factory()->create();
        DetailHardware::factory()->count(2)->create(['master_inventory_id' => $inventory->id]);
        DetailSoftware::factory()->count(2)->create(['master_inventory_id' => $inventory->id]);

        $this->service->deleteInventory($inventory->id);

        $this->assertDatabaseMissing('master_inventories', ['id' => $inventory->id]);
        $this->assertDatabaseCount('detail_hardwares', 0);
        $this->assertDatabaseCount('detail_softwares', 0);
    }

    /** @test */
    public function it_can_add_hardware_type()
    {
        $type = $this->service->addHardwareType('Graphics Card');

        $this->assertDatabaseHas('hardware_type_inventories', [
            'name' => 'Graphics Card'
        ]);
        $this->assertInstanceOf(HardwareTypeInventory::class, $type);
    }

    /** @test */
    public function it_can_add_software_type()
    {
        $type = $this->service->addSoftwareType('Operating System');

        $this->assertDatabaseHas('software_type_inventories', [
            'name' => 'Operating System'
        ]);
        $this->assertInstanceOf(SoftwareTypeInventory::class, $type);
    }

    /** @test */
    public function it_can_delete_hardware_type()
    {
        $type = HardwareTypeInventory::factory()->create();

        $result = $this->service->deleteType($type->id, 'hardware');

        $this->assertTrue($result);
        $this->assertDatabaseMissing('hardware_type_inventories', ['id' => $type->id]);
    }

    /** @test */
    public function it_can_delete_software_type()
    {
        $type = SoftwareTypeInventory::factory()->create();

        $result = $this->service->deleteType($type->id, 'software');

        $this->assertTrue($result);
        $this->assertDatabaseMissing('software_type_inventories', ['id' => $type->id]);
    }

    /** @test */
    public function it_returns_false_when_deleting_non_existent_type()
    {
        $result = $this->service->deleteType(999, 'hardware');

        $this->assertFalse($result);
    }
}
