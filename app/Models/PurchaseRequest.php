<?php

namespace App\Models;

use App\Console\Commands\SendPREmailNotification;
use App\Notifications\PurchaseRequestCreated;
use App\Notifications\PurchaseRequestUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;

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

    protected static function boot()
    {
        parent::boot();

        static::created(function ($pr) {
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
            $date = $pr->created_at->format('ymd'); // Day-Month-Year format (e.g., '240819' for August 24, 2019)

            // Get the department code
            $department = $pr->to_department;
            $branchCode = $departmentCodes[$department] ?? 'UNK'; // Use 'UNK' for unknown departments

            // Get the area code from the branch
            $branch = $pr->branch;
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

            $prNo = substr($department, 0, 4) . '-' . $pr->id;

            $pr->update(['pr_no' => $prNo, 'doc_num' => $docNum]);
            $pr->sendNotification('created');
        });

        static::updated(function ($pr) {
            $statusChanged = $pr->isDirty('status');

            if ($statusChanged) {
                $pr->sendNotification('updated');
            }
        });
    }

    private function sendNotification($event)
    {
        $details = $this->prepareNotificationDetails($event);
        $this->notifyUsers($details, $event);
    }

    private function prepareNotificationDetails($event)
    {
        $status = $this->getStatusText($this->status);

        $commonDetails = [
            'greeting' => 'Purchase Request Notification',
            'actionText' => 'Check Now',
            'actionURL' => route('purchaserequest.detail', $this->id),
        ];

        if ($event == 'created' || $event == 'updated') {
            $commonDetails['body'] = "Here's the detail : <br>
                - Doc. Num : $this->doc_num <br>
                - PR No. : $this->pr_no <br>
                - Created By : {$this->createdBy->name} <br>
                - Date PR : $this->date_pr <br>
                - Date Required : $this->date_required <br>
                - PIC : $this->pic <br>
                - Remark : $this->remark <br>
                - To Department : $this->to_department <br>
                - Status : $status";
        }

        return $commonDetails;
    }

    private function getStatusText($status)
    {
        switch ($status) {
            case 1:
                return 'WAITING FOR DEPT HEAD';
            case 2:
                return 'WAITING FOR VERIFICATOR';
            case 3:
                return 'WAITING FOR DIRECTOR';
            case 4:
                return 'APPROVED';
            case 5:
                return 'REJECTED';
            case 6:
                return 'WAITING FOR PURCHASER';
            case 7:
                return 'WAITING FOR GM';
            default:
                return 'NOT DEFINED';
        }
    }

    private function notifyUsers($details, $event)
    {
        $creator = [$this->createdBy];
        if ($event == 'created') {
            // test
        } else {
            $status = $this->status;
            switch ($status) {
                case 1:
                    if ($this->from_department === 'MOULDING') {
                        if ($this->is_import === 1) {
                            $deptHead = 'fang@daijo.co.id';
                        } else {
                            // if is_import is false or null, notification will sent to fang and ong
                            $deptHead = User::where('is_head', 1)
                                ->whereHas('department', function ($query) {
                                    $query->where('name', $this->from_department);
                                })
                                ->pluck('email')
                                ->toArray();
                        }
                    } elseif ($this->from_department === 'STORE') {
                        $deptHead = User::where('is_head', 1)->whereHas('department', function ($query) {
                            $query->where('name', 'LOGISTIC');
                        })->first();
                    } else {
                        $deptHead = User::where('is_head', 1)
                            ->whereHas('department', function ($query) {
                                $query->where('name', $this->from_department);
                            })
                            ->first();
                    }
                    $user = $deptHead ? $deptHead->email : $this->createdBy->email;
                    break;
                case 7:
                    $gm = User::whereHas('department', function ($query) {
                        $query->where('name', '!=', 'MOULDING')->where('is_gm', 1);
                    })
                        ->first();
                    $user = $gm ? $gm->email : $this->createdBy->email;
                    break;
                case 6:
                    if ($this->to_department === "Computer") {
                        $purchaser = 'vicky@daijo.co.id';
                    } elseif ($this->to_department === "Purchasing") {
                        $purchaser = 'dian@daijo.co.id';
                    } elseif ($this->to_department === "Maintenance") {
                        $purchaser = 'nur@daijo.co.id';
                    } elseif ($this->to_department === "Personnel") {
                        $purchaser = 'ani_apriani@daijo.co.id';
                    } else {
                        $purchaser = $this->createdBy->email;
                    }

                    $user = $purchaser;
                    break;
                case 2:
                    $verificator = User::with('specification')
                        ->whereHas('specification', function ($query) {
                            $query->where('name', 'VERIFICATOR');
                        })
                        ->where('is_head', 1)
                        ->first();
                    $user = $verificator ? $verificator->email : $this->createdBy->email;
                    break;
                case 3:
                    $director = User::with('department')
                        ->whereHas('department', function ($query) {
                            $query->where('name', 'DIRECTOR');
                        })
                        ->first();
                    $user = $director ? $director->email : $this->createdBy->email;
                    break;
                case 4:
                    $user = $this->createdBy->email;
                    break;
                case 5:
                    $user = $this->createdBy->email;
                    break;
                default:
                    $user = $this->createdBy->email;
                    break;
            }
        }
        $users = isset($user) ? array_merge($creator, [$user]) : $creator;
        Notification::send($users, new PurchaseRequestCreated($this, $details));
    }
}
