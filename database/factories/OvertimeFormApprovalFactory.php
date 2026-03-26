<?php

namespace Database\Factories;

use App\Domain\Overtime\Models\OvertimeForm;
use App\Models\OvertimeFormApproval;
use Illuminate\Database\Eloquent\Factories\Factory;

class OvertimeFormApprovalFactory extends Factory
{
    protected $model = OvertimeFormApproval::class;

    public function definition(): array
    {
        return [
            'overtime_form_id' => OvertimeForm::factory(),
            'flow_step_id' => 1,
            'status' => 'pending',
            'approver_id' => null,
            'signed_at' => null,
            'signature_path' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'signed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }
}
