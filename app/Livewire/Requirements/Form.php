<?php

// app/Livewire/Requirements/Form.php

namespace App\Livewire\Requirements;

use App\Models\Requirement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
class Form extends Component
{
    public ?Requirement $requirement = null; // null = create mode

    public ?bool $code_is_unique = null;

    // Form fields
    public string $code = '';

    public string $name = '';

    public ?string $description = null;

    public array $allowed_mimetypes = [];

    public array $selected_presets = [];

    public array $custom_mimes = [];

    public string $custom_input = '';

    public int $min_count = 1;

    public ?int $validity_days = null;

    public string $frequency = 'once'; // once|yearly|quarterly|monthly

    public bool $requires_approval = false;

    public array $usage = ['assignments' => 0, 'uploads' => 0];

    public string $delete_confirm_input = '';

    public bool $delete_in_progress = false;

    protected function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:64', 'regex:/^[A-Z0-9_\-\/]+$/', Rule::unique('requirements', 'code')->ignore($this->requirement?->id)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'allowed_mimetypes' => ['array'],
            'allowed_mimetypes.*' => ['string', 'max:255'],
            'custom_mimes' => ['array'],
            'custom_mimes.*' => ['string', 'max:255'],
            'min_count' => ['required', 'integer', 'min:1', 'max:20'],
            'validity_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'frequency' => ['required', Rule::in(['once', 'yearly', 'quarterly', 'monthly'])],
            'requires_approval' => ['boolean'],
        ];
    }

    protected $validationAttributes = [
        'code' => 'Code',
        'name' => 'Name',
        'allowed_mimetypes' => 'Allowed file types',
        'min_count' => 'Minimum files',
        'validity_days' => 'Validity (days)',
    ];

    public function mount(?Requirement $requirement): void
    {
        $this->requirement = $requirement;
        if ($requirement?->exists) {
            $this->code = $requirement->code;
            $this->name = $requirement->name;
            $this->description = $requirement->description;
            $this->allowed_mimetypes = $requirement->allowed_mimetypes ?? [];
            $this->min_count = (int) $requirement->min_count;
            $this->validity_days = $requirement->validity_days;
            $this->frequency = $requirement->frequency;
            $this->requires_approval = (bool) $requirement->requires_approval;
            $this->refreshUsageCounts();
        }

        $this->inferPresetsFromMimes();
    }

    public function refreshUsageCounts(): void
    {
        if (! $this->requirement?->exists) {
            $this->usage = ['assignments' => 0, 'uploads' => 0];

            return;
        }

        // use loadCount for efficiency
        $this->requirement->loadCount(['assignments', 'uploads']);
        $this->usage = [
            'assignments' => (int) $this->requirement->assignments_count,
            'uploads' => (int) $this->requirement->uploads_count,
        ];
    }

    public function dehydrate()
    {
        // normalize whitespace
        $this->code = strtoupper(trim($this->code));
        $this->name = trim($this->name);
    }

    public function checkCodeUnique(): void
    {
        $q = Requirement::query()->where('code', $this->code);
        if ($this->requirement?->exists) {
            $q->where('id', '!=', $this->requirement->id);
        }
        $this->code_is_unique = ! $q->exists();
    }

    /** Central map: preset → { label, apps, mimes } */
    public function mimePresets(): array
    {
        return config('requirements.mime_presets');
    }

    /** Rebuild allowed_mimetypes whenever selection changes */
    public function updatedSelectedPresets(): void
    {
        dd('test');
        $this->rebuildAllowedMimes();
    }

    public function togglePreset(string $key): void
    {
        if (in_array($key, $this->selected_presets, true)) {
            $this->selected_presets = array_values(array_filter(
                $this->selected_presets, fn ($k) => $k !== $key
            ));
        } else {
            $this->selected_presets[] = $key;
        }
        $this->rebuildAllowedMimes();
    }

    public function addCustom(): void
    {
        $v = trim($this->custom_input);
        if ($v === '') {
            return;
        }

        // quick aliases for user-friendly short entries
        $aliases = [
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'csv' => 'text/csv',
            'txt' => 'text/plain',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            '7z' => 'application/x-7z-compressed',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];
        $mime = $aliases[strtolower($v)] ?? $v;

        if (! in_array($mime, $this->custom_mimes, true)) {
            $this->custom_mimes[] = $mime;
            $this->custom_input = '';
            $this->rebuildAllowedMimes();
        }
    }

    public function removeCustom(int $i): void
    {
        unset($this->custom_mimes[$i]);
        $this->custom_mimes = array_values($this->custom_mimes);
        $this->rebuildAllowedMimes();
    }

    private function inferPresetsFromMimes(): void
    {
        $all = $this->allowed_mimetypes;
        $presets = $this->mimePresets();
        $selected = [];
        $covered = [];

        foreach ($presets as $key => $p) {
            $want = $p['mimes'];
            $isSubset = empty(array_diff($want, $all)); // all preset mimes are present
            if ($isSubset) {
                $selected[] = $key;
                $covered = array_merge($covered, $want);
            }
        }

        $this->selected_presets = $selected;

        // leftovers become custom
        $this->custom_mimes = array_values(array_diff($all, $covered));
        $this->rebuildAllowedMimes();
    }

    private function rebuildAllowedMimes(): void
    {
        $presets = $this->mimePresets();
        $m = [];

        foreach ($this->selected_presets as $key) {
            foreach ($presets[$key]['mimes'] ?? [] as $mm) {
                $m[] = $mm;
            }
        }
        foreach ($this->custom_mimes as $mm) {
            $m[] = $mm;
        }
        $this->allowed_mimetypes = array_values(array_unique($m));
    }

    // Human text for frequency
    public function frequencyLabel(): string
    {
        return [
            'once' => 'One-time',
            'yearly' => 'Every year',
            'quarterly' => 'Every quarter',
            'monthly' => 'Every month',
        ][$this->frequency] ?? ucfirst($this->frequency);
    }

    // Computed: selected preset metadata (label + apps)
    public function getSelectedPresetMetaProperty(): array
    {
        $presets = $this->mimePresets();
        $out = [];
        foreach ($this->selected_presets as $key) {
            if (! isset($presets[$key])) {
                continue;
            }
            $out[] = [
                'key' => $key,
                'label' => $presets[$key]['label'],
                'apps' => $presets[$key]['apps'],
            ];
        }

        return $out;
    }

    // Small English sentence for “what counts”
    public function getPolicyLineProperty(): string
    {
        $parts = [];
        $parts[] = "Min {$this->min_count} file".($this->min_count > 1 ? 's' : '');
        if ($this->validity_days) {
            $parts[] = "valid {$this->validity_days} day(s)";
        }
        $parts[] = $this->frequencyLabel();
        if ($this->requires_approval) {
            $parts[] = 'requires approval';
        }

        return implode(' · ', $parts);
    }

    public function selectAllPresets(): void
    {
        $this->selected_presets = array_keys($this->mimePresets());
        $this->rebuildAllowedMimes(); // keep allowed_mimetypes in sync
    }

    public function clearPresets(): void
    {
        $this->selected_presets = [];
        $this->rebuildAllowedMimes();
    }

    /** Optional: remove one badge quickly */
    public function removePreset(string $key): void
    {
        $this->selected_presets = array_values(array_filter($this->selected_presets, fn ($k) => $k !== $key));
        $this->rebuildAllowedMimes();
    }

    /** Auto-format CODE as user types */
    public function updatedCode(): void
    {
        // Uppercase; replace spaces with _
        $this->code = strtoupper(str_replace(' ', '_', $this->code));
    }

    public function deleteRequirement(): void
    {
        if (! $this->requirement?->exists) {
            return;
        }

        // Must type the exact CODE to proceed
        if (trim($this->delete_confirm_input) !== $this->requirement->code) {
            $this->addError('delete_confirm_input', 'Type the exact code to confirm.');

            return;
        }

        // Re-check usage just in case it changed
        $this->refreshUsageCounts();
        if ($this->usage['assignments'] > 0 || $this->usage['uploads'] > 0) {
            $this->addError('delete_confirm_input',
                'Cannot delete while assigned or with uploads. Detach assignments and remove uploads first.');

            return;
        }

        $this->delete_in_progress = true;

        DB::transaction(function () {
            $this->requirement->delete();
        });

        // Close modal in the browser, show toast, and redirect
        $this->dispatch('hide-delete-modal');
        session()->flash('success', 'Requirement deleted.');
        $this->redirectRoute('requirements.index');
    }

    public function save()
    {
        $this->validate();

        $data = [
            'code' => strtoupper($this->code),
            'name' => $this->name,
            'description' => $this->description ?: null,
            'allowed_mimetypes' => $this->allowed_mimetypes,
            'min_count' => $this->min_count,
            'validity_days' => $this->validity_days,
            'frequency' => $this->frequency,
            'requires_approval' => $this->requires_approval,
        ];

        DB::transaction(function () use (&$data) {
            if ($this->requirement?->exists) {
                $this->requirement->update($data);
                session()->flash('success', 'Requirement updated.');
            } else {
                $this->requirement = Requirement::create($data);
                session()->flash('success', 'Requirement created.');
            }
        });

        return redirect()->route('requirements.index');
    }

    #[Title('Requirement Form')]
    public function render()
    {
        return view('livewire.requirements.form');
    }
}
