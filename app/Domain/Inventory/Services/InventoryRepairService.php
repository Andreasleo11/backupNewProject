<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Services;

use App\Models\InventoryRepairHistory;
use App\Models\MasterInventory;

final class InventoryRepairService
{
    /**
     * Create a new repair history record.
     */
    public function createRepair(array $data): InventoryRepairHistory
    {
        $repairData = [
            'master_id' => $data['master_id'],
            'request_name' => $data['requestName'],
            'type' => $data['type'],
            'action' => $data['action'],
            'old_part' => $data['oldPart'] ?? null,
            'remark' => $data['remark'] ?? null,
        ];

        if ($data['action'] === 'replacement') {
            $repairData['item_type'] = $data['itemType'];
            $repairData['item_brand'] = $data['itemBrand'];
            $repairData['item_name'] = $data['itemName'];
        } elseif ($data['action'] === 'installation') {
            $repairData['item_type'] = $data['itemTypeInstallation'];
            $repairData['item_brand'] = $data['itemBrandInstallation'];
            $repairData['item_name'] = $data['itemNameInstallation'];
        }

        return InventoryRepairHistory::create($repairData);
    }

    /**
     * Update history and apply changes to master inventory.
     */
    public function applyRepairHistory(int $repairHistoryId): void
    {
        $repairHistory = InventoryRepairHistory::findOrFail($repairHistoryId);
        $inventory = MasterInventory::with(['hardwares', 'softwares'])->findOrFail($repairHistory->master_id);

        $repairHistory->update(['action_date' => now()]);

        if ($repairHistory->action === 'replacement') {
            $this->handleReplacement($repairHistory, $inventory);
        } elseif ($repairHistory->action === 'installation') {
            $this->handleInstallation($repairHistory, $inventory);
        }
    }

    /**
     * Handle replacement logic.
     */
    private function handleReplacement(InventoryRepairHistory $repairHistory, MasterInventory $inventory): void
    {
        if ($repairHistory->type === 'hardware') {
            $hardwareDetail = $inventory->hardwares->firstWhere('hardware_name', $repairHistory->old_part);
            if ($hardwareDetail) {
                $hardwareDetail->update([
                    'hardware_name' => $repairHistory->item_name,
                    'brand' => $repairHistory->item_brand,
                    'remark' => $repairHistory->remark,
                ]);
            }
        } elseif ($repairHistory->type === 'software') {
            $softwareDetail = $inventory->softwares->firstWhere('software_name', $repairHistory->old_part);
            if ($softwareDetail) {
                $softwareDetail->update([
                    'software_name' => $repairHistory->item_name,
                    'software_brand' => $repairHistory->item_brand,
                    'remark' => $repairHistory->remark,
                ]);
            }
        }
    }

    /**
     * Handle installation logic.
     */
    private function handleInstallation(InventoryRepairHistory $repairHistory, MasterInventory $inventory): void
    {
        if ($repairHistory->type === 'hardware') {
            $inventory->hardwares()->create([
                'master_inventory_id' => $repairHistory->master_id,
                'hardware_id' => $repairHistory->item_type,
                'hardware_name' => $repairHistory->item_name,
                'brand' => $repairHistory->item_brand,
                'remark' => $repairHistory->remark,
            ]);
        } elseif ($repairHistory->type === 'software') {
            $inventory->softwares()->create([
                'master_inventory_id' => $repairHistory->master_id,
                'software_name' => $repairHistory->item_name,
                'software_brand' => $repairHistory->item_brand,
                'software_id' => $repairHistory->item_type,
                'remark' => $repairHistory->remark,
                'license' => 'Not License',
            ]);
        }
    }
}
