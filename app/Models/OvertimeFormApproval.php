<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimeFormApproval extends Model
{
    use HasFactory;
    protected $fillable = ['flow_step_id', 'status', 'approver_id', 'signed_at', 'signature_path'];
    protected $casts = [
        'signed_at' => 'datetime',   // ← add this
    ];

    public function form()
    {
        return $this->belongsTo(HeaderFormOvertime::class, 'overtime_form_id', 'id');
    }

    public function step()
    {
        return $this->belongsTo(ApprovalFlowStep::class, 'flow_step_id');
    }

    public function approver()
    {
        return $this->hasOne(User::class, 'id', 'approver_id');
    }
}
