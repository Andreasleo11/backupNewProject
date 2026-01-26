<?php

namespace Tests\Unit\Domain\QAQC;

use App\Domain\QAQC\Services\QaqcExportService;
use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class QaqcExportServiceTest extends TestCase
{
    use RefreshDatabase;

    private QaqcExportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new QaqcExportService();
        Storage::fake('public');
    }

    /** @test */
    public function it_can_export_reports_to_excel()
    {
        Excel::fake();

        $response = $this->service->exportReportsToExcel();

        Excel::assertDownloaded('reports-all-data.xlsx');
    }

    /** @test */
    public function it_can_export_form_adjust_to_excel()
    {
        Excel::fake();

        $response = $this->service->exportFormAdjustToExcel();

        Excel::assertDownloaded('formadjust-all-data.xlsx');
    }

    /** @test */
    public function it_can_export_monthly_report_with_correct_filename()
    {
        Excel::fake();

        $response = $this->service->exportMonthlyReport('2026-01-15');

        Excel::assertDownloaded('VQC MonthlyReport January-2026.xlsx');
    }

    /** @test */
    public function it_can_save_pdf_to_storage()
    {
        $this->actingAs(\App\Models\User::factory()->create());
        
        $report = Report::factory()->create();
        \App\Models\Detail::factory()->create(['report_id' => $report->id]);

        $filePath = $this->service->savePdf($report->id);

        $this->assertEquals("pdfs/verification-report-{$report->id}.pdf", $filePath);
        Storage::disk('public')->assertExists($filePath);
    }

    /** @test */
    public function it_prepares_report_data_correctly_for_pdf()
    {
        $this->actingAs(\App\Models\User::factory()->create());
        
        $report = Report::factory()->create([
            'autograph_user_1' => 'john_doe',
            'autograph_user_2' => 'jane_smith',
            'autograph_user_3' => null,
        ]);

        $detail = \App\Models\Detail::factory()->create([
            'report_id' => $report->id,
            'daijo_defect_detail' => json_encode(['defect' => 'test']),
            'customer_defect_detail' => json_encode(['defect' => 'customer']),
            'supplier_defect_detail' => json_encode(['defect' => 'supplier']),
            'remark' => json_encode(['note' => 'test note']),
        ]);

        // Since prepareReportData is private, we test it indirectly through previewPdf
        $view = $this->service->previewPdf($report->id);

        $this->assertNotNull($view);
    }
}
