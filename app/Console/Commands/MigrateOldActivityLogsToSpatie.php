<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class MigrateOldActivityLogsToSpatie extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activitylog:migrate-old
                            {--batch-size=500 : Number of records to process per batch}
                            {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate old activity_logs records to Spatie activity_log table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch-size');

        // Check if old table exists
        if (!DB::getSchemaBuilder()->hasTable('activity_logs')) {
            $this->error('The activity_logs table does not exist.');
            return 1;
        }

        // Get total count
        $totalCount = DB::table('activity_logs')->count();
        
        if ($totalCount === 0) {
            $this->info('No records to migrate.');
            return 0;
        }

        $this->info("Found {$totalCount} records to migrate.");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            
            // Show sample transformation
            $sample = DB::table('activity_logs')->first();
            if ($sample) {
                $this->line("\nSample transformation:");
                $this->table(
                    ['Old Field', 'Old Value', 'New Field', 'New Value'],
                    [
                        ['user_id', $sample->user_id, 'causer_id', $sample->user_id],
                        ['action', $sample->action, 'event', $sample->action],
                        ['model_type', $sample->model_type, 'subject_type', $sample->model_type],
                        ['model_id', $sample->model_id, 'subject_id', $sample->model_id],
                        ['changes', substr($sample->changes ?? 'null', 0, 30) . '...', 'properties', 'JSON with old_attributes/attributes'],
                    ]
                );
            }
            return 0;
        }

        // Confirm before proceeding
        if (!$this->confirm("This will migrate {$totalCount} records. Continue?")) {
            $this->info('Migration cancelled.');
            return 0;
        }

        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        $migratedCount = 0;
        $errorCount = 0;

        DB::table('activity_logs')
            ->orderBy('id')
            ->chunk($batchSize, function ($oldLogs) use ($bar, &$migratedCount, &$errorCount) {
                $newLogs = [];

                foreach ($oldLogs as $oldLog) {
                    try {
                        // Parse the changes JSON
                        $changes = $oldLog->changes ? json_decode($oldLog->changes, true) : [];
                        
                        // Prepare properties in Spatie format
                        $properties = [
                            'attributes' => $changes,
                            'old' => [], // Old log didn't track previous values
                        ];

                        $newLogs[] = [
                            'log_name' => 'legacy',
                            'description' => $oldLog->action,
                            'subject_type' => $oldLog->model_type,
                            'subject_id' => $oldLog->model_id,
                            'causer_type' => $oldLog->user_id > 0 ? 'App\\Models\\User' : null,
                            'causer_id' => $oldLog->user_id > 0 ? $oldLog->user_id : null,
                            'properties' => json_encode($properties),
                            'event' => $oldLog->action,
                            'batch_uuid' => null,
                            'created_at' => $oldLog->created_at,
                            'updated_at' => $oldLog->updated_at,
                        ];

                        $migratedCount++;
                    } catch (\Exception $e) {
                        $errorCount++;
                        $this->error("\nError processing log ID {$oldLog->id}: {$e->getMessage()}");
                    }

                    $bar->advance();
                }

                // Batch insert into new table
                if (!empty($newLogs)) {
                    DB::table('activity_log')->insert($newLogs);
                }
            });

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info("Migration completed!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Records', $totalCount],
                ['Successfully Migrated', $migratedCount],
                ['Errors', $errorCount],
            ]
        );

        // Verify counts
        $newCount = DB::table('activity_log')->where('log_name', 'legacy')->count();
        $this->info("Verification: {$newCount} records found in activity_log with log_name='legacy'");

        if ($errorCount === 0 && $migratedCount === $totalCount) {
            $this->info('✓ All records migrated successfully!');
            $this->newLine();
            $this->comment('Next steps:');
            $this->comment('1. Verify the migrated data in the activity_log table');
            $this->comment('2. Once verified, you can keep the old activity_logs table for reference');
            $this->comment('3. The old table will no longer be written to after trait replacement');
        }

        return 0;
    }
}
