<?php

namespace App\Domains\Operations\Actions;

use App\Infrastructure\Persistence\Eloquent\Models\DeliveryNote;
use App\Models\ApprovalFlow;
use App\Support\ApprovalFlowResolver;
use Illuminate\Support\Facades\DB;

class UpdateDeliveryNote
{
    public function execute(DeliveryNote $note, array $data, array $destinations, bool $isDraft): DeliveryNote
    {
        return DB::transaction(function () use ($note, $data, $destinations, $isDraft) {

            $totalCost = $this->calculateTotalCost($destinations);

            $context = array_merge($data, [
                'total_cost' => $totalCost,
                'is_design' => false,
                'dept_id' => null,
            ]);
            $flowSlug = ApprovalFlowResolver::for($context);
            $approvalFlow = ApprovalFlow::where('slug', $flowSlug)->first();

            $note->update(array_merge($data, [
                'status' => $isDraft ? 'draft' : 'submitted',
                'approval_flow_id' => $approvalFlow ? $approvalFlow->id : $note->approval_flow_id,
            ]));

            // Prevent syncing complexity by recreating destinations (existing approach in Livewire)
            $note->destinations()->each(function ($dest) {
                $dest->deliveryOrders()->delete();
                $dest->delete();
            });

            foreach ($destinations as $dest) {
                $destination = $note->destinations()->create([
                    'destination' => $dest['destination'] ?? null,
                    'remarks' => $dest['remarks'] ?? null,
                    'driver_cost' => $dest['driver_cost'] ?? 0,
                    'kenek_cost' => $dest['kenek_cost'] ?? 0,
                    'balikan_cost' => $dest['balikan_cost'] ?? 0,
                    'driver_cost_currency' => $dest['driver_cost_currency'] ?? 'IDR',
                    'kenek_cost_currency' => $dest['kenek_cost_currency'] ?? 'IDR',
                    'balikan_cost_currency' => $dest['balikan_cost_currency'] ?? 'IDR',
                ]);

                if (! empty($dest['delivery_order_numbers']) && is_array($dest['delivery_order_numbers'])) {
                    foreach ($dest['delivery_order_numbers'] as $doNumber) {
                        if (! empty(trim($doNumber))) {
                            $destination->deliveryOrders()->create([
                                'delivery_order_number' => trim($doNumber),
                            ]);
                        }
                    }
                }
            }

            return $note;
        });
    }

    private function calculateTotalCost(array $destinations): float
    {
        $total = 0;
        foreach ($destinations as $dest) {
            $total += (float) ($dest['driver_cost'] ?? 0);
            $total += (float) ($dest['kenek_cost'] ?? 0);
            $total += (float) ($dest['balikan_cost'] ?? 0);
        }

        return $total;
    }
}
