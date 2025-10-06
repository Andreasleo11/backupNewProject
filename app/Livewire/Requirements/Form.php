<?php

// app/Livewire/Requirements/Form.php

namespace App\Livewire\Requirements;

use App\Models\Requirement;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
class Form extends Component
{
    public ?Requirement $requirement = null; // null = create mode

    // Form fields
    public string $code = '';

    public string $name = '';

    public ?string $description = null;

    public string $allowed_mimetypes_input = ''; // textarea or comma-separated

    public int $min_count = 1;

    public ?int $validity_days = null;

    public string $frequency = 'once'; // once|yearly|quarterly|monthly

    public bool $requires_approval = false;

    public function mount(?Requirement $requirement): void
    {
        $this->requirement = $requirement;
        if ($requirement?->exists) {
            $this->code = $requirement->code;
            $this->name = $requirement->name;
            $this->description = $requirement->description;
            $this->allowed_mimetypes_input = implode(",\n", $requirement->allowed_mimetypes ?? []);
            $this->min_count = (int) $requirement->min_count;
            $this->validity_days = $requirement->validity_days;
            $this->frequency = $requirement->frequency;
            $this->requires_approval = (bool) $requirement->requires_approval;
        }
    }

    protected function rules(): array
    {
        return [
            'code' => [
                'required', 'string', 'max:64',
                // allow uppercase letters, numbers, underscore, dash
                'regex:/^[A-Z0-9_\-]+$/',
                Rule::unique('requirements', 'code')->ignore($this->requirement?->id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'allowed_mimetypes_input' => ['nullable', 'string', 'max:5000'],
            'min_count' => ['required', 'integer', 'min:1', 'max:20'],
            'validity_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'frequency' => ['required', Rule::in(['once', 'yearly', 'quarterly', 'monthly'])],
            'requires_approval' => ['boolean'],
        ];
    }

    private function parsedMimes(): array
    {
        if (trim($this->allowed_mimetypes_input) === '') {
            return [];
        }
        $parts = preg_split('/[\n,]+/', $this->allowed_mimetypes_input);

        return array_values(array_filter(array_map(fn ($v) => trim($v), $parts)));
    }

    public function save()
    {
        $this->validate();

        $data = [
            'code' => strtoupper($this->code),
            'name' => $this->name,
            'description' => $this->description ?: null,
            'allowed_mimetypes' => $this->parsedMimes(),
            'min_count' => $this->min_count,
            'validity_days' => $this->validity_days,
            'frequency' => $this->frequency,
            'requires_approval' => $this->requires_approval,
        ];

        if ($this->requirement) {
            $this->requirement->update($data);
            session()->flash('success', 'Requirement updated.');
        } else {
            $this->requirement = Requirement::create($data);
            session()->flash('success', 'Requirement created.');
        }

        return redirect()->route('requirements.index');
    }

    #[Title('Requirement Form')]
    public function render()
    {
        return view('livewire.requirements.form');
    }
}
