<?php

namespace App\Livewire\Verification;

use App\Application\Verification\DTOs\DefectData;
use App\Application\Verification\DTOs\ItemData;
use App\Application\Verification\DTOs\ReportData;
use App\Application\Verification\UseCases\CreateReport;
use App\Application\Verification\UseCases\UpdateReport;
use App\Infrastructure\Persistence\Eloquent\Models\DefectCatalog;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Edit extends Component
{
    use AuthorizesRequests;

    public ?VerificationReport $report = null;

    public ?int $pickerForItem = null;

    public string $defectSearch = '';

    public array $catalogResults = [];

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
            'defects' => [],
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
            'items.*.defects' => ['array'],
            'items.*.defects.*.code' => ['nullable', 'string', 'max:64'],
            'items.*.defects.*.name' => ['required_with:items.*.defects.*.quantity', 'string', 'max:191'],
            'items.*.defects.*.severity' => ['nullable', 'in:LOW,MEDIUM,HIGH'],
            'items.*.defects.*.source' => ['nullable', 'in:DAIJO,CUSTOMER,SUPPLIER'],
            'items.*.defects.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.defects.*.notes' => ['nullable', 'string'],
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
                    'defects' => $i->defects->map(fn ($d) => [
                        'id' => $d->id,
                        'code' => $d->code,
                        'name' => $d->name,
                        'severity' => $d->severity->value,
                        'source' => $d->source->value,
                        'quantity' => (float) $d->quantity,
                        'notes' => $d->notes,
                    ])->toArray(),
                ])->toArray();
        }
    }

    // --- Commands -----------------------------------------------------------

    // open picker for a specific item index
    public function openDefectPicker(int $itemIndex): void
    {
        $this->pickerForItem = $itemIndex;
        $this->defectSearch = '';
        $this->catalogResults = $this->searchCatalog('');
    }

    // close picker
    public function closeDefectPicker(): void
    {
        $this->pickerForItem = null;
        $this->defectSearch = '';
        $this->catalogResults = [];
    }

    // live-search in catalog
    public function updatedDefectSearch(): void
    {
        $this->catalogResults = $this->searchCatalog($this->defectSearch);
    }

    private function searchCatalog(string $term): array
    {
        $q = DefectCatalog::query()->where('active', true);
        if (trim($term) !== '') {
            $s = "%{$term}%";
            $q->where(fn ($qq) => $qq->where('code', 'like', $s)->orWhere('name', 'like', $s));
        }

        return $q->orderBy('code')->limit(15)->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'code' => $r->code,
                'name' => $r->name,
                'severity' => $r->default_severity?->value ?? (string) $r->default_severity,
                'source' => $r->default_source?->value ?? (string) $r->default_source,
                'quantity' => (float) $r->default_quantity,
                'notes' => $r->notes,
            ])->toArray();
    }

    // inject selected catalog defect into item defects
    public function pickCatalogDefect(int $catalogId): void
    {
        if ($this->pickerForItem === null) {
            return;
        }

        $c = DefectCatalog::findOrFail($catalogId);
        $row = [
            'code' => $c->code,
            'name' => $c->name,
            'severity' => $c->default_severity?->value ?? (string) $c->default_severity,
            'source' => $c->default_source?->value ?? (string) $c->default_source,
            'quantity' => (float) $c->default_quantity,
            'notes' => $c->notes,
        ];

        $this->items[$this->pickerForItem]['defects'] = array_values(array_merge(
            $this->items[$this->pickerForItem]['defects'] ?? [], [$row]
        ));

        $this->closeDefectPicker();
    }

    public function addItem(): void
    {
        $this->items[] = ['name' => '', 'notes' => null, 'amount' => 0];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function addDefect(int $itemIndex): void
    {
        $this->items[$itemIndex]['defects'][] = [
            'code' => null, 'name' => '', 'severity' => 'LOW', 'source' => 'DAIJO', 'quantity' => null, 'notes' => null,
        ];
    }

    public function removeDefect(int $itemIndex, int $defectIndex): void
    {
        unset($this->items[$itemIndex]['defects'][$defectIndex]);
        $this->items[$itemIndex]['defects'] = array_values($this->items[$itemIndex]['defects'] ?? []);
    }

    public function save(CreateReport $create, UpdateReport $update): void
    {
        // dd($this->items);
        $this->validate();

        // Build DTOs
        $reportDto = ReportData::fromArray($this->form);
        $itemDtos = array_map(function ($row) {
            // map defects
            $row['defects'] = array_map(
                fn ($d) => DefectData::fromArray($d),
                $row['defects'] ?? []
            );

            return ItemData::fromArray($row);
        }, $this->items);

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
