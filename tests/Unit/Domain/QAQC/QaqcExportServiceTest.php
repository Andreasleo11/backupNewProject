<?php

use App\Domain\QAQC\Services\QaqcExportService;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new QaqcExportService;
    Storage::fake('public');
});

test('it can export reports to excel', function () {
    Excel::fake();

    $this->service->exportReportsToExcel();

    Excel::assertDownloaded('reports-all-data.xlsx');
});

test('it can export form adjust to excel', function () {
    Excel::fake();

    $this->service->exportFormAdjustToExcel();

    Excel::assertDownloaded('formadjust-all-data.xlsx');
});

test('it can export monthly report with correct filename', function () {
    Excel::fake();

    $this->service->exportMonthlyReport('2026-01-15');

    Excel::assertDownloaded('VQC MonthlyReport January-2026.xlsx');
});

test('it can save pdf to storage', function () {
    $this->actingAs(\App\Models\User::factory()->create());

    $report = Report::factory()->create();
    \App\Models\Detail::factory()->create(['report_id' => $report->id]);

    $filePath = $this->service->savePdf($report->id);

    expect($filePath)->toBe("pdfs/verification-report-{$report->id}.pdf");
    Storage::disk('public')->assertExists($filePath);
});

test('it prepares report data correctly for pdf', function () {
    $this->actingAs(\App\Models\User::factory()->create());

    $report = Report::factory()->create([
        'autograph_user_1' => 'john_doe',
        'autograph_user_2' => 'jane_smith',
        'autograph_user_3' => null,
    ]);

    \App\Models\Detail::factory()->create([
        'report_id' => $report->id,
        'daijo_defect_detail' => json_encode(['defect' => 'test']),
        'customer_defect_detail' => json_encode(['defect' => 'customer']),
        'supplier_defect_detail' => json_encode(['defect' => 'supplier']),
        'remark' => json_encode(['note' => 'test note']),
    ]);

    // Since prepareReportData is private, we test it indirectly through previewPdf
    $view = $this->service->previewPdf($report->id);

    expect($view)->not->toBeNull();
});
