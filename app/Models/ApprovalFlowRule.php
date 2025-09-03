<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalFlowRule extends Model
{
    use HasFactory;

    protected $fillable = ["department_id", "branch", "is_design", "approval_flow_id", "priority"];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function flow()
    {
        return $this->belongsTo(ApprovalFlow::class, "approval_flow_id");
    }
}
