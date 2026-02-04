<?php

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\DTOs\CreatePurchaseRequestDTO;
use App\Application\PurchaseRequest\DTOs\PurchaseRequestItemDTO;
use App\Application\PurchaseRequest\UseCases\CreatePurchaseRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;

uses(DatabaseTruncation::class);

beforeEach(function () {
    (new \Database\Seeders\PrRoleMappingSeeder)->run();
    (new \Database\Seeders\PrApprovalRulesSeeder)->run();

    $this->fromDept = Department::factory()->create(['name' => 'Computer', 'is_office' => true]);
    $this->toDept = Department::factory()->create(['name' => 'Purchasing', 'is_office' => true]);
    $this->user = User::factory()->create(['department_id' => $this->fromDept->id]);

    // Assign role for approval steps
    $this->deptHead = User::factory()->create();
    $this->deptHead->assignRole('pr-dept-head-office');

    \App\Infrastructure\Persistence\Eloquent\Models\UserSignature::create([
        'user_id' => $this->deptHead->id,
        'label' => 'Test Signature',
        'is_default' => true,
        'file_path' => 'signatures/test.png',
        'sha256' => 'sample-sha',
    ]);
});

test('it progresses through approval steps', function () {
    $useCase = app(CreatePurchaseRequest::class);

    $dto = new CreatePurchaseRequestDTO(
        requestedByUserId: $this->user->id,
        fromDepartment: 'Computer',
        toDepartment: 'Purchasing',
        branch: 'JAKARTA',
        datePr: now()->format('Y-m-d'),
        dateRequired: now()->format('Y-m-d'),
        remark: 'Test',
        supplier: 'Supplier',
        pic: 'PIC',
        isDraft: false,
        isImport: false,
        items: [
            new PurchaseRequestItemDTO('Item', 1, 'Test', 100, 'PCS', 'IDR'),
        ]
    );

    $pr = $useCase->handle($dto);
    $pr->refresh();

    expect($pr->approvalRequest->status)->toBe('IN_REVIEW');
    expect($pr->approvalRequest->current_step)->toBe(1);

    // Approve as Dept Head
    $this->actingAs($this->deptHead);
    $approvals = app(Approvals::class);
    $approvals->approve($pr, $this->deptHead->id, 'Looks good');

    $pr->refresh();

    // After first approval, it should move to Purchaser (for office -> purchasing)
    // Rule pr.office.to-purchasing: 1: dept-head-office, 2: purchaser, 3: director
    expect($pr->approvalRequest->current_step)->toBe(2);

    // Check if Sync service updated the PR fields
    // Assuming Sync service is triggered by event or manually
    // In CreatePurchaseRequest, it doesn't sync unless we call it.
    // Wait, the Sync service is usually called in ApprovePurchaseRequest UseCase.
});
