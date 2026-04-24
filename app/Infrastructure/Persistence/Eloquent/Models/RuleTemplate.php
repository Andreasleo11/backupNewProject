<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Infrastructure\Support\Traits\Versionable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RuleTemplate extends Model
{
    use SoftDeletes, Versionable;

    protected $table = 'approvals_rule_templates';

    protected $fillable = [
        'model_type', 'code', 'name', 'active', 'priority', 'match_expr',
        'version_uuid', 'version_number', 'is_current', 'parent_version_id',
        'version_notes', 'created_by',
    ];

    protected $casts = [
        'match_expr' => 'array',
        'active' => 'boolean',
        'is_current' => 'boolean',
    ];

    protected $dates = ['deleted_at'];

    protected function getVersionedFields(): array
    {
        return ['model_type', 'code', 'name', 'active', 'priority', 'match_expr'];
    }

    public function steps(): HasMany
    {
        return $this->hasMany(RuleStepTemplate::class, 'rule_template_id')->orderBy('sequence');
    }

    protected function cloneRelatedToVersion($newVersion): void
    {
        foreach ($this->steps as $step) {
            $newVersion->steps()->create([
                'sequence' => $step->sequence,
                'approver_type' => $step->approver_type,
                'approver_id' => $step->approver_id,
                'final' => $step->final,
                'parallel_group' => $step->parallel_group,
            ]);
        }
    }

    public function approvalRequests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class, 'rule_template_version_id');
    }
}
