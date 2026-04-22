<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixEvaluationTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evaluation:fix-types {--dry-run : Only show what would be fixed}';

    protected $description = 'Re-sync evaluation_type from employees table for all evaluation records';

    public function handle()
    {
        $total = \App\Models\EvaluationData::count();
        $fixed = 0;
        $bar = $this->output->createProgressBar($total);

        \App\Models\EvaluationData::with('karyawan')->chunk(500, function ($records) use (&$fixed, $bar) {
            foreach ($records as $record) {
                $correctType = $record->evaluationType(true);

                if ($record->evaluation_type !== $correctType) {
                    if ($this->option('dry-run')) {
                        $this->line("\nNIK {$record->NIK} ({$record->Month->format('Y-m')}): {$record->evaluation_type} -> {$correctType}");
                    } else {
                        $record->update(['evaluation_type' => $correctType]);
                    }
                    $fixed++;
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->line('');

        $status = $this->option('dry-run') ? 'Would fix' : 'Successfully fixed';
        $this->info("{$status} {$fixed} out of {$total} records.");
    }
}
