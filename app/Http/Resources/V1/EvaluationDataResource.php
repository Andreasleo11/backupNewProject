<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationDataResource extends JsonResource
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
            'employee' => [
                'nik' => $this->nik,
                'name' => $this->karyawan?->name,
                'status' => $this->karyawan?->status,
            ],
            'department' => [
                'code' => $this->dept,
                'name' => $this->department?->name,
            ],
            'month' => $this->Month?->format('Y-m'),
            'scores' => [
                'kemampuan_kerja' => $this->kemampuan_kerja,
                'kecerdasan_kerja' => $this->kecerdasan_kerja,
                'qualitas_kerja' => $this->qualitas_kerja,
                'disiplin_kerja' => $this->disiplin_kerja,
                'kepatuhan_kerja' => $this->kepatuhan_kerja,
                'lembur' => $this->lembur,
                'efektifitas_kerja' => $this->efektifitas_kerja,
                'relawan' => $this->relawan,
                'integritas' => $this->integritas,
            ],
            'violations' => [
                'alpha' => $this->Alpha,
                'telat' => $this->Telat,
                'sakit' => $this->Sakit,
                'izin' => $this->Izin,
            ],
            'total' => $this->total,
            'approvals' => [
                'dept_head' => $this->depthead,
                'general_manager' => $this->generalmanager,
            ],
            'is_locked' => (bool) $this->is_lock,
            'remark' => $this->remark,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
