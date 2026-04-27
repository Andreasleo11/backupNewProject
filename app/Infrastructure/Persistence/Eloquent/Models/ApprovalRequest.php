<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalRequest extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * Required because this model is in Infrastructure namespace
     * instead of the default App\Models namespace.
     *
     * @see https://laravel.com/docs/eloquent-factories#factory-relationships
     */
    protected static function newFactory()
    {
        return \Database\Factories\Infrastructure\Persistence\Eloquent\Models\ApprovalRequestFactory::new();
    }

    protected $fillable = [
        'status', 'rule_template_id', 'rule_template_version_id', 'current_step', 'submitted_by', 'submitted_at', 'meta',
    ];

    protected $casts = ['meta' => 'array', 'submitted_at' => 'datetime'];

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(RuleTemplate::class, 'rule_template_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class)->orderBy('sequence');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ApprovalAction::class)->latest();
    }

    /**
     * Centralized visibility scope for any user.
     */
    public function scopeForUser($query, User $user): void
    {
        (new \App\Infrastructure\Approval\Services\ApprovalVisibilityScoper)->apply($query, $user);
    }
}
