<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalFlowStep extends Model
{
    use HasFactory;

    protected $fillable = ['approval_flow_id', 'step_order', 'role_slug', 'mandatory'];

    public function flow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'approval_flow_id');
    }

    public function approvals()
    {
        return $this->hasMany(OvertimeFormApproval::class, 'flow_step_id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            // default to last position if not set
            if ($model->step_order === null) {
                $model->step_order = $model->flow->steps()->max('step_order') + 1;
            }
        });
    }
}
