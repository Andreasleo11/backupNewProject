<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $stepMap = [
            1 => 'MAKER',
            2 => 'DEPT_HEAD',
            3 => 'VERIFICATOR',
            4 => 'DIRECTOR',
            5 => 'PURCHASER',
            6 => 'GM',
            7 => 'HEAD_DESIGN',
        ];

        DB::table('purchase_requests')
            ->orderBy('id')
            ->chunkById(200, function ($prs) use ($stepMap) {
                $insert = [];

                foreach ($prs as $pr) {
                    foreach ($stepMap as $slot => $stepCode) {
                        $imageCol = "autograph_{$slot}";
                        $nameCol = "autograph_user_{$slot}";

                        if (! $pr->$imageCol) {
                            continue;
                        }

                        // Try to resolve user_id via name/email if you can, or leave null
                        $signedById = null;

                        $signedById = DB::table('users')->where('name', $pr->$nameCol)->value('id');

                        $insert[] = [
                            'purchase_request_id' => $pr->id,
                            'step_code' => $stepCode,
                            'signed_by_user_id' => $signedById,
                            'image_path' => $pr->$imageCol,
                            'signed_at' => $pr->updated_at, // best guess
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                if (! empty($insert)) {
                    DB::table('purchase_request_signatures')->insert($insert);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // no-op, signatures are additive
    }
};
