<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Services;

use App\Models\DetailHardware;
use App\Models\DetailSoftware;
use App\Models\HardwareTypeInventory;
use App\Models\MasterInventory;
use App\Models\SoftwareTypeInventory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

final class InventoryManagementService
{
    /**
     * Get inventory items with filtering and pagination.
     */
    public function getInventoryItems(array $filters, $itemsPerPage = 10): LengthAwarePaginator|Collection
    {
        $query = MasterInventory::with([
            'hardwares.hardwareType',
            'softwares.softwareType',
        ]);

        // Apply filters (logic from applyFilters in BaseController if applicable,
        // but here we can implement directly for the domain)
        foreach ($filters as $column => $value) {
            if (! empty($value)) {
                $query->where($column, 'like', '%' . $value . '%');
            }
        }

        if ($itemsPerPage === 'all') {
            return $query->get();
        }

        return $query->paginate($itemsPerPage);
    }

    /**
     * Store new master inventory with related data.
     */
    public function storeInventory(array $data, ?object $imageFile): MasterInventory
    {
        $imagePath = null;
        if ($imageFile) {
            $imageName = $imageFile->getClientOriginalName();
            $imagePath = $imageFile->storeAs('masterinventory', $imageName, 'public');
        }

        $masterInventory = MasterInventory::create([
            'ip_address' => $data['ip_address'],
            'username' => $data['username'],
            'position_image' => $imagePath,
            'dept' => $data['dept'],
            'type' => $data['type'],
            'purpose' => $data['purpose'],
            'brand' => $data['brand'],
            'os' => $data['os'],
            'description' => $data['description'],
        ]);

        $this->processHardwares($masterInventory, $data['hardwares'] ?? []);
        $this->processSoftwares($masterInventory, $data['softwares'] ?? []);

        return $masterInventory;
    }

    /**
     * Update existing master inventory.
     */
    public function updateInventory(int $id, array $data, ?object $imageFile): MasterInventory
    {
        $masterInventory = MasterInventory::findOrFail($id);

        if ($imageFile) {
            // Delete old image
            if ($masterInventory->position_image) {
                Storage::disk('public')->delete($masterInventory->position_image);
            }
            $imageName = $imageFile->getClientOriginalName();
            $imagePath = $imageFile->storeAs('masterinventory', $imageName, 'public');
            $masterInventory->position_image = $imagePath;
        }

        $masterInventory->update([
            'ip_address' => $data['ip_address'],
            'username' => $data['username'],
            'dept' => $data['dept'],
            'type' => $data['type'],
            'purpose' => $data['purpose'],
            'brand' => $data['brand'],
            'os' => $data['os'],
            'description' => $data['description'],
        ]);

        $this->syncHardwares($masterInventory, $data['hardwares'] ?? []);
        $this->syncSoftwares($masterInventory, $data['softwares'] ?? []);

        return $masterInventory;
    }

    /**
     * Delete master inventory and related details.
     */
    public function deleteInventory(int $id): void
    {
        $masterInventory = MasterInventory::findOrFail($id);

        // Delete related details
        DetailHardware::where('master_inventory_id', $id)->delete();
        DetailSoftware::where('master_inventory_id', $id)->delete();

        // Image cleanup
        if ($masterInventory->position_image) {
            Storage::disk('public')->delete($masterInventory->position_image);
        }

        $masterInventory->delete();
    }

    /**
     * Add hardware type.
     */
    public function addHardwareType(string $name): HardwareTypeInventory
    {
        return HardwareTypeInventory::create(['name' => $name]);
    }

    /**
     * Add software type.
     */
    public function addSoftwareType(string $name): SoftwareTypeInventory
    {
        return SoftwareTypeInventory::create(['name' => $name]);
    }

    /**
     * Delete inventory type (hardware/software).
     */
    public function deleteType(int $id, string $type): bool
    {
        $model = ($type === 'hardware') ? HardwareTypeInventory::find($id) : SoftwareTypeInventory::find($id);

        if ($model) {
            $model->delete();

            return true;
        }

        return false;
    }

    /**
     * Helper to process hardware creation.
     */
    private function processHardwares(MasterInventory $masterInventory, array $hardwares): void
    {
        foreach ($hardwares as $hardware) {
            if (! empty($hardware['brand']) && ! empty($hardware['hardware_name'])) {
                $masterInventory->hardwares()->create([
                    'hardware_id' => $hardware['type'],
                    'brand' => $hardware['brand'],
                    'hardware_name' => $hardware['hardware_name'],
                    'remark' => $hardware['remark'] ?? null,
                ]);
            }
        }
    }

    /**
     * Helper to sync hardwares during update.
     */
    private function syncHardwares(MasterInventory $masterInventory, array $hardwares): void
    {
        $existingHardwareIds = $masterInventory->hardwares->pluck('id')->toArray();
        $newHardwareIds = [];

        foreach ($hardwares as $hardware) {
            if (! empty($hardware['brand']) && ! empty($hardware['hardware_name'])) {
                $existing = $masterInventory->hardwares()
                    ->where('hardware_name', $hardware['hardware_name'])
                    ->where('brand', $hardware['brand'])
                    ->first();

                if ($existing) {
                    $existing->update([
                        'hardware_id' => $hardware['type'],
                        'brand' => $hardware['brand'],
                        'hardware_name' => $hardware['hardware_name'],
                        'remark' => $hardware['remark'] ?? null,
                    ]);
                    $newHardwareIds[] = $existing->id;
                } else {
                    $new = $masterInventory->hardwares()->create([
                        'hardware_id' => $hardware['type'],
                        'brand' => $hardware['brand'],
                        'hardware_name' => $hardware['hardware_name'],
                        'remark' => $hardware['remark'] ?? null,
                    ]);
                    $newHardwareIds[] = $new->id;
                }
            }
        }

        $toBeDeleted = array_diff($existingHardwareIds, $newHardwareIds);
        if (! empty($toBeDeleted)) {
            $masterInventory->hardwares()->whereIn('id', $toBeDeleted)->delete();
        }
    }

    /**
     * Helper to process software creation.
     */
    private function processSoftwares(MasterInventory $masterInventory, array $softwares): void
    {
        foreach ($softwares as $software) {
            if (! empty($software['software_brand']) && ! empty($software['software_name'])) {
                $masterInventory->softwares()->create([
                    'software_id' => $software['type'],
                    'software_brand' => $software['software_brand'],
                    'license' => $software['license'],
                    'software_name' => $software['software_name'],
                    'remark' => $software['remark'] ?? null,
                ]);
            }
        }
    }

    /**
     * Helper to sync softwares during update.
     */
    private function syncSoftwares(MasterInventory $masterInventory, array $softwares): void
    {
        $existingSoftwareIds = $masterInventory->softwares->pluck('id')->toArray();
        $newSoftwareIds = [];

        foreach ($softwares as $software) {
            if (! empty($software['software_name']) && ! empty($software['license'])) {
                $existing = $masterInventory->softwares()
                    ->where('software_name', $software['software_name'])
                    ->where('license', $software['license'])
                    ->first();

                if ($existing) {
                    $existing->update([
                        'software_id' => $software['type'],
                        'software_brand' => $software['software_brand'],
                        'license' => $software['license'],
                        'software_name' => $software['software_name'],
                        'remark' => $software['remark'] ?? null,
                    ]);
                    $newSoftwareIds[] = $existing->id;
                } else {
                    $new = $masterInventory->softwares()->create([
                        'software_id' => $software['type'],
                        'software_brand' => $software['software_brand'],
                        'license' => $software['license'],
                        'software_name' => $software['software_name'],
                        'remark' => $software['remark'] ?? null,
                    ]);
                    $newSoftwareIds[] = $new->id;
                }
            }
        }

        $toBeDeleted = array_diff($existingSoftwareIds, $newSoftwareIds);
        if (! empty($toBeDeleted)) {
            $masterInventory->softwares()->whereIn('id', $toBeDeleted)->delete();
        }
    }
}
