<?php

namespace Tests\Unit\Domain\QAQC;

use App\Domain\QAQC\Services\QaqcReportService;
use App\Models\Detail;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QaqcReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private QaqcReportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new QaqcReportService();
    }

    /** @test */
    public function it_can_get_reports_without_status_filter()
    {
        Report::factory()->count(15)->create();

        $reports = $this->service->getReports();

        $this->assertCount(9, $reports);
        $this->assertEquals(15, $reports->total());
    }

    /** @test */
    public function it_can_filter_reports_by_approved_status()
    {
        Report::factory()->count(3)->create(['is_approve' => 1]);
        Report::factory()->count(2)->create(['is_approve' => 0]);

        $reports = $this->service->getReports('approved');

        $this->assertCount(3, $reports);
    }

    /** @test */
    public function it_can_filter_reports_by_rejected_status()
    {
        Report::factory()->count(2)->create(['is_approve' => false]);
        Report::factory()->count(3)->create(['is_approve' => true]);

        $reports = $this->service->getReports('rejected');

        $this->assertCount(2, $reports);
    }

    /** @test */
    public function it_can_delete_report_and_its_details()
    {
        $report = Report::factory()->create();
        Detail::factory()->count(3)->create(['report_id' => $report->id]);

        $this->service->deleteReport($report->id);

        $this->assertDatabaseMissing('reports', ['id' => $report->id]);
        $this->assertDatabaseCount('details', 0);
    }

    /** @test */
    public function it_can_save_autograph_for_report()
    {
        $report = Report::factory()->create();

        $this->service->saveAutograph($report->id, 1, 'john_doe');

        $report->refresh();
        $this->assertEquals('john_doe.png', $report->autograph_1);
        $this->assertEquals('john_doe', $report->autograph_user_1);
    }

    /** @test */
    public function it_can_reject_report_with_description()
    {
        $report = Report::factory()->create(['is_approve' => true]);

        $this->service->rejectReport($report->id, 'Quality issues found');

        $report->refresh();
        $this->assertFalse($report->is_approve);
        $this->assertEquals('Quality issues found', $report->description);
    }

    /** @test */
    public function it_can_lock_report()
    {
        $report = Report::factory()->create(['is_locked' => false]);

        $this->service->lockReport($report->id);

        $report->refresh();
        $this->assertTrue($report->is_locked);
    }

    /** @test */
    public function it_can_update_do_number_for_detail()
    {
        $detail = Detail::factory()->create(['do_num' => null]);

        $this->service->updateDoNumber($detail->id, 'DO-2026-001');

        $detail->refresh();
        $this->assertEquals('DO-2026-001', $detail->do_num);
    }

    /** @test */
    public function it_can_get_monthly_report_data_grouped_by_month_and_customer()
    {
        // Create reports with different months and customers
        $report1 = Report::factory()->create([
            'rec_date' => '2026-01-15',
            'customer' => 'Customer A'
        ]);
        Detail::factory()->create([
            'report_id' => $report1->id,
            'rec_quantity' => 100,
            'verify_quantity' => 95,
            'price' => 10,
            'cant_use' => 5
        ]);

        $result = $this->service->getMonthlyReportData();

        $this->assertArrayHasKey('2026-01', $result);
        $this->assertArrayHasKey('Customer A', $result['2026-01']);
        $this->assertEquals(100, $result['2026-01']['Customer A']['total_rec_quantity']);
    }

    /** @test */
    public function it_can_get_monthly_report_details_for_specific_month()
    {
        Report::factory()->create(['rec_date' => '2026-01-15']);
        Report::factory()->create(['rec_date' => '2026-02-15']);

        $reports = $this->service->getMonthlyReportDetails('2026-01-15');

        $this->assertCount(1, $reports);
    }

    /** @test */
    public function it_can_mark_report_as_emailed()
    {
        $report = Report::factory()->create(['has_been_emailed' => false]);

        $this->service->markAsEmailed($report->id);

        $report->refresh();
        $this->assertTrue($report->has_been_emailed);
    }
}
