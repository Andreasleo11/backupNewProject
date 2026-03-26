<?php

use App\Domain\QAQC\Services\QaqcReportService;
use App\Models\Detail;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new QaqcReportService;
});

test('it can get reports without status filter', function () {
    Report::factory()->count(15)->create();

    $reports = $this->service->getReports();

    expect($reports)->toHaveCount(9);
    expect($reports->total())->toBe(15);
});

test('it can filter reports by approved status', function () {
    Report::factory()->count(3)->create(['is_approve' => 1]);
    Report::factory()->count(2)->create(['is_approve' => 0]);

    $reports = $this->service->getReports('approved');

    expect($reports)->toHaveCount(3);
});

test('it can filter reports by rejected status', function () {
    Report::factory()->count(2)->create(['is_approve' => false]);
    Report::factory()->count(3)->create(['is_approve' => true]);

    $reports = $this->service->getReports('rejected');

    expect($reports)->toHaveCount(2);
});

test('it can delete report and its details', function () {
    $report = Report::factory()->create();
    Detail::factory()->count(3)->create(['report_id' => $report->id]);

    $this->service->deleteReport($report->id);

    $this->assertDatabaseMissing('reports', ['id' => $report->id]);
    $this->assertDatabaseCount('details', 0);
});

test('it can save autograph for report', function () {
    $report = Report::factory()->create();

    $this->service->saveAutograph($report->id, 1, 'john_doe');

    $report->refresh();
    expect($report->autograph_1)->toBe('john_doe.png');
    expect($report->autograph_user_1)->toBe('john_doe');
});

test('it can reject report with description', function () {
    $report = Report::factory()->create(['is_approve' => true]);

    $this->service->rejectReport($report->id, 'Quality issues found');

    $report->refresh();
    expect($report->is_approve)->toBeFalse();
    expect($report->description)->toBe('Quality issues found');
});

test('it can lock report', function () {
    $report = Report::factory()->create(['is_locked' => false]);

    $this->service->lockReport($report->id);

    $report->refresh();
    expect($report->is_locked)->toBeTrue();
});

test('it can update do number for detail', function () {
    $detail = Detail::factory()->create(['do_num' => null]);

    $this->service->updateDoNumber($detail->id, 'DO-2026-001');

    $detail->refresh();
    expect($detail->do_num)->toBe('DO-2026-001');
});

test('it can get monthly report data grouped by month and customer', function () {
    // Create reports with different months and customers
    $report1 = Report::factory()->create([
        'rec_date' => '2026-01-15',
        'customer' => 'Customer A',
    ]);
    Detail::factory()->create([
        'report_id' => $report1->id,
        'rec_quantity' => 100,
        'verify_quantity' => 95,
        'price' => 10,
        'cant_use' => 5,
    ]);

    $result = $this->service->getMonthlyReportData();

    expect($result)->toHaveKey('2026-01');
    expect($result['2026-01'])->toHaveKey('Customer A');
    expect($result['2026-01']['Customer A']['total_rec_quantity'])->toBe(100);
});

test('it can get monthly report details for specific month', function () {
    Report::factory()->create(['rec_date' => '2026-01-15']);
    Report::factory()->create(['rec_date' => '2026-02-15']);

    $reports = $this->service->getMonthlyReportDetails('2026-01-15');

    expect($reports)->toHaveCount(1);
});

test('it can mark report as emailed', function () {
    $report = Report::factory()->create(['has_been_emailed' => false]);

    $this->service->markAsEmailed($report->id);

    $report->refresh();
    expect($report->has_been_emailed)->toBeTrue();
});
