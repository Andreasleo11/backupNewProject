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
        'rec_date' => null,
        'verify_date' => null,
        'customer' => null,
        'invoice_number' => null,
        'meta' => [],
    ];

    /** @var array<int, array{name:string,notes:?string,amount:float|int|string}> */
    public array $items = [
        [
            'part_name' => '',
            'rec_quantity' => 0,
            'verify_quantity' => 0,
            'can_use' => 0,
            'cant_use' => 0,
            'price' => 0,
            'currency' => 'IDR',
        ],
    ];

    // --- Validation ---------------------------------------------------------

    protected function rules(): array
    {
        return [
            'form.rec_date' => ['required', 'date'],
            'form.verify_date' => ['required', 'date', 'after_or_equal:form.rec_date'],
            'form.customer' => ['required', 'string', 'max:191'],
            'form.invoice_number' => ['required', 'string', 'max:191'],
            'form.meta' => ['nullable', 'array'],

            'items' => ['array', 'min:1'],
            'items.*.part_name' => ['required', 'string', 'max:255'],
            'items.*.rec_quantity' => ['required', 'numeric', 'min:0'],
            'items.*.verify_quantity' => ['required', 'numeric', 'min:0'],
            'items.*.can_use' => ['required', 'numeric', 'min:0'],
            'items.*.cant_use' => ['required', 'numeric', 'min:0'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.currency' => ['required', 'string', 'max:10'],
        ];
    }

    // --- Lifecycle ----------------------------------------------------------

    public function mount(?VerificationReport $report): void
    {
        $this->report = $report;

        if ($report?->exists) {
            // Gate: only creator can edit while DRAFT
            $this->authorize('update', $report);

            $this->form = [
                'rec_date' => optional($report->rec_date)?->format('Y-m-d'),
                'verify_date' => optional($report->verify_date)?->format('Y-m-d'),
                'customer' => $report->customer,
                'invoice_number' => $report->invoice_number,
                'meta' => $report->meta ?? [],
            ];

            $this->items = $report->items
                ->map(fn ($i) => [
                    'part_name' => $i->part_name,
                    'rec_quantity' => (float) $i->rec_quantity,
                    'verify_quantity' => (float) $i->verify_quantity,
                    'can_use' => (float) $i->can_use,
                    'cant_use' => (float) $i->cant_use,
                    'price' => (float) $i->price,
                    'currency' => $i->currency,
                ])
                ->toArray();
        }
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
