<?php

namespace App\Console\Commands;

use App\Models\ApprovalFlow;
use App\Models\HeaderFormOvertime;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

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
                $flowSlug = $this->pickApprovalFlowSlug($row);


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

    /**
     * Decide which approval-flow slug a header form should use.
     *
     * @param  \App\Models\HeaderFormOvertime  $header
     * @return string
     *
     * ────────────────────────────────────────────────────────────────
     *  Rules
     *  ── Department + Branch + is_design
     *   1. ASSEMBLY, SECOND PROCESS, STORE, LOGISTIC
     *          →  dept-head-director
     *   2. MOULDING + is_design = false + branch = Karawang
     *          →  gm-director
     *   3. MOULDING + is_design = false
     *          →  supervisor-dept-head-director
     *   4. MOULDING + is_design = true
     *          →  dept-head-director
     *   5. PLASTIC INJECTION + branch = Karawang
     *          →  gm-director
     *   6. PLASTIC INJECTION (other branches)
     *          →  dept-head-gm-director
     *   7. Fallback
     *          →  dept-head-director
     */
    protected function pickApprovalFlowSlug($header): string
    {
        $dept   = Str::upper($header->dept->name ?? '');      // assumes dept relation
        $branch = Str::upper($header->branch ?? '');
        $isDesign = (bool) $header->is_design;

        return match (true) {
            in_array($dept, ['ASSEMBLY', 'SECOND PROCESS', 'STORE', 'LOGISTIC']) =>
            'dept-head-director',

            // ————————— MOULDING rules —————————
            $dept === 'MOULDING' && ! $isDesign && $branch === 'KARAWANG' =>
            'gm-director',

            $dept === 'MOULDING' && ! $isDesign =>
            'supervisor-dept-head-director',

            $dept === 'MOULDING' && $isDesign =>
            'dept-head-director',

            // ————————— PLASTIC INJECTION rules —————————
            $dept === 'PLASTIC INJECTION' && $branch === 'KARAWANG' =>
            'gm-director',

            $dept === 'PLASTIC INJECTION' =>
            'dept-head-gm-director',

            // ————————— Fallback —————————
            default => 'dept-head-director',
        };
    }
}
