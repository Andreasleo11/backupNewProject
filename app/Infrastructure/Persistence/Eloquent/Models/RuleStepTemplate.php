<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RuleStepTemplate extends Model
{
    protected $table = 'approvals_rule_step_templates';

    protected $fillable = ['rule_template_id', 'sequence', 'approver_type', 'approver_id', 'final', 'parallel_group'];

    protected $casts = ['final' => 'boolean', 'parallel_group' => 'boolean'];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(RuleTemplate::class, 'rule_template_id');
    }
}
