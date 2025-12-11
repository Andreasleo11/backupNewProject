<?php

namespace App\Livewire\Admin\Verification\Defects;

use App\Infrastructure\Persistence\Eloquent\Models\DefectCatalog;
use Livewire\Component;

class CatalogEdit extends Component
{
    public ?DefectCatalog $row = null;

    public string $code = '';

    public string $name = '';

    public string $default_severity = 'LOW';

    public string $default_source = 'CUSTOMER';

    public float $default_quantity = 0.0;

    public ?string $notes = null;

    public bool $active = true;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->row = DefectCatalog::findOrFail($id);
            $this->code = $this->row->code;
            $this->name = $this->row->name;
            $this->default_severity = $this->row->default_severity?->value ?? (string) $this->row->default_severity;
            $this->default_source = $this->row->default_source?->value ?? (string) $this->row->default_source;
            $this->default_quantity = (float) $this->row->default_quantity;
            $this->notes = $this->row->notes;
            $this->active = (bool) $this->row->active;
        }
    }

    protected function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:64'],
            'name' => ['required', 'string', 'max:191'],
            'default_severity' => ['required', 'in:LOW,MEDIUM,HIGH'],
            'default_source' => ['required', 'in:CUSTOMER,DAIJO,SUPPLIER'],
            'default_quantity' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'active' => ['boolean'],
        ];
    }

    public function save()
    {
        $this->validate();

        $payload = [
            'code' => $this->code,
            'name' => $this->name,
            'default_severity' => $this->default_severity,
            'default_source' => $this->default_source,
            'default_quantity' => $this->default_quantity,
            'notes' => $this->notes,
            'active' => $this->active,
        ];

        if ($this->row) {
            $this->row->update($payload);
            session()->flash('ok', 'Updated.');
        } else {
            $this->row = DefectCatalog::create($payload);
            session()->flash('ok', 'Created.');

            return redirect()->route('admin.verification.defects.edit', $this->row->id);
        }
    }

    public function render()
    {
        return view('livewire.admin.verification.defects.catalog-edit');
    }
}
