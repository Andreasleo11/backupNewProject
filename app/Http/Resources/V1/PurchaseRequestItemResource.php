<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequestItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_name' => $this->item_name,
            'quantity' => $this->quantity,
            'uom' => $this->uom,
            'purpose' => $this->purpose,
            'price' => $this->price,
            'currency' => $this->currency,
            'is_approved_by_head' => (bool) $this->is_approve_by_head,
        ];
    }
}
