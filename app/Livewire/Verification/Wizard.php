<?php

namespace App\Livewire\Verification;

use App\Application\Verification\DTOs\DefectData;
use App\Application\Verification\DTOs\ItemData;
use App\Application\Verification\DTOs\ReportData;
use App\Application\Verification\UseCases\CreateReport;
use App\Application\Verification\UseCases\UpdateReport;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationDraft;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use App\Livewire\Verification\Concerns\VerificationRules;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\MessageBag;
use Livewire\Attributes\On;
use Livewire\Component;

class Wizard extends Component
{
    use AuthorizesRequests, VerificationRules;

    public ?VerificationReport $report = null;

    public int $step = 1;

    public ?int $activeItem = null;

    public string $defaultCurrency = 'IDR';

    public int $previewVersion = 1;

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

    public bool $isDirty = false;

    protected function messages(): array
    {
        return $this->messagesAll();
    }

    protected function validationAttributes(): array
    {
        return $this->attributesAll();
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
                'verify_date' => optional($report->verify_date)?->format('Y-m-d'),
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

        if (! $this->isDirty) {
            return;
        } // only save when dirty

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
        $this->isDirty = false;
        $this->dispatch('saved-clean');
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

    public function updated($propertyName): void
    {
        $this->isDirty = true;
    }

    #[On('active-item-updated')]
    public function updateActiveItem(?int $index): void
    {
        $this->activeItem = $index;
    }

    public function goToItem(int $i): void
    {
        if (! array_key_exists($i, $this->items)) {
            return;
        }

        if ($this->step === 1) {
            try {
                $this->validate($this->rulesHeader());
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->setErrorBag($e->validator->errors());
                return;
            }
        }

        $this->activeItem = $i;
        $this->step = 2;
        $this->autosaveDraft();
    }

    #[On('go-to-item')]
    public function handleGoToItem(int $index): void
    {
        $this->goToItem($index);
    }

    #[On('go-to-step')]
    public function goToStep(int $step): void
    {
        $step = max(1, min(3, $step));

        // Guard transitions forward with validation
        if ($step > $this->step) {
            if ($this->step === 1) {
                $this->validate($this->rulesHeader());
            }
            if ($this->step === 2) {
                try {
                    $this->validate($this->rulesItems());
                    $this->validate($this->rulesDefects());
                } catch (\Illuminate\Validation\ValidationException $e) {
                    $errors = $e->validator->errors()->toArray();
                    $this->setErrorBag($e->validator->errors());
                    $this->autoSelectFirstInvalidItem($errors);
                    throw $e;
                }
            }
        }
        $this->step = $step;

        // When entering Step 2, ensure an active item exists
        if ($this->step === 2) {
            if (count($this->items) === 0) {
                $this->step = 1;
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
        $this->dispatch('request-validate', step: $this->step);
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
        $this->autosaveDraft();
    }

    #[On('step-valid')]
    public function onStepValid(int $step): void
    {
        if ($step !== $this->step) {
            return;
        }

        $this->resetErrorBag();

        $this->step = min(3, $this->step + 1);

        if ($this->step === 3) {
            $this->previewVersion++;
        }

        $this->autosaveDraft();
    }

    #[On('step-invalid')]
    public function onStepInvalid(int $step, array $errors): void
    {
        if ($step !== $this->step) {
            return;
        }

        $this->setErrorBag(new \Illuminate\Support\MessageBag($errors));
        $this->autoSelectFirstInvalidItem($errors);
    }

    private function autoSelectFirstInvalidItem(array $errors): void
    {
        foreach ($errors as $key => $messages) {
            if (str_starts_with($key, 'items.')) {
                $parts = explode('.', $key);
                $index = isset($parts[1]) ? (int)$parts[1] : null;
                if ($index !== null && array_key_exists($index, $this->items)) {
                    $this->activeItem = $index;
                    $this->dispatch('active-item-updated', index: $index);

                    $tab = str_contains($key, '.defects.') ? 'defects' : 'details';
                    $this->dispatch('switch-tab', tab: $tab);
                    break;
                }
            }
        }
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
