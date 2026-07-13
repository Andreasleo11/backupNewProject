<?php

namespace App\Livewire\Verification\Steps;

use App\Livewire\Verification\Concerns\VerificationRules;
use App\Models\MasterDataRogCustomerName;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Component;

class Header extends Component
{
    use VerificationRules;

    #[Modelable]
    public array $form = [];

    protected function messages(): array
    {
        return $this->messagesAll();
    }

    protected function validationAttributes(): array
    {
        return $this->attributesAll();
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rulesHeader());
    }

    #[On('request-validate')]
    public function handleValidation(int $step): void
    {
        if ($step !== 1) {
            return;
        }

        try {
            $this->validate($this->rulesHeader());
            $this->resetErrorBag();
            $this->dispatch('step-valid', step: 1);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
            $this->dispatch('step-invalid', step: 1, errors: $e->errors());

            return;
        }
    }

    public function render()
    {
        return view('livewire.verification.steps.header', [
            'customers' => MasterDataRogCustomerName::orderBy('name')->get(),
        ]);
    }
}
