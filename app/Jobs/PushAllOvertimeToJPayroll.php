<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Overtime\Models\OvertimeForm;
use App\Domain\Overtime\Services\OvertimeJPayrollService;
use App\Models\JobProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class PushAllOvertimeToJPayroll implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600; // 1 hour timeout
    public int $tries = 1; // No automatic retries, manual intervention required

    private array $formIds;
    private int $userId;
    private int $jobProgressId;

    public function __construct(array $formIds, int $userId, int $jobProgressId)
    {
        $this->formIds = $formIds;
        $this->userId = $userId;
        $this->jobProgressId = $jobProgressId;
    }

    public function handle(): void
    {
        $progress = JobProgress::find($this->jobProgressId);
        if (!$progress) {
            Log::error('PushAllOvertimeToJPayroll: JobProgress not found', ['jobProgressId' => $this->jobProgressId]);
            return;
        }

        try {
            $progress->update([
                'status' => 'processing',
                'current_task' => 'Initializing push operation',
                'started_at' => now(),
            ]);

            Log::info('PushAllOvertimeToJPayroll: Job started', [
                'jobProgressId' => $this->jobProgressId,
                'userId' => $this->userId,
                'formCount' => count($this->formIds)
            ]);

            $service = app(OvertimeJPayrollService::class);
            $totalForms = count($this->formIds);
            $processedForms = 0;
            $successfulForms = 0;
            $failedForms = 0;
            $totalDetails = 0;
            $successfulDetails = 0;
            $failedDetails = 0;

            foreach ($this->formIds as $index => $formId) {
                if ($progress->isCancelled()) {
                    $progress->update([
                        'status' => 'cancelled',
                        'current_task' => 'Operation cancelled by user',
                        'completed_at' => now(),
                        'results' => [
                            'processed_forms' => $processedForms,
                            'successful_forms' => $successfulForms,
                            'failed_forms' => $failedForms,
                            'total_details' => $totalDetails,
                            'successful_details' => $successfulDetails,
                            'failed_details' => $failedDetails,
                            'cancelled_at_form' => $index + 1,
                        ]
                    ]);
                    return;
                }

                $form = OvertimeForm::find($formId);
                if (!$form) {
                    $failedForms++;
                    Log::warning('PushAllOvertimeToJPayroll: Form not found', ['formId' => $formId]);
                    continue;
                }

                $progressPercentage = round((($index) / $totalForms) * 100);
                $progress->update([
                    'current_task' => 'Processing form ' . $form->id . ' (' . ($index + 1) . '/' . $totalForms . ')',
                    'progress_percentage' => $progressPercentage,
                ]);

                Log::info('PushAllOvertimeToJPayroll: Progress update', [
                    'jobProgressId' => $this->jobProgressId,
                    'progress' => $progressPercentage,
                    'currentTask' => 'Processing form ' . $form->id,
                    'formIndex' => $index + 1,
                    'totalForms' => $totalForms
                ]);

                try {
                    $result = $service->pushAllDetails($formId);
                    $processedForms++;

                    if ($result['success']) {
                        $successfulForms++;
                        $successfulDetails += $result['total_success'];
                    } else {
                        $failedForms++;
                        $failedDetails += $result['total_failed'];
                        Log::warning('PushAllOvertimeToJPayroll: Failed to push form', [
                            'formId' => $formId,
                            'error' => $result['message']
                        ]);
                    }

                    $totalDetails += $result['total_success'] + $result['total_failed'];

                } catch (Throwable $e) {
                    $failedForms++;
                    Log::error('PushAllOvertimeToJPayroll: Exception while pushing form', [
                        'formId' => $formId,
                        'exception' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            $progress->update([
                'status' => 'completed',
                'current_task' => 'Operation completed',
                'progress_percentage' => 100,
                'completed_at' => now(),
                'results' => [
                    'total_forms' => $totalForms,
                    'processed_forms' => $processedForms,
                    'successful_forms' => $successfulForms,
                    'failed_forms' => $failedForms,
                    'total_details' => $totalDetails,
                    'successful_details' => $successfulDetails,
                    'failed_details' => $failedDetails,
                ]
            ]);

            Log::info('PushAllOvertimeToJPayroll: Job completed successfully', [
                'jobProgressId' => $this->jobProgressId,
                'results' => [
                    'total_forms' => $totalForms,
                    'successful_forms' => $successfulForms,
                    'failed_forms' => $failedForms,
                    'total_details' => $totalDetails,
                    'successful_details' => $successfulDetails,
                    'failed_details' => $failedDetails,
                ]
            ]);

        } catch (Throwable $e) {
            $progress->update([
                'status' => 'failed',
                'current_task' => 'Operation failed: ' . $e->getMessage(),
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            Log::error('PushAllOvertimeToJPayroll: Job failed', [
                'jobProgressId' => $this->jobProgressId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        $progress = JobProgress::find($this->jobProgressId);
        if ($progress) {
            $progress->update([
                'status' => 'failed',
                'current_task' => 'Job failed permanently',
                'completed_at' => now(),
                'error_message' => $exception->getMessage(),
            ]);
        }

        Log::error('PushAllOvertimeToJPayroll: Job failed permanently', [
            'jobProgressId' => $this->jobProgressId,
            'exception' => $exception->getMessage(),
        ]);
    }
}