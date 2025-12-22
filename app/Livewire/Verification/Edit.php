<?php

namespace App\Livewire\Verification;

use App\Application\Verification\DTOs\DefectData;
use App\Application\Verification\DTOs\ItemData;
use App\Application\Verification\DTOs\ReportData;
use App\Application\Verification\UseCases\CreateReport;
use App\Application\Verification\UseCases\UpdateReport;
use App\Infrastructure\Persistence\Eloquent\Models\DefectCatalog;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationDraft;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Edit extends Component
{
    use AuthorizesRequests;

    public ?VerificationReport $report = null;

    /** Wizard state */
    public int $step = 1;                // 1=Header, 2=Items, 3=Defects

    public ?int $activeItem = null;      // which item is being edited in Step 3

    public ?int $pickerForItem = null;

    public string $defectSearch = '';

    public array $catalogResults = [];

    public string $defaultCurrency = 'IDR';

    public bool $pasteDialog = false;

    public string $pasteBuffer = '';

    /** @var array{title:string,description:?string,meta?:array} */
    public array $form = [
        'rec_date' => null,
        'verify_date' => null,
        'customer' => null,
        'invoice_number' => null,
        'meta' => [],
    ];

    /** @var array<int, array{name:string,notes:?string,amount:float|int|string}> */
    public array $items = [];

    /** Wizard UI */
    public ?string $lastAutosaveAt = null;

    public bool $autosaveEnabled = true;

    public int $autosaveMs = 15000; // 15s interval

    protected function reportKey(): string
    {
        return $this->report?->id ? (string) $this->report->id : 'new';
    }

    public function autosaveDraft(): void
    {
        if (! $this->autosaveEnabled) {
            return;
        }

        VerificationDraft::updateOrCreate(
            ['user_id' => auth()->id(), 'report_key' => $this->reportKey()],
            ['payload' => [
                'form' => $this->form,
                'items' => $this->items,
                'defaultCurrency' => $this->defaultCurrency,
                'step' => $this->step,
                'activeItem' => $this->activeItem,
            ]]
        );

        $this->lastAutosaveAt = now()->format('H:i:s');
    }

    protected function recoverDraftIfAny(): void
    {
        $draft = VerificationDraft::where('user_id', auth()->id())
            ->where('report_key', $this->reportKey())
            ->latest('updated_at')
            ->first();

        if (! $draft) {
            return;
        }

        $payload = $draft->payload ?? [];

        $this->form = $payload['form'] ?? $this->form;
        $this->items = $payload['items'] ?? $this->items;
        $this->defaultCurrency = $payload['defaultCurrency'] ?? $this->defaultCurrency;
        $this->step = $payload['step'] ?? $this->step;
        $this->activeItem = $payload['activeItem'] ?? $this->activeItem;

        session()->flash('ok', 'Recovered your auto-saved draft.');
    }

    public function clearDraft(): void
    {
        VerificationDraft::where('user_id', auth()->id())
            ->where('report_key', $this->reportKey())
            ->delete();

        $this->dispatch('draft-cleared', message: 'Draft cleared!');
    }

    /* ---------------- Validation rules (split per step) ---------------- */

    protected function rulesHeader(): array
    {
        return [
            'form.rec_date' => ['required', 'date'],
            'form.verify_date' => ['required', 'date', 'after_or_equal:form.rec_date'],
            'form.customer' => ['required', 'string', 'max:191'],
            'form.invoice_number' => ['required', 'string', 'max:191'],
            'form.meta' => ['nullable', 'array'],
        ];
    }

    protected function rulesItems(): array
    {
        return [
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

    protected function rulesDefects(): array
    {
        return [
            'items.*.defects' => ['array'],
            'items.*.defects.*.code' => ['nullable', 'string', 'max:64'],
            'items.*.defects.*.name' => ['required_with:items.*.defects.*.quantity', 'string', 'max:191'],
            'items.*.defects.*.severity' => ['nullable', 'in:LOW,MEDIUM,HIGH'],
            'items.*.defects.*.source' => ['nullable', 'in:DAIJO,CUSTOMER,SUPPLIER'],
            'items.*.defects.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.defects.*.notes' => ['nullable', 'string'],
        ];
    }

    protected function rulesAll(): array
    {
        return array_merge($this->rulesHeader(), $this->rulesItems(), $this->rulesDefects());
    }

    /* ---------------- Lifecycle ---------------- */

    public function mount(?VerificationReport $report): void
    {

        $this->report = $report;

        if ($report?->exists) {
            $this->authorize('update', $report);

            $this->form = [
                'rec_date' => optional($report->rec_date)?->format('Y-m-d'),
                'verify_date' => optional($report->verify_date)?->format('Y-m-d'),
                'customer' => $report->customer,
                'invoice_number' => $report->invoice_number,
                'meta' => $report->meta ?? [],
            ];

            $this->items = $report->items->map(function ($i) {
                return [
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
                ];
            })->toArray();

            $this->activeItem = count($this->items) ? 0 : null;
        } else {
            // New report → start wizard at step 1 with empty items
            $this->items = [];
            $this->activeItem = null;
        }

        $this->recoverDraftIfAny();
    }

    /* ---------------- Wizard actions ---------------- */

    public function goToStep(int $s): void
    {
        // Guard transitions forward with validation
        if ($s > $this->step) {
            if ($this->step === 1) {
                $this->validate($this->rulesHeader());
            }
            if ($this->step === 2) {
                $this->validate($this->rulesItems());
            }
        }
        // Backward is always allowed
        $this->step = max(1, min(3, $s));

        // When entering Step 3, ensure an active item exists
        if ($this->step === 3) {
            if (count($this->items) === 0) {
                $this->step = 2;

                return;
            }
            if ($this->activeItem === null || ! array_key_exists($this->activeItem, $this->items)) {
                $this->activeItem = 0;
            }
        }

        $this->autosaveDraft();

    }

    public function nextStep(): void
    {
        $this->goToStep($this->step + 1);
    }

    public function prevStep(): void
    {
        $this->goToStep($this->step - 1);
    }

    public function goToItem(int $i): void
    {
        if (! array_key_exists($i, $this->items)) {
            return;
        }
        $this->activeItem = $i;
        $this->step = 3;
    }

    /* ---------------- Catalog picker ---------------- */

    public function openDefectPicker(int $itemIndex): void
    {
        $this->pickerForItem = $itemIndex;
        $this->defectSearch = '';
        $this->catalogResults = $this->searchCatalog('');
    }

    public function closeDefectPicker(): void
    {
        $this->pickerForItem = null;
        $this->defectSearch = '';
        $this->catalogResults = [];
    }

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

        return $q->orderBy('code')->limit(15)->get()->map(fn ($r) => [
            'id' => $r->id,
            'code' => $r->code,
            'name' => $r->name,
            'severity' => $r->default_severity?->value ?? (string) $r->default_severity,
            'source' => $r->default_source?->value ?? (string) $r->default_source,
            'quantity' => (float) $r->default_quantity,
            'notes' => $r->notes,
        ])->toArray();
    }

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
        $this->items[$this->pickerForItem]['defects'] =
            array_values(array_merge($this->items[$this->pickerForItem]['defects'] ?? [], [$row]));

        $this->closeDefectPicker();
    }

    /* ---------------- Items & defects commands ---------------- */

    public function addItem(): void
    {
        $this->items[] = [
            'part_name' => '',
            'rec_quantity' => 0,
            'verify_quantity' => 0,
            'can_use' => 0,
            'cant_use' => 0,
            'price' => 0,
            'currency' => $this->defaultCurrency ?: 'IDR',
            'defects' => [],
        ];
        if ($this->activeItem === null) {
            $this->activeItem = 0;
        }
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        if ($this->activeItem !== null) {
            if (! array_key_exists($this->activeItem, $this->items)) {
                $this->activeItem = count($this->items) ? 0 : null;
            }
        }
    }

    public function addDefect(int $itemIndex): void
    {
        $this->items[$itemIndex]['defects'][] = [
            'code' => null,
            'name' => '',
            'severity' => 'LOW',
            'source' => 'DAIJO',
            'quantity' => null,
            'notes' => null,
        ];
    }

    public function removeDefect(int $itemIndex, int $defectIndex): void
    {
        unset($this->items[$itemIndex]['defects'][$defectIndex]);
        $this->items[$itemIndex]['defects'] = array_values($this->items[$itemIndex]['defects'] ?? []);

    }

    public function applyDefaultCurrency(): void
    {
        foreach ($this->items as &$r) {
            $r['currency'] = $this->defaultCurrency ?: ($r['currency'] ?? 'IDR');
        }
        unset($r);
    }

    public function fillCantUseFromDefects(int $i): void
    {
        $sum = collect($this->items[$i]['defects'] ?? [])->sum(fn ($d) => (float) ($d['quantity'] ?? 0));
        $this->items[$i]['cant_use'] = round((float) $sum, 4);
    }

    public function fillAllCantUseFromDefects(): void
    {
        foreach ($this->items as $i => $_) {
            $this->fillCantUseFromDefects($i);
        }
    }

    public function insertItemBelow(int $i): void
    {
        $empty = [
            'part_name' => '',
            'rec_quantity' => 0,
            'verify_quantity' => 0,
            'can_use' => 0,
            'cant_use' => 0,
            'price' => 0,
            'currency' => $this->defaultCurrency ?: 'IDR',
            'defects' => [],
        ];
        array_splice($this->items, $i + 1, 0, [$empty]);
    }

    public function duplicateItem(int $i): void
    {
        $copy = $this->items[$i];
        array_splice($this->items, $i + 1, 0, [$copy]);
    }

    public function moveItemUp(int $i): void
    {
        if ($i > 0) {
            [$this->items[$i - 1], $this->items[$i]] = [$this->items[$i], $this->items[$i - 1]];
        }
    }

    public function moveItemDown(int $i): void
    {
        if ($i < count($this->items) - 1) {
            [$this->items[$i + 1], $this->items[$i]] = [$this->items[$i], $this->items[$i + 1]];
        }
    }

    /* ---------------- Paste handler ---------------- */

    public function applyPastedItems(): void
    {
        $rows = preg_split('/\r\n|\r|\n/', trim($this->pasteBuffer));
        foreach ($rows as $line) {
            if ($line === '') {
                continue;
            }
            $cols = str_getcsv($line, (str_contains($line, "\t") ? "\t" : ','));
            $this->items[] = [
                'part_name' => (string) ($cols[0] ?? ''),
                'rec_quantity' => (float) ($cols[1] ?? 0),
                'verify_quantity' => (float) ($cols[2] ?? 0),
                'can_use' => (float) ($cols[3] ?? 0),
                'cant_use' => (float) ($cols[4] ?? 0),
                'price' => (float) ($cols[5] ?? 0),
                'currency' => (string) ($cols[6] ?? ($this->defaultCurrency ?: 'IDR')),
                'defects' => [],
            ];
        }
        $this->pasteBuffer = '';
        $this->pasteDialog = false;
        if ($this->activeItem === null && count($this->items)) {
            $this->activeItem = 0;
        }
    }

    /* ---------------- Persist ---------------- */

    public function save(CreateReport $create, UpdateReport $update): void
    {
        // For Save (finish), validate everything
        $this->validate($this->rulesAll());

        $reportDto = ReportData::fromArray($this->form);
        $itemDtos = array_map(function ($row) {
            $row['defects'] = array_map(fn ($d) => DefectData::fromArray($d), $row['defects'] ?? []);

            return ItemData::fromArray($row);
        }, $this->items);

        if ($this->report?->exists) {
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
            $created = $create->handle(
                data: $reportDto,
                items: $itemDtos,
                creatorId: auth()->id()
            );
            session()->flash('ok', 'Report created.');
            $this->redirectRoute('verification.show', $created->id);
        }

        $this->clearDraft();

    }

    // --- Rendering ----------------------------------------------------------

    public function render()
    {
        return view('livewire.verification.edit');
    }
}
