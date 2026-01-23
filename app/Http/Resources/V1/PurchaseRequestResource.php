<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequestResource extends JsonResource
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
            'doc_num' => $this->doc_num,
            'request_date' => $this->created_at?->format('Y-m-d'),
            'expected_date' => $this->expected_date,
            'cost_reduce_idea' => $this->cost_reduce_idea,
            'status' => $this->status,
            'department' => [
                'id' => $this->fromDepartment?->id,
                'name' => $this->fromDepartment?->name,
                'dept_no' => $this->fromDepartment?->dept_no,
            ],
            'to_department' => $this->to_department,
            'items' => PurchaseRequestItemResource::collection($this->whenLoaded('items')),
            'created_by' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
