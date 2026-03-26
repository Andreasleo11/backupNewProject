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
            'pr_no' => $this->pr_no,
            'branch' => $this->branch,

            // Workflow information (new)
            'workflow' => [
                'status' => $this->workflow_status,
                'step' => $this->workflow_step,
                'current_step_number' => $this->approvalRequest?->current_step,
                'total_steps' => $this->approvalRequest?->steps()->count(),
                'progress_percent' => $this->approvalRequest && $this->approvalRequest->steps()->count() > 0
                    ? round(($this->approvalRequest->current_step / $this->approvalRequest->steps()->count()) * 100)
                    : 0,
            ],

            // Request details
            'request_date' => $this->created_at?->format('Y-m-d'),
            'expected_date' => $this->expected_date,
            'date_pr' => $this->date_pr?->format('Y-m-d'),
            'date_required' => $this->date_required?->format('Y-m-d'),
            'supplier' => $this->supplier,
            'pic' => $this->pic,
            'remark' => $this->remark,

            // Departments
            'from_department' => [
                'id' => $this->fromDepartment?->id,
                'name' => $this->fromDepartment?->name ?? $this->from_department,
                'dept_no' => $this->fromDepartment?->dept_no,
            ],
            'to_department' => $this->to_department,

            // Legacy status (backward compatibility)
            'status' => $this->status,
            'status_text' => $this->getStatusText(),

            // Related data
            'items' => PurchaseRequestItemResource::collection($this->whenLoaded('items')),
            'created_by' => new UserResource($this->whenLoaded('createdBy')),

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
        ];
    }
}
