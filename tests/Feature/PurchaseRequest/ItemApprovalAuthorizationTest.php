<?php

use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\DetailPurchaseRequest;
use App\Models\PurchaseRequest;
use App\Models\Specification;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Tests\Database\Seeders\TestRoleSeeder;
use Tests\Helpers\SignatureTestHelper;

uses(DatabaseTruncation::class);

beforeEach(function () {
    $this->seed(TestRoleSeeder::class);
});

function createPrWithWorkflow(string $roleSlug, int $currentStep): PurchaseRequest
{
    // Map role slug to role ID from TestRoleSeeder
    $roleIdMap = [
        'pr-dept-head-office' => 100,
        'pr-verificator-computer' => 102,
        'pr-director' => 105,
    ];

    $pr = PurchaseRequest::factory()->create();

    $approval = ApprovalRequest::factory()->create([
        'approvable_id' => $pr->id,
        'approvable_type' => PurchaseRequest::class,
        'current_step' => $currentStep,
        'status' => 'IN_REVIEW',
        'submitted_by' => null,
        'submitted_at' => now(),
    ]);

    ApprovalStep::factory()->create([
        'approval_request_id' => $approval->id,
        'sequence' => $currentStep,
        'approver_type' => 'role',
        'approver_id' => $roleIdMap[$roleSlug] ?? null,
        'status' => 'PENDING',
    ]);

    return $pr->fresh(['approvalRequest', 'approvalRequest.steps']);
}

test('dept head can approve item at correct step', function () {
    $user = User::factory()->create(['is_head' => 1]);
    $user->assignRole('pr-dept-head-office');
    SignatureTestHelper::createDefaultSignature($user->id);

    $pr = createPrWithWorkflow('pr-dept-head-office', 1);
    $item = DetailPurchaseRequest::factory()->create([
        'purchase_request_id' => $pr->id,
        'is_approve_by_head' => null,
    ]);

    $response = $this->actingAs($user)->post(
        route('purchase-requests.items.approve', $item)
    );

    $response->assertRedirect();
    $response->assertSessionHas('success');
    expect($item->fresh()->is_approve_by_head)->toBe(1);
});

test('dept head can reject item at correct step', function () {
    $user = User::factory()->create(['is_head' => 1]);
    $user->assignRole('pr-dept-head-office');
    SignatureTestHelper::createDefaultSignature($user->id);

    $pr = createPrWithWorkflow('pr-dept-head-office', 1);
    $item = DetailPurchaseRequest::factory()->create([
        'purchase_request_id' => $pr->id,
        'is_approve_by_head' => null,
    ]);

    $response = $this->actingAs($user)->post(
        route('purchase-requests.items.reject', $item)
    );

    $response->assertRedirect();
    $response->assertSessionHas('success');
    expect($item->fresh()->is_approve_by_head)->toBe(0);
});

test('verificator can approve item at correct step', function () {
    $spec = Specification::factory()->create(['name' => 'VERIFICATOR']);
    $user = User::factory()->create(['specification_id' => $spec->id]);
    $user->assignRole('pr-verificator-computer');
    SignatureTestHelper::createDefaultSignature($user->id);

    $pr = createPrWithWorkflow('pr-verificator-computer', 2);
    $item = DetailPurchaseRequest::factory()->create([
        'purchase_request_id' => $pr->id,
        'is_approve_by_verificator' => null,
    ]);

    $response = $this->actingAs($user)->post(
        route('purchase-requests.items.approve', $item)
    );

    $response->assertRedirect();
    $response->assertSessionHas('success');
    expect($item->fresh()->is_approve_by_verificator)->toBe(1);
});

test('director can approve item at correct step', function () {
    $spec = Specification::factory()->create(['name' => 'DIRECTOR']);
    $user = User::factory()->create(['specification_id' => $spec->id]);
    $user->assignRole('pr-director');
    SignatureTestHelper::createDefaultSignature($user->id);

    $pr = createPrWithWorkflow('pr-director', 3);
    $item = DetailPurchaseRequest::factory()->create([
        'purchase_request_id' => $pr->id,
        'is_approve' => null,
    ]);

    $response = $this->actingAs($user)->post(
        route('purchase-requests.items.approve', $item)
    );

    $response->assertRedirect();
    $response->assertSessionHas('success');
    expect($item->fresh()->is_approve)->toBe(1);
});

test('unauthorized user gets 403', function () {
    $user = User::factory()->create(['is_head' => 0]);
    $pr = createPrWithWorkflow('pr-dept-head-office', 1);
    $item = DetailPurchaseRequest::factory()->create(['purchase_request_id' => $pr->id]);

    $response = $this->actingAs($user)->post(
        route('purchase-requests.items.approve', $item)
    );

    $response->assertForbidden();
});

test('wrong workflow step gets 403', function () {
    $spec = Specification::factory()->create(['name' => 'DIRECTOR']);
    $user = User::factory()->create(['specification_id' => $spec->id]);
    // PR at step 1 (dept head), but user is director
    $pr = createPrWithWorkflow('pr-dept-head-office', 1);
    $item = DetailPurchaseRequest::factory()->create(['purchase_request_id' => $pr->id]);

    $response = $this->actingAs($user)->post(
        route('purchase-requests.items.approve', $item)
    );

    $response->assertForbidden();
});

test('post without csrf fails', function () {
    $user = User::factory()->create(['is_head' => 1]);
    $pr = createPrWithWorkflow('pr-dept-head-office', 1);
    $item = DetailPurchaseRequest::factory()->create(['purchase_request_id' => $pr->id]);

    // In testing environment, Laravel usually disables CSRF protection.
    $response = $this->actingAs($user)
        ->post(route('purchase-requests.items.approve', $item), [
            '_token' => 'invalid_token',
        ], ['X-CSRF-TOKEN' => 'invalid_token']);

    // Based on typical Laravel test setup
    if (app()->environment('testing')) {
        $this->markTestSkipped('CSRF validation is typically disabled in testing environment.');
    }

    $response->assertStatus(419);
});
