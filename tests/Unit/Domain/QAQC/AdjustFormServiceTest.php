<?php

use App\Domain\QAQC\Services\AdjustFormService;
use App\Models\Detail;
use App\Models\HeaderFormAdjust;
use App\Models\MasterDataAdjust;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new AdjustFormService();
});

test('it can get or create header form adjust', function () {
    $report = Report::factory()->create();

    $header = $this->service->getOrCreateHeader($report->id);

    expect($header)->toBeInstanceOf(HeaderFormAdjust::class);
    $this->assertDatabaseHas('header_form_adjusts', [
        'report_id' => $report->id,
    ]);
});

test('it returns existing header when already exists', function () {
    $report = Report::factory()->create();
    $existingHeader = HeaderFormAdjust::create(['report_id' => $report->id]);

    $header = $this->service->getOrCreateHeader($report->id);

    expect($header->id)->toBe($existingHeader->id);
    $this->assertDatabaseCount('header_form_adjusts', 1);
});

test('it can get master data for report parts', function () {
    $report = Report::factory()->create();
    Detail::factory()->create([
        'report_id' => $report->id,
        'part_name' => 'ABC/123'
    ]);
    Detail::factory()->create([
        'report_id' => $report->id,
        'part_name' => 'DEF/456'
    ]);

    MasterDataAdjust::create(['fg_code' => 'ABC', 'rm_code' => 'RM-001']);
    MasterDataAdjust::create(['fg_code' => 'DEF', 'rm_code' => 'RM-002']);

    $masterData = $this->service->getMasterDataForReport($report->id);

    expect($masterData)->toHaveCount(2);
});

test('it can save adjustment with master data', function () {
    $detail = Detail::factory()->create();
    $header = HeaderFormAdjust::create(['report_id' => $detail->report_id]);
    
    $masterData = MasterDataAdjust::create([
        'fg_code' => 'FG-001',
        'rm_code' => 'RM-001',
        'rm_description' => 'Raw Material 1',
        'rm_quantity' => 100,
        'fg_measure' => 'kg',
        'rm_measure' => 'kg',
    ]);

    $data = [
        'detail_id' => $detail->id,
        'master_id' => $masterData->id,
        'header_id' => $header->id,
        'rm_warehouse' => 'Warehouse A',
    ];

    $this->service->saveAdjustment($data);

    $this->assertDatabaseHas('form_adjust_masters', [
        'detail_id' => $detail->id,
        'header_id' => $header->id,
        'rm_code' => 'RM-001',
        'warehouse_name' => 'Warehouse A',
    ]);

    $detail->refresh();
    expect($detail->fg_measure)->toBe('kg');
});

test('it can save warehouse for detail', function () {
    $detail = Detail::factory()->create(['fg_warehouse_name' => null]);

    $this->service->saveWarehouse($detail->id, 'Warehouse B');

    $detail->refresh();
    expect($detail->fg_warehouse_name)->toBe('Warehouse B');
});

test('it can add remark to detail', function () {
    $detail = Detail::factory()->create(['remark' => null]);

    $this->service->addRemark($detail->id, 'Important note');

    $detail->refresh();
    expect($detail->remark)->toBe('Important note');
});

test('it can save autograph for header form', function () {
    $this->actingAs(\App\Models\User::factory()->create(['name' => 'test_user']));
    
    $report = Report::factory()->create();
    $header = HeaderFormAdjust::create(['report_id' => $report->id]);

    $this->service->saveAutograph($header->id, 1);

    $header->refresh();
    expect($header->autograph_1)->toBe('test_user.png');
});
