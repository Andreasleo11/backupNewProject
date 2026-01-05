<?php

namespace App\Models;

use App\Domain\Approval\Contracts\Approvable;
use App\Domain\Signature\Contracts\SignatureStampsApproval;
use App\Domain\Signature\Entities\UserSignature;
use App\Enums\ToDepartment;
use App\Infrastructure\Approval\Concerns\HasApproval;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use App\Notifications\PurchaseRequestCreated;
use App\Notifications\PurchaseRequestUpdated;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Notification;

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
