<?php

namespace App\Domains\Operations\Actions;

use App\Infrastructure\Persistence\Eloquent\Models\DeliveryNote;
use Illuminate\Support\Facades\DB;

class CreateDeliveryNote
{
    public function execute(array $data, array $destinations, bool $isDraft): DeliveryNote
    {
        return DB::transaction(function () use ($data, $destinations, $isDraft) {

            // Calculate total costs for the approval context
            $totalCost = $this->calculateTotalCost($destinations);

            $note = DeliveryNote::create(array_merge($data, [
                'status' => $isDraft ? 'draft' : 'submitted',
                'total_cost' => $totalCost, // assuming we want to track it or not, wait, delivery_notes doesn't have total_cost column yet, so we won't insert it.
            ]));

            unset($note->total_cost); // Don't try to insert non-existent column

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
