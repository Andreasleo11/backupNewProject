<?php

use App\Application\PurchaseRequest\DTOs\CreatePurchaseRequestDTO;
use App\Application\PurchaseRequest\DTOs\PurchaseRequestItemDTO;
use App\Application\PurchaseRequest\UseCases\CreatePurchaseRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('debug pr creation', function () {
    $this->seed(\Database\Seeders\RefactoredPrPermissionsSeeder::class);
    $this->seed(\Database\Seeders\PrApprovalRulesSeeder::class);

    $fromDept = Department::factory()->create(['name' => 'Computer', 'is_office' => true]);
    $toDept = Department::factory()->create(['name' => 'Purchasing', 'is_office' => true]);
    $user = User::factory()->create(['department_id' => $fromDept->id]);

    $useCase = app(CreatePurchaseRequest::class);

    $dto = new CreatePurchaseRequestDTO(
        requestedByUserId: $user->id,
        fromDepartment: 'Computer',
        toDepartment: 'Purchasing',
        branch: 'JAKARTA',
        datePr: now()->format('Y-m-d'),
        dateRequired: now()->addDays(7)->format('Y-m-d'),
        remark: 'Debug',
        supplier: 'Supplier',
        pic: 'PIC',
        isDraft: false,
        isImport: false,
        items: [
            new PurchaseRequestItemDTO(
                itemName: 'Item 1',
                quantity: 10,
                purpose: 'Test',
                price: 100,
                uom: 'PCS',
                currency: 'IDR'
            ),
        ]
    );

    try {
        $pr = $useCase->handle($dto);
        expect($pr->from_department)->toBe('COMPUTER');
        expect($pr->status)->toBe(1);
    } catch (\Throwable $e) {
        fwrite(STDERR, 'ERROR CAUGHT: ' . $e->getMessage() . "\n");
        fwrite(STDERR, 'TRACE: ' . $e->getTraceAsString() . "\n");
        throw $e;
    }
});
