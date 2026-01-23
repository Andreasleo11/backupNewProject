<?php

use App\Domain\Overtime\Services\OvertimeApprovalService;
use App\Models\HeaderFormOvertime;
use App\Models\OvertimeFormApproval;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new OvertimeApprovalService();
    
    $this->user = \App\Infrastructure\Persistence\Eloquent\Models\User::factory()->create([
        'name' => 'Test User',
    ]);
    
    $this->actingAs($this->user);
});

describe('OvertimeApprovalService', function () {
    it('can sign and approve overtime form', function () {
        $form = HeaderFormOvertime::factory()->create(['status' => 'pending']);
        $approval = OvertimeFormApproval::factory()->create([
            'header_form_overtime_id' => $form->id,
            'flow_step_id' => 1,
            'status' => 'pending',
        ]);

        $result = $this->service->sign($form->id, 1);

        expect($result['success'])->toBeTrue();
        expect($result['message'])->toBe('Form signed successfully');

        $approval->refresh();
        expect($approval->status)->toBe('approved');
        expect($approval->approver_id)->toBe($this->user->id);
        expect($approval->signed_at)->not->toBeNull();
    });

    it('rejects overtime form with description', function () {
        $form = HeaderFormOvertime::factory()->create(['status' => 'pending']);
        $approval = OvertimeFormApproval::factory()->create([
            'header_form_overtime_id' => $form->id,
            'status' => 'pending',
        ]);

        $result = $this->service->reject($form->id, $approval->id, 'Not enough budget');

        expect($result['success'])->toBeTrue();
        expect($result['message'])->toBe('Report rejected');

        $form->refresh();
        expect($form->status)->toBe('rejected');
        expect($form->description)->toBe('Not enough budget');

        $approval->refresh();
        expect($approval->status)->toBe('rejected');
    });

    it('returns error when form not found', function () {
        $result = $this->service->sign(99999, 1);

        expect($result['success'])->toBeFalse();
        expect($result['message'])->toBe('Form not found');
    });

    it('can reject detail manually', function () {
        $detail = \App\Models\DetailFormOvertime::factory()->create([
            'status' => null,
        ]);

        $result = $this->service->rejectDetail($detail->id);

        expect($result['success'])->toBeTrue();

        $detail->refresh();
        expect($detail->status)->toBe('Rejected');
        expect($detail->reason)->toBe('Rejected manually from DISS server');
    });

    it('returns error when rejecting non-existent detail', function () {
        $result = $this->service->rejectDetail(99999);

        expect($result['success'])->toBeFalse();
        expect($result['message'])->toBe('Detail not found');
    });
});
