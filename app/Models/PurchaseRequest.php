<?php

namespace App\Models;

use App\Domain\Approval\Contracts\Approvable;
use App\Enums\ToDepartment;
use App\Infrastructure\Approval\Concerns\HasApproval;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequest extends Model implements Approvable
{
    use HasApproval, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'user_id_create',
        'date_pr',
        'date_required',
        'remark',
        'to_department',
        'autograph_1',
        'autograph_2',
        'autograph_3',
        'autograph_4',
        'autograph_5',
        'autograph_6',
        'autograph_7',
        'autograph_user_1',
        'autograph_user_2',
        'autograph_user_3',
        'autograph_user_4',
        'autograph_user_5',
        'autograph_user_6',
        'autograph_user_7',
        'attachment_pr',
        'status',
        'pr_no',
        'supplier',
        'description',
        'approved_at',
        'updated_at',
        'pic',
        'type',
        'from_department',
        'is_import',
        'is_cancel',
        'po_number',
        'doc_num',
        'branch',
        'workflow_status',
        'workflow_step',
    ];

    protected $casts = [
        'to_department' => ToDepartment::class,
    ];

    public function items()
    {
        // sesuaikan, kalau sudah pakai PurchaseRequestItem ganti di sini
        return $this->hasMany(DetailPurchaseRequest::class);
    }

    public function fromDepartment()
    {
        return $this->belongsTo(Department::class, 'from_department', 'name');
        // atau 'from_department_id' kalau sudah dinormalisasi
    }

    public function signatures()
    {
        return $this->hasMany(PurchaseRequestSignature::class);
    }

    /**
     * Get all signatures, merging the new relationship and legacy columns.
     * Useful during the transition period.
     */
    public function getAllSignaturesAttribute(): array
    {
        $modern = $this->signatures->map(fn($s) => [
            'step_code' => $s->step_code,
            'user' => $s->signed_by_user_id,
            'image' => $s->image_path,
            'at' => $s->signed_at,
            'source' => 'modern'
        ])->toArray();

        $legacy = [];
        for ($i = 1; $i <= 7; $i++) {
            $col = "autograph_{$i}";
            $userCol = "autograph_user_{$i}";
            if ($this->$col) {
                $legacy[] = [
                    'step_code' => "SLOT_{$i}",
                    'user' => $this->$userCol,
                    'image' => $this->$col,
                    'at' => $this->updated_at,
                    'source' => 'legacy'
                ];
            }
        }

        return array_merge($modern, $legacy);
    }

    public function itemDetail()
    {
        return $this->hasMany(DetailPurchaseRequest::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_id_create');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'doc_id', 'doc_num');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 4);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 3);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 5);
    }
}
