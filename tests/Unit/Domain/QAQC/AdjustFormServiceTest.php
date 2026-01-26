<?php

namespace Tests\Unit\Domain\QAQC;

use App\Domain\QAQC\Services\AdjustFormService;
use App\Models\Detail;
use App\Models\FormAdjustMaster;
use App\Models\HeaderFormAdjust;
use App\Models\MasterDataAdjust;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AdjustFormServiceTest extends TestCase
{
    use RefreshDatabase;

    private AdjustFormService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AdjustFormService();
    }

    /** @test */
    public function it_can_get_or_create_header_form_adjust()
    {
        $report = Report::factory()->create();

        $header = $this->service->getOrCreateHeader($report->id);

        $this->assertInstanceOf(HeaderFormAdjust::class, $header);
        $this->assertDatabaseHas('header_form_adjusts', [
            'report_id' => $report->id,
        ]);
    }

    /** @test */
    public function it_returns_existing_header_when_already_exists()
    {
        $report = Report::factory()->create();
        $existingHeader = HeaderFormAdjust::create(['report_id' => $report->id]);

        $header = $this->service->getOrCreateHeader($report->id);

        $this->assertEquals($existingHeader->id, $header->id);
        $this->assertDatabaseCount('header_form_adjusts', 1);
    }

    /** @test */
    public function it_can_get_master_data_for_report_parts()
    {
        $report = Report::factory()->create();
        $detail1 = Detail::factory()->create([
            'report_id' => $report->id,
            'part_name' => 'ABC/123'
        ]);
        $detail2 = Detail::factory()->create([
            'report_id' => $report->id,
            'part_name' => 'DEF/456'
        ]);

        MasterDataAdjust::create(['fg_code' => 'ABC', 'rm_code' => 'RM-001']);
        MasterDataAdjust::create(['fg_code' => 'DEF', 'rm_code' => 'RM-002']);

        $masterData = $this->service->getMasterDataForReport($report->id);

        $this->assertCount(2, $masterData);
    }

    /** @test */
    public function it_can_save_adjustment_with_master_data()
    {
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
        $this->assertEquals('kg', $detail->fg_measure);
    }

    /** @test */
    public function it_can_save_warehouse_for_detail()
    {
        $detail = Detail::factory()->create(['fg_warehouse_name' => null]);

        $this->service->saveWarehouse($detail->id, 'Warehouse B');

        $detail->refresh();
        $this->assertEquals('Warehouse B', $detail->fg_warehouse_name);
    }

    /** @test */
    public function it_can_add_remark_to_detail()
    {
        $detail = Detail::factory()->create(['remark' => null]);

        $this->service->addRemark($detail->id, 'Important note');

        $detail->refresh();
        $this->assertEquals('Important note', $detail->remark);
    }

    /** @test */
    public function it_can_save_autograph_for_header_form()
    {
        $this->actingAs(\App\Models\User::factory()->create(['name' => 'test_user']));
        
        $report = Report::factory()->create();
        $header = HeaderFormAdjust::create(['report_id' => $report->id]);

        $this->service->saveAutograph($header->id, 1);

        $header->refresh();
        $this->assertEquals('test_user.png', $header->autograph_1);
    }
}
