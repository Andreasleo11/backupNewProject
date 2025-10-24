<?php

namespace App\Livewire\Verification;

use App\Application\Verification\DTOs\DefectData;
use App\Application\Verification\DTOs\ItemData;
use App\Application\Verification\DTOs\ReportData;
use App\Application\Verification\UseCases\CreateReport;
use App\Application\Verification\UseCases\UpdateReport;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationDraft;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Wizard extends Component
{
    use AuthorizesRequests;

    public ?VerificationReport $report = null;

    public int $step = 1;

    public ?int $activeItem = null;

    public string $defaultCurrency = 'IDR';

    public array $form = [
        'rec_date' => null,
        'verify_date' => null,
        'customer' => null,
        'invoice_number' => null,
        'meta' => [],
    ];

    public $items = [];

    // autosave props
    public ?string $lastAutosaveAt = null;

    public bool $autosaveEnabled = true;

    public int $autosaveMs = 15000;

    /* ---------- Validation per step ---------- */
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

    protected function reportKey(): string
    {
        return $this->report?->id ? (string) $this->report->id : 'new';
    }

    public function mount(?VerificationReport $report): void
    {
        $this->report = $report;
        if ($report?->exists) {
            $this->authorize('update', $report);
            $this->form = [
                'rec_date' => optional($report->rec_date)?->format('Y-m-d'),
                'rec_date' => optional($report->rec_date)?->format('Y-m-d'),
                'customer' => $report->customer,
                'invoice_number' => $report->invoice_number,
                'meta' => $report->meta ?? [],
            ];

            $this->items = $report->items->map(fn ($i) => [
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
            $this->activeItem = count($this->items) ? 0 : null;
        } else {
            $this->items = [];
            $this->activeItem = null;
        }

        $this->recoverDraftIfAny();
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

    public function goToStep(int $s): void
    {
        try {
            // Guard transitions forward with validation
            if ($s > $this->step) {
                if ($this->step === 1) {
                    $this->validate($this->rulesHeader());
                }
                if ($this->step === 2) {
                    $this->validate($this->rulesItems());
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('validation-errors-updated', errors: $e->errors());
            throw $e;
        }

        // Backward allowed
        $this->step = max(1, min(3, $s));

        // When entering Step 3, ensure an active item exists
        if ($this->step === 3) {
            if (! count($this->items)) {
                $this->step = 2;

                return;
            }
            $this->activeItem ??= 0;
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

    public function save(CreateReport $create, UpdateReport $update): void
    {
        $this->validate($this->rulesAll());

        $reportDto = ReportData::fromArray($this->form);
        $itemDtos = array_map(function ($row) {
            $row['defects'] = array_map(fn ($d) => DefectData::fromArray($d), $row['defects'] ?? []);

            return ItemData::fromArray($row);
        }, $this->items);

        if ($this->report?->exists) {
            $this->authorize('update', $this->report);
            $updated = $update->handle(reportId: $this->report->id, data: $reportDto, items: $itemDtos, actorId: auth()->id());
            $this->clearDraft();
            $this->redirectRoute('verification.show', $updated->id);
        } else {
            $created = $create->handle(data: $reportDto, items: $itemDtos, creatorId: auth()->id());
            $this->clearDraft();
            $this->redirectRoute('verification.show', $created->id);
        }
    }

    public function render()
    {
        return view('livewire.verification.wizard');
    }
}
