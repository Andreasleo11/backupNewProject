<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RuleStepTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'approvals_rule_step_templates';

    protected $fillable = ['rule_template_id', 'sequence', 'approver_type', 'approver_id', 'final', 'parallel_group'];

    protected $casts = ['final' => 'boolean', 'parallel_group' => 'boolean'];

    protected $dates = ['deleted_at'];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(RuleTemplate::class, 'rule_template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approver_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'approver_id');
    }
}
