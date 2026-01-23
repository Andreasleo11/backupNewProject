<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'is_head' => (bool) $this->is_head,
            'is_gm' => (bool) $this->is_gm,
            'department' => [
                'id' => $this->department?->id,
                'name' => $this->department?->name,
                'dept_no' => $this->department?->dept_no,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
