<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\JobProgress;
use Illuminate\Http\JsonResponse;

class JobProgressController extends Controller
{
    public function show(int $id): JsonResponse
    {
        $jobProgress = JobProgress::findOrFail($id);

        // Ensure user can only access their own job progress
        if ($jobProgress->user_id !== auth()->id()) {
            abort(403, 'Access denied');
        }

        // Log polling requests for debugging
        \Log::debug('JobProgress polling request', [
            'jobProgressId' => $id,
            'userId' => auth()->id(),
            'status' => $jobProgress->status,
            'progress' => $jobProgress->progress_percentage,
            'currentTask' => $jobProgress->current_task,
        ]);

        return response()->json([
            'id' => $jobProgress->id,
            'status' => $jobProgress->status,
            'progress_percentage' => $jobProgress->progress_percentage,
            'current_task' => $jobProgress->current_task,
            'results' => $jobProgress->results,
            'error_message' => $jobProgress->error_message,
            'started_at' => $jobProgress->started_at?->toISOString(),
            'completed_at' => $jobProgress->completed_at?->toISOString(),
            'estimated_time_remaining' => $jobProgress->getEstimatedTimeRemaining(),
        ]);
    }
}