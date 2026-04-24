<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RuleTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'approvals_rule_templates';

    protected $fillable = ['model_type', 'code', 'name', 'active', 'priority', 'match_expr'];

    protected $casts = ['match_expr' => 'array', 'active' => 'boolean'];

    protected $dates = ['deleted_at'];

    public function steps(): HasMany
    {
        return $this->hasMany(RuleStepTemplate::class, 'rule_template_id')->orderBy('sequence');
    }

    public function approvalRequests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class, 'rule_template_id');
    }
}
