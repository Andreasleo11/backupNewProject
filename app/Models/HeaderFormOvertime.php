<?php

namespace App\Models;

use App\Notifications\FormOvertimeNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HeaderFormOvertime extends Model
{
    use SoftDeletes;

    protected $table = 'header_form_overtime';

    protected $fillable = [
        'user_id',
        'dept_id',
        'branch',
        'is_approve',
        'status',
        'is_design',
        'is_export',
        'description',
        'is_planned',
        'approval_flow_id',
        'is_after_hour'
    ];

    public function user()
    {
        return $this->hasone(User::class, 'id', 'user_id');
    }

    public function department()
    {
        return $this->hasone(Department::class, 'id', 'dept_id');
    }

    public function details()
    {
        return $this->hasMany(DetailFormOvertime::class, 'header_id', 'id');
    }

    public function flow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'approval_flow_id', 'id');
    }

    public function approvals()
    {
        return $this->hasMany(OvertimeFormApproval::class, 'overtime_form_id', 'id');
    }

    public function currentStep()
    {
        return $this->flow
            ? $this->flow->steps()
            ->whereNotIn(
                'id',
                $this->approvals()
                    ->where('status', 'approved')
                    ->pluck('flow_step_id')
            )
            ->orderBy('step_order')
            ->first()
            : null;
    }

    public function nextStep(): ?ApprovalFlowStep
    {
        if (! $this->flow) {
            return null;               // no template attached
        }

        // Which step order number is the last that got approved?
        $lastApprovedOrder = $this->approvals()
            ->where('status', 'approved')
            ->with('step')             // eager load so we can read step_order
            ->get()
            ->pluck('step.step_order')
            ->max();                   // null if nothing approved yet

        // Return the first step with a higher order number
        return $this->flow->steps()
            ->when(
                $lastApprovedOrder !== null,
                fn($q) => $q->where('step_order', '>', $lastApprovedOrder)
            )
            ->orderBy('step_order')
            ->first();                 // null means we’re at the end
    }

    public function sendNotification($report)
    {
        $director = User::whereHas('specification', function ($query) {
            $query->where('name', 'DIRECTOR');
        })->first();

        $verificator = User::whereHas('specification', function ($query) {
            $query->where('name', 'VERIFICATOR');
        })->first();

        $supervisor = User::whereHas('specification', function ($query) {
            $query->where('name', 'SUPERVISOR');
        })->first();

        $deptHead = User::where('is_head', 1)->where('department_id', $report->dept_id)->first();

        switch ($report->status) {
            // Send to Dept Head
            case 'waiting-dept-head':
                if ($report->department->name === 'STORE') {
                    $user = User::where('is_head', 1)->whereHas('department', function ($query) {
                        $query->where('name', 'LOGISTIC');
                    })->first();
                } else {
                    $user = $deptHead;
                }
                break;
            // Send to Supervisor
            case 'waiting-supervisor':
                $user = $supervisor;
                break;
            // Send to GM
            case 'waiting-gm':
                if (strtoupper($this->branch) === 'KARAWANG') {
                    $user = User::where('email', 'pawarid_pannin@daijo.co.id')->first();
                } else {
                    $user = User::where('email', 'albert@daijo.co.id')->first();
                }
                break;
            // Send to Verificator
            case 'waiting-verificator':
                $user = $verificator;
                break;
            // Send to Director
            case 'waiting-director':
                $user = $director;
                break;

            default:
                return redirect()->back()->with('error', 'Failed send notification!');
                break;
        }

        $formattedCreateDate = \Carbon\Carbon::parse($report->created_at)->format('d-m-Y');
        $cc = [$report->user->email];
        $status = ucwords(str_replace('-', ' ', $report->status));

        if ($report->is_approve === 1 || $report->is_approve === 0) {
            $user = $report->user;
            array_push($cc, $verificator);
        }

        $details = [
            'greeting' => 'Form Overtime Notification',
            'body' => "We waiting for your sign for this report : <br>
                    - Report ID : $report->id <br>
                    - Department From : {$report->department->name} ({$report->department->dept_no}) <br>
                    - Create Date : {$formattedCreateDate} <br>
                    - Created By : {$report->user->name} <br>
                    - Status : {$status} <br> 
                        ",
            'cc' => $cc,
            'actionText' => 'Click to see the detail',
            'actionURL' => env('APP_URL', 'http://116.254.114.93:2420/') . 'formovertime/detail/' . $report->id,
        ];

        $user->notify(new FormOvertimeNotification($report, $details));
    }
}
