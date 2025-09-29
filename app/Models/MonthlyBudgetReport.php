<?php

namespace App\Models;

use App\Notifications\MonthlyBudgetReportUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class MonthlyBudgetReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'dept_no',
        'creator_id',
        'report_date',
        'created_autograph',
        'is_known_autograph',
        'approved_autograph',
        'reject_reason',
        'is_reject',
        'doc_num',
        'status',
        'is_cancel',
        'cancel_reason',
    ];

    // Relations
    public function details()
    {
        return $this->hasMany(MonthlyBudgetReportDetail::class, 'header_id');
    }

    public function department()
    {
        return $this->hasOne(Department::class, 'dept_no', 'dept_no');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    // Queries
    public function scopeApprovedByDirector($query)
    {
        return $query
            ->whereHas('department', function ($query) {
                $query->where('name', 'QA')->orWhere('name', 'QC');
            })
            ->where('status', 6);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 5);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 7);
    }

    // Other
    protected static function boot()
    {
        parent::boot();

        static::created(function ($report) {
            $prefix = 'MBR';
            $id = $report->id;
            $date = $report->created_at->format('dmY');
            $docNum = "$prefix/$id/$date";

            $report->update(['doc_num' => $docNum]);

            $report->sendNotification('created');
        });

        static::updated(function ($report) {
            if ($report->isDirty('status')) {
                $report->sendNotification('updated');
            }
        });
    }

    private function sendNotification($event)
    {
        $details = $this->prepareNotificationDetails();
        $this->notifyUsers($details, $event);
    }

    private function prepareNotificationDetails()
    {
        $status = $this->getStatusText($this->status);

        $commonDetails = [
            'greeting' => 'Monthly Budget Report Notification',
            'actionText' => 'Check Now',
            'actionURL' => route('monthly.budget.report.show', $this->id),
        ];

        $commonDetails['body'] = "Notification for Monthly Budget Report: <br>
            - Document Number : $this->doc_num <br>
            - Creator : {$this->user->name} <br>
            - Department : {$this->department->name} <br>
            - Status : $status";

        return $commonDetails;
    }

    private function getStatusText($status)
    {
        switch ($status) {
            case 1:
                return 'Waiting Creator';
            case 2:
                return 'Waiting Dept Head';
            case 3:
                return 'Waiting Head Design';
            case 4:
                return 'Waiting GM';
            case 5:
                return 'Waiting Director';
            case 6:
                return 'Approved';
            case 7:
                return 'Rejected';
            default:
                return 'Unknown';
        }
    }

    private function notifyUsers($details, $event)
    {
        if ($event == 'created') {
            // $creator[0]->notify(new MonthlyBudgetSummaryReportCreated($this, $details));
        } else {
            $creator = [$this->user]; // Convert to array
            $user = null; // Initialize $user to avoid undefined variable error
            $cc = null;

            if (
                $this->created_autograph &&
                ! $this->is_known_autograph &&
                ! $this->approved_autograph
            ) {
                if ($this->department->name === 'MOULDING') {
                    $user = User::with('department', 'specification')
                        ->whereHas('department', function ($query) {
                            $query->where('name', 'MOULDING');
                        })
                        ->where('is_head', 1)
                        ->whereHas('specification', function ($query) {
                            $query->where('name', 'design');
                        })
                        ->first();
                } elseif ($this->department->name === 'STORE') {
                    $user = User::where('is_head', 1)
                        ->whereHas('department', function ($query) {
                            $query->where('name', 'LOGISTIC');
                        })
                        ->first();
                } elseif ($this->department->name === 'PLASTIC INJECTION') {
                    $user = User::where('email', 'albert@daijo.co.id')->first();
                } else {
                    $user = User::where('department_id', $this->department->id)
                        ->where('is_head', 1)
                        ->first();
                }
            } elseif (
                $this->created_autograph &&
                $this->is_known_autograph &&
                ! $this->approved_autograph
            ) {
                if ($this->department->name === 'MOULDING') {
                    $user = User::with('department', 'specification')
                        ->whereHas('department', function ($query) {
                            $query->where('name', 'MOULDING');
                        })
                        ->where('is_head', 1)
                        ->whereHas('specification', function ($query) {
                            $query->where('name', '!=', 'design');
                        })
                        ->first();
                } elseif ($this->department->name === 'QA' || $this->department->name === 'QC') {
                    $user = User::with('specification')
                        ->whereHas('specification', function ($query) {
                            $query->where('name', 'DIRECTOR');
                        })
                        ->first();
                } else {
                    $user = User::where('email', 'albert@daijo.co.id')->first();
                }
            }

            $cc = User::where('name', 'nur')->first();
            $users = isset($user) ? array_merge($creator, [$user, $cc]) : $creator;

            // Ensure $users is not empty before sending notifications
            if (! empty($users)) {
                Notification::send($users, new MonthlyBudgetReportUpdated($this, $details));
            } else {
                // Log or handle the case where no users were found
                Log::warning(
                    'No valid users found to send the notification for MonthlyBudgetReportUpdated.',
                );
            }
        }
    }
}
