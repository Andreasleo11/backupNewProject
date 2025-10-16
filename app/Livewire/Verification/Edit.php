<?php

namespace App\Livewire\Verification;

use App\Application\Verification\DTOs\ItemData;
use App\Application\Verification\DTOs\ReportData;
use App\Application\Verification\UseCases\CreateReport;
use App\Application\Verification\UseCases\UpdateReport;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Edit extends Component
{
    use AuthorizesRequests;

    public ?VerificationReport $report = null;

    /** @var array{title:string,description:?string,meta?:array} */
    public array $form = [
        'title' => '',
        'description' => null,
        'meta' => [],
    ];

    /** @var array<int, array{name:string,notes:?string,amount:float|int|string}> */
    public array $items = [
        ['name' => '', 'notes' => null, 'amount' => 0],
    ];

    // --- Lifecycle ----------------------------------------------------------

    public function mount(?VerificationReport $report): void
    {
        $this->report = $report;

        if ($report?->exists) {
            // Gate: only creator can edit while DRAFT
            $this->authorize('update', $report);

            $this->form = [
                'title' => $report->title,
                'description' => $report->description,
                'meta' => $report->meta ?? [],
            ];

            $this->items = $report->items
                ->map(fn ($i) => [
                    'name' => $i->name,
                    'notes' => $i->notes,
                    'amount' => (float) $i->amount,
                ])
                ->toArray();
        }
    }

    // --- Validation ---------------------------------------------------------

    protected function rules(): array
    {
        return [
            'form.title' => ['required', 'string', 'max:255'],
            'form.description' => ['nullable', 'string'],
            'form.meta' => ['nullable', 'array'],

            'items' => ['array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.notes' => ['nullable', 'string'],
            'items.*.amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    // --- Commands -----------------------------------------------------------

    public function addItem(): void
    {
        $this->items[] = ['name' => '', 'notes' => null, 'amount' => 0];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save(CreateReport $create, UpdateReport $update): void
    {
        $this->validate();

        // Build DTOs
        $reportDto = ReportData::fromArray($this->form);
        $itemDtos = array_map(fn ($row) => ItemData::fromArray($row), $this->items);

        if ($this->report?->exists) {
            // Update existing
            $this->authorize('update', $this->report);

            $this->report = $update->handle(
                reportId: $this->report->id,
                data: $reportDto,
                items: $itemDtos,
                actorId: auth()->id()
            );

            session()->flash('ok', 'Report updated.');
            $this->redirectRoute('verification.show', $this->report->id);
        } else {
            // Create new
            $created = $create->handle(
                data: $reportDto,
                items: $itemDtos,
                creatorId: auth()->id()
            );

            session()->flash('ok', 'Report created.');
            $this->redirectRoute('verification.show', $created->id);
        }
    }

    // --- Rendering ----------------------------------------------------------

    public function render()
    {
        return view('livewire.verification.edit');
    }
}
