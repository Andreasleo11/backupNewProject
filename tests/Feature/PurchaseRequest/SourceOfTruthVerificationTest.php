<?php

use App\Application\PurchaseRequest\Services\HistoricalPrBackfiller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('it correctly backfills source of truth sample data', function () {
    $user = User::factory()->create();

    // Legacy Source of Truth IDs: 4681, 4680, 4679, 4678, 4677
    $samples = [
        [
            'id' => 4681,
            'doc_num' => 'MT/PR/JKT/260126/009',
            'status' => 6,
            'from_department' => 'Maintenance',
            'to_department' => 'Maintenance',
            'autograph_1' => 'CATUR.png',
            'autograph_2' => 'agussuparto.png',
            'autograph_5' => 'albert.png',
        ],
        [
            'id' => 4680,
            'doc_num' => 'MT/PR/JKT/260126/008',
            'status' => 1,
            'from_department' => 'Maintenance',
            'to_department' => 'Maintenance',
            'autograph_1' => 'POPON.png',
        ],
        [
            'id' => 4679,
            'doc_num' => 'HRD/PR/JKT/260126/007',
            'status' => 5,
            'from_department' => 'Personnel',
            'to_department' => 'Personnel',
            'autograph_1' => 'POPON.png',
            'autograph_2' => 'Kautsar.png',
            'autograph_5' => 'albert.png',
        ],
        [
            'id' => 4678,
            'doc_num' => 'CP/PR/JKT/260126/006',
            'status' => 6,
            'from_department' => 'Computer',
            'to_department' => 'Computer',
            'autograph_1' => 'NAYA.png',
            'autograph_2' => 'benny.png',
        ],
        [
            'id' => 4677,
            'doc_num' => 'MT/PR/JKT/260126/005',
            'status' => 6,
            'from_department' => 'Maintenance',
            'to_department' => 'Maintenance',
            'autograph_1' => 'UMI_KULSUM.png',
            'autograph_2' => 'arifin.png',
            'autograph_5' => 'albert.png',
        ],
    ];

    foreach ($samples as $sample) {
        DB::table('purchase_requests')->insert(array_merge($sample, [
            'user_id_create' => $user->id,
            'date_pr' => '2026-01-26',
            'date_required' => '2026-01-29',
            'pr_no' => 'TMP-' . $sample['id'],
            'branch' => 'JAKARTA',
            'type' => 'factory', // default to factory for most samples
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    $backfiller = new HistoricalPrBackfiller;
    $backfiller->backfill();

    // Assertions based on our refined mapping

    // ID 4681: Status 6 -> IN_REVIEW / Pending Purchaser
    $pr4681 = DB::table('purchase_requests')->where('id', 4681)->first();
    expect($pr4681->workflow_status)->toBe('IN_REVIEW');
    expect($pr4681->workflow_step)->toBe('Pending Purchaser');

    // ID 4680: Status 1 -> IN_REVIEW / Pending Dept Head
    $pr4680 = DB::table('purchase_requests')->where('id', 4680)->first();
    expect($pr4680->workflow_status)->toBe('IN_REVIEW');
    expect($pr4680->workflow_step)->toBe('Pending Dept Head');

    // ID 4679: Status 5 -> REJECTED
    $pr4679 = DB::table('purchase_requests')->where('id', 4679)->first();
    expect($pr4679->workflow_status)->toBe('REJECTED');
    expect($pr4679->workflow_step)->toBeNull();

    // ID 4678: Status 6 -> IN_REVIEW / Pending Purchaser
    $pr4678 = DB::table('purchase_requests')->where('id', 4678)->first();
    expect($pr4678->workflow_status)->toBe('IN_REVIEW');
    expect($pr4678->workflow_step)->toBe('Pending Purchaser');
});
