<?php

namespace App\Models;

use App\Notifications\MonthlyBudgetSummaryReportCreated;
use App\Notifications\MonthlyBudgetSummaryReportUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Notification;

class MonthlyBudgetSummaryReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'report_date',
        'creator_id',
        'created_autograph',
        'is_known_autograph',
        'approved_autograph',
        'doc_num',
        'is_reject',
        'reject_reason',
        'is_moulding'
    ];

    // Relations
    public function details()
    {
        return $this->hasMany(MonthlyBudgetReportSummaryDetail::class, 'header_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'doc_id', 'doc_num');
    }

    // Queries
    public function scopeApproved($query)
    {
        return $query->where('status', 5);
    }

    public function scopeWaitingDirector($query)
    {
        return $query->where('status', 4);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 5);
    }

    // Other
    protected static function boot()
    {
        parent::boot();

        static::created(function ($report) {
            $prefix = 'MBSR';
            if ($report->is_moulding) {
                $prefix = $prefix . '/MOULD';
            }
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
        $details = $this->prepareNotificationdetails();
        $this->notifyUsers($details, $event);
    }

    private function prepareNotificationDetails()
    {
        $status = $this->getStatusText($this->status);

        $commonDetails = [
            'greeting' => 'Monthly Budget Summary Report Notification',
            'actionText' => 'Check Now',
            'actionURL' => route('monthly.budget.summary.report.show', $this->id),
        ];

        $reportDate = \Carbon\Carbon::parse($this->report_date)->format('F Y');

        $commonDetails['body'] = "Notification for Monthly Budget Summary Report: <br>
            - Document Number : $this->doc_num <br>
            - Month : $reportDate <br>
            - Status : $status";

        return $commonDetails;
    }

    private function getStatusText($status)
    {
        switch ($status) {
            case 1:
                return 'Waiting Creator';
            case 2:
                return 'Waiting GM';
            case 3:
                return 'Waiting Dept Head';
            case 4:
                return 'Waiting Director';
            case 5:
                return 'Approved';
            case 6:
                return 'Rejected';
            default:
                return 'Unknown';
        }
    }

    private function notifyUsers($details, $event)
    {
        $creator = $this->user; // Convert to array
        $users = [];
        array_push($users, $creator);

        if ($event === 'created') {
            // $creator[0]->notify(new MonthlyBudgetSummaryReportCreated($this, $details));
        } elseif ($event === 'updated') {
            if ($this->status == 2) {
                $gm = User::where('is_gm', 1)->first();
                array_push($users, $gm);
            } elseif ($this->status == 3) {
                $mouldingHead = User::whereHas('department', function ($query) {
                    $query->where('name', 'MOULDING');
                })->whereHas('specification', function ($query) {
                    $query->where('name', 'DESIGN');
                })->where('is_head', 1)->first();
                array_push($users, $mouldingHead);
            } elseif ($this->status == 4) {
                $director = User::with('department')->whereHas('department', function ($query) {
                    $query->where('name', 'DIRECTOR');
                })->first();
                array_push($users, $director);
            }

            Notification::send($users, new MonthlyBudgetSummaryReportUpdated($this, $details));
        }
    }
}
