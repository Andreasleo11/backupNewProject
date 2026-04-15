<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OvertimeAutographs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:overtime-autographs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'none';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->error('This command is deprecated. The Overtime module now uses the Unified ApprovalEngine.');

        return 1;
    }
}
