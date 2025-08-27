<?php

namespace App\Console\Commands;

use App\Models\ApprovalFlow;
use App\Models\HeaderFormOvertime;
use App\Support\ApprovalFlowResolver;
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
        HeaderFormOvertime::chunk(200, function ($rows) {
            foreach ($rows as $row) {
                // 1. Pick a flow based on which autograph_x columns are filled
                $flowSlug = ApprovalFlowResolver::for($row->toArray());

                $flow = ApprovalFlow::where('slug', $flowSlug)->firstOrFail();
                $row->update(['approval_flow_id' => $flow->id]);

                // 2. Create a log row per step
                foreach ($flow->steps as $step) {
                    $signaturePath = match ($step->step_order) {
                        1 => $row->autograph_1,
                        2 => $row->autograph_2,
                        3 => $row->autograph_3,
                        4 => $row->autograph_4,
                    };

                    $filename = pathinfo($signaturePath, PATHINFO_FILENAME);
                    $name = strtolower($filename);
                    $approverId = \App\Models\User::where('name', $name)->first()?->id;

                    $row->approvals()->updateOrCreate(
                        ['flow_step_id' => $step->id],
                        [
                            'status'    => $signaturePath ? 'approved' : 'pending',
                            'signed_at' => $signaturePath ? now() : null,
                            'comment'   => null,
                            'signature_path' => $signaturePath,
                            'approver_id'    => $approverId,   // set if you can back-fill the user
                        ]
                    );
                }
            }
        });
    }
}
