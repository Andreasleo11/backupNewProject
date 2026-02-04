<?php

use App\Application\PurchaseRequest\Services\HistoricalPrBackfiller;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\DB;

uses(DatabaseTruncation::class);

test('it backfills historical records correctly', function () {
    $user = User::factory()->create();

    // 1. Insert raw records directly into DB
    $records = [
        ['status' => 0, 'doc_num' => 'PR-0'],
        ['status' => 1, 'doc_num' => 'PR-1'],
        ['status' => 2, 'doc_num' => 'PR-2'],
        ['status' => 3, 'doc_num' => 'PR-3'],
        ['status' => 4, 'doc_num' => 'PR-4'],
        ['status' => 5, 'doc_num' => 'PR-5'],
        ['status' => 6, 'doc_num' => 'PR-6'],
        ['status' => 7, 'doc_num' => 'PR-7'],
        ['status' => 8, 'doc_num' => 'PR-8'],
    ];

    foreach ($records as $record) {
        DB::table('purchase_requests')->insert(array_merge($record, [
            'user_id_create' => $user->id,
            'date_pr' => now(),
            'date_required' => now(),
            'to_department' => 'Purchasing',
            'from_department' => 'COMPUTER',
            'branch' => 'JAKARTA',
            'type' => 'office',
            'pr_no' => 'TMP',
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    $backfiller = new HistoricalPrBackfiller;
    $updatedCount = $backfiller->backfill();

    expect($updatedCount)->toBe(9);

    // Verify some mappings
    $pr1 = DB::table('purchase_requests')->where('doc_num', 'PR-1')->first();
    expect($pr1->workflow_status)->toBe('IN_REVIEW');
    expect($pr1->workflow_step)->toBe('Pending Dept Head');

    $pr4 = DB::table('purchase_requests')->where('doc_num', 'PR-4')->first();
    expect($pr4->workflow_status)->toBe('APPROVED');
    expect($pr4->workflow_step)->toBeNull();

    $pr8 = DB::table('purchase_requests')->where('doc_num', 'PR-8')->first();
    expect($pr8->workflow_status)->toBe('CANCELED');
    expect($pr8->workflow_step)->toBeNull();
});
