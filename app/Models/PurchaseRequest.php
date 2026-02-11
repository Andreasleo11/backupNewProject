<?php

namespace App\Models;

use App\Domain\Approval\Contracts\Approvable;
use App\Enums\ToDepartment;
use App\Infrastructure\Approval\Concerns\HasApproval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseRequest extends Model implements Approvable
{
    use HasApproval, HasFactory, LogsActivity, SoftDeletes;

    /**
     * Get combined activities from PR and its Items.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCombinedActivitiesAttribute()
    {
        // 1. Get PR Logs
        $prLogs = $this->activities;

        // 2. Get Item Logs
        $itemIds = $this->items()->pluck('id');
        
        $itemLogs = \Spatie\Activitylog\Models\Activity::where('subject_type', \App\Models\DetailPurchaseRequest::class)
            ->whereIn('subject_id', $itemIds)
            ->with('causer') 
            ->get();

        // 3. Get File Logs (New)
        // Assuming files are linked via doc_num. We need to find File IDs first.
        // Since there is no direct relationship defined yet (files() accessor might exist but using raw query for safety)
        $fileIds = \App\Models\File::where('doc_id', $this->doc_num)->pluck('id');

        $fileLogs = \Spatie\Activitylog\Models\Activity::where('subject_type', \App\Models\File::class)
            ->whereIn('subject_id', $fileIds)
            ->with('causer')
            ->get();

        // 4. Merge & Sort desc
        return $prLogs->concat($itemLogs)->concat($fileLogs)->sortByDesc('created_at');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

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
        'status',
        'pr_no',
        'supplier',
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
     * Get all signatures, merging modern approval steps, legacy signatures relationship, and legacy columns.
     * Useful during the transition period.
     */
    public function getAllSignaturesAttribute(): array
    {
        $signatures = collect();

        // 1. Primary Source: Approval Steps (Modern Approval Engine)
        if ($this->approvalRequest) {
            $approvalSignatures = $this->approvalRequest->steps()
                ->whereNotNull('acted_at')
                ->whereNotNull('signature_image_path')
                ->with('actedUser') // Ensure relationship exists in ApprovalStep model or use acted_by
                ->get()
                ->map(function ($step) {
                    return [
                        'step_code' => $step->approver_label ?? 'Approver', // Use accessor with fallback
                        'user' => $step->actedUser, 
                        'name' => $step->approver_name ?? 'Unknown', // Use accessor with fallback
                        'image' => $step->signature_image_path,
                        'at' => $step->acted_at,
                        'source' => 'approval_system',
                    ];
                });
            $signatures = $signatures->merge($approvalSignatures);
        }

        // 2. Secondary Source: PurchaseRequestSignature model (Manual/Backfill)
        $modern = $this->signatures->load('user')->map(fn ($s) => [
            'step_code' => $s->step_code,
            'user' => $s->user,
            'name' => $s->user?->name ?? 'Unknown User',
            'image' => $s->image_path,
            'at' => $s->signed_at,
            'source' => 'modern',
        ]);
        $signatures = $signatures->merge($modern);

        // 3. Fallback: Legacy Signatures
        $legacy = [];
        for ($i = 1; $i <= 7; $i++) {
            $col = "autograph_{$i}";
            $userCol = "autograph_user_{$i}";
            
            if (!empty($this->$col)) {
                $userData = $this->$userCol;
                $userName = is_string($userData) ? $userData : 'Unknown';
                
                $legacy[] = [
                    'step_code' => "SLOT_{$i}",
                    'user' => null,
                    'name' => $userName,
                    'image' => $this->$col,
                    'at' => $this->updated_at,
                    'source' => 'legacy',
                ];
            }
        }
        $signatures = $signatures->merge($legacy);

        // Unique by step_code/name to avoid duplicates if systems overlap
        return $signatures->unique(function ($item) {
            return $item['step_code'] . $item['name']; 
        })->values()->toArray();
    }

    /**
     * Get all workflow steps for visualization, including pending and empty slots.
     */
    public function getWorkflowSignaturesAttribute(): array
    {
        $steps = collect();

        // 1. Primary Source: Approval Steps (Modern Approval Engine)
        if ($this->approvalRequest) {
            $approvalSteps = $this->approvalRequest->steps()
                ->orderBy('sequence')
                ->with('actedUser') 
                ->get()
                ->map(function ($step) {
                    $status = strtoupper($step->status);
                    
                    // Determine status for UI
                    $uiStatus = match ($status) {
                        'APPROVED' => 'signed',
                        'REJECTED' => 'rejected',
                        'PENDING', 'IN_PROGRESS' => 'pending',
                        default => 'pending',
                    };

                    if ($step->acted_at && $step->signature_image_path) {
                        $uiStatus = 'signed';
                    }

                    return [
                        'step_code' => $step->approver_label ?? 'Approver',
                        'user' => $step->actedUser,
                        'name' => $step->approver_name ?? 'Waiting...',
                        'image' => $step->signature_image_path,
                        'at' => $step->acted_at,
                        'status' => $uiStatus,
                        'is_current' => $this->approvalRequest->current_step == $step->sequence && $this->approvalRequest->status === 'IN_REVIEW',
                        'source' => 'approval_system',
                    ];
                });
            
            if ($approvalSteps->isNotEmpty()) {
                return $approvalSteps->toArray();
            }
        }

        // 2. Fallback: Legacy Signatures (Simulate 7 slots)
        for ($i = 1; $i <= 7; $i++) {
            $col = "autograph_{$i}";
            $userCol = "autograph_user_{$i}";
            
            $labels = [
                1 => 'Created By', 2 => 'Checked By', 3 => 'Known By', 4 => 'Approved By',
                5 => 'Approved By', 6 => 'Approved By', 7 => 'Approved By'
            ];
            
            if (!empty($this->$col)) {
                $userData = $this->$userCol;
                $userName = is_string($userData) ? $userData : 'Unknown';
                
                $steps->push([
                    'step_code' => $labels[$i] ?? "Approver {$i}",
                    'user' => null,
                    'name' => $userName,
                    'image' => $this->$col,
                    'at' => $this->updated_at,
                    'status' => 'signed',
                    'is_current' => false,
                    'source' => 'legacy',
                ]);
            } else {
                 // Only show empty slots if we don't have a modern workflow
                 // AND it's likely a legacy request (or we just show empty slots for strict legacy compat)
                 // meaningful labels for legacy slots
                 $steps->push([
                    'step_code' => $labels[$i] ?? "Approver {$i}",
                    'user' => null,
                    'name' => 'Waiting...',
                    'image' => null,
                    'at' => null,
                    'status' => 'pending', // or empty
                    'is_current' => false,
                    'source' => 'legacy',
                ]);
            }
        }

        return $steps->toArray();
    }

    public function itemDetail()
    {
        return $this->hasMany(DetailPurchaseRequest::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_id_create');
    }

    /**
     * Alias for createdBy() for better semantic clarity
     */
    public function creator()
    {
        return $this->createdBy();
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
