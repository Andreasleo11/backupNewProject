<?php

namespace Tests\Unit\Application\PurchaseRequest\UseCases;

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\DTOs\CreatePurchaseRequestDTO;
use App\Application\PurchaseRequest\UseCases\CreatePurchaseRequest;
use App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository;
use App\Domain\PurchaseRequest\Services\PurchaseRequestNumberGenerator;
use App\Events\PurchaseRequestCreated;
use App\Models\PurchaseRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class CreatePurchaseRequestTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_handle_creates_pr_generates_number_and_dispatches_event()
    {
        Event::fake([PurchaseRequestCreated::class]);
        DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
            return $callback();
        });

        $repo = Mockery::mock(PurchaseRequestRepository::class);
        $approvals = Mockery::mock(Approvals::class);
        $generator = Mockery::mock(PurchaseRequestNumberGenerator::class);

        $dto = new CreatePurchaseRequestDTO(
            requestedByUserId: 1,
            fromDepartment: 'COMPUTER',
            toDepartment: 'MAINTENANCE',
            branch: 'JAKARTA',
            datePr: '2024-01-01',
            dateRequired: '2024-01-10',
            remark: 'Test Remark',
            supplier: 'Test Supplier',
            pic: 'Test PIC',
            isDraft: false,
            isImport: false,
            items: []
        );

        $mockPr = Mockery::mock(PurchaseRequest::class)->makePartial();
        $mockPr->id = 123;
        $mockPr->from_department = 'COMPUTER';
        $mockPr->approvalRequest = null;
        $mockPr->shouldReceive('saveQuietly')->once();

        // Expectation: Generator called
        $repo->shouldReceive('getOfficeDepartmentNames')
            ->once()
            ->andReturn(['COMPUTER', 'HRD', 'FINANCE']);

        $generator->shouldReceive('generateDocNum')
            ->once()
            ->andReturn('CP/PR/JKT/240101/001');

        $generator->shouldReceive('generatePrNo')
            ->once()
            ->with('MAINTENANCE', 123)
            ->andReturn('MAIN-123');

        // Expectation: Repository create called with doc_num
        $repo->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg['doc_num'] === 'CP/PR/JKT/240101/001'
                    && $arg['status'] === 1 // Not draft
                    && $arg['type'] === 'office'; // COMPUTER is office
            }))
            ->andReturn($mockPr);

        $repo->shouldReceive('addItems')->once();
        $repo->shouldReceive('loadForApprovalContext')->andReturn($mockPr);
        $mockPr->shouldReceive('buildApprovalContext')->andReturn([]);
        $approvals->shouldReceive('submit')
            ->once()
            ->andReturn(new \App\Application\Approval\DTOs\ApprovalInfo(1, 'pending', 1));

        $useCase = new CreatePurchaseRequest($repo, $approvals, $generator);
        $result = $useCase->handle($dto);

        $this->assertSame($mockPr, $result);
        
        Event::assertDispatched(PurchaseRequestCreated::class);
    }
}
