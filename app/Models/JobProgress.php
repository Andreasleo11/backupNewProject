<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'job_type',
        'user_id',
        'status',
        'progress_percentage',
        'current_task',
        'results',
        'error_message',
        'started_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'results' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'progress_percentage' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['completed', 'failed', 'cancelled']);
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isRunning(): bool
    {
        return $this->status === 'processing';
    }

    public function getEstimatedTimeRemaining(): ?int
    {
        if (!$this->isRunning() || $this->progress_percentage <= 0) {
            return null;
        }

        $elapsed = $this->started_at->diffInSeconds(now());
        $totalEstimated = ($elapsed / $this->progress_percentage) * 100;

        return (int) ($totalEstimated - $elapsed);
    }
}