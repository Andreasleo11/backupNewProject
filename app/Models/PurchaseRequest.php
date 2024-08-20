<?php

namespace App\Models;

use App\Console\Commands\SendPREmailNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Bus;

class PurchaseRequest extends Model
{
    use HasFactory, SoftDeletes;

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
        'branch'
    ];


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

    protected static function booted()
    {
        static::created(function ($purchaseRequest) {
            // Map department names to codes
            $departmentCodes = [
                'Accounting' => 'ACU',
                'Assembly' => 'ASM',
                'Business' => 'BUS',
                'Computer' => 'CP',
                'HRD' => 'HRD',
                'Personnel' => 'HRD',
                'Maintenance' => 'MT',
                'Maintenance Moulding' => 'MTM',
                'Moulding' => 'MLD',
                'Plastic Injection' => 'PI',
                'PPIC' => 'PIC',
                'Purchasing' => 'PUR',
                'QA' => 'QA',
                'QC' => 'QC',
                'Second Process' => 'SPC',
                'Store' => 'STR',
                'Logistic' => 'LOG',
                'PE' => 'PE'
            ];


             // Map branches to area codes
            $branchCodes = [
                'JAKARTA' => 'JKT',
                'KARAWANG' => 'KRW'
            ];

            // Get the date portion
            $date = $purchaseRequest->created_at->format('ymd'); // Day-Month-Year format (e.g., '240819' for August 24, 2019)

            // Get the department code
            $department = $purchaseRequest->to_department;
            $branchCode = $departmentCodes[$department] ?? 'UNK'; // Use 'UNK' for unknown departments

             // Get the area code from the branch
            $branch = $purchaseRequest->branch;
            $areaCode = $branchCodes[$branch] ?? 'UNK'; // Use 'UNK' for unknown branches

            // Fetch the last record's doc_num for the current date and branch code
            $latest = static::where('doc_num', 'like', "%/PR/{$areaCode}/{$date}/%")
                ->orderBy('id', 'desc')
                ->first();

            if ($latest) {
                // Extract the increment part from the latest doc_num
                $lastIncrement = (int) substr($latest->doc_num, -3); // Assuming the increment is always 3 digits
            } else {
                $lastIncrement = 0; // No records found for today
            }

            // Calculate the next increment number
            $increment = str_pad($lastIncrement + 1, 3, '0', STR_PAD_LEFT);

            // Build the docNum
            $docNum = "{$branchCode}/PR/{$areaCode}/{$date}/{$increment}";

            $prNo = substr($department, 0, 4) . '-' . $purchaseRequest->id;

            $purchaseRequest->update(['pr_no' => $prNo, 'doc_num' => $docNum]);

            // Dispatch the job to send the email notification
            Bus::dispatch(new SendPREmailNotification($purchaseRequest));
        });

        static::updating(function ($purchaseRequest) {
            if ($purchaseRequest->isDirty('status')) {
                $originalStatus = $purchaseRequest->getOriginal('status');
                $newStatus = $purchaseRequest->status;

                // Check if the status has changed
                if ($originalStatus !== $newStatus) {
                    // Dispatch the job to send the email notification
                    Bus::dispatch(new SendPREmailNotification($purchaseRequest));
                }
            }
        });
    }
}
