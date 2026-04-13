<?php

namespace App\Livewire\Overtime;

use App\Domain\Overtime\Models\OvertimeForm;
use App\Domain\Overtime\Models\OvertimeFormDetail;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

#[Layout('new.layouts.app')]
class BulkImport extends Component
{
    use WithFileUploads;

    public $file;
    public array $stagedData = [];
    public array $groupedHeaders = [];
    public array $departmentsMap = []; // dept_code -> ['id', 'name']
    public array $employeesMap = [];   // NIK -> {name, branch, dept_code}

    public bool $isAnalyzing = false;
    public bool $isReady = false;
    public int $totalValid = 0;
    public int $totalErrors = 0;
    public string $importMode = 'auto'; // 'auto' = detect dept from employee, 'manual' = columns in file

    // Integrity Guard State
    public bool $isIntegrityChecked = false;
    public array $integrityResults = [];
    public bool $isCheckingPayroll = false;
    public bool $isSubmitting = false;
    public ?string $activeFilter = null;

    public function downloadTemplate()
    {
        // Auto-detect mode template (matches the admin's daily reference format)
        $headers = [
            'EMPLOYEE ID', 
            'OVERTIME DATE (YYYY-MM-DD)', 
            'JOB DESCRIPTION',
            'START DATE (YYYY-MM-DD)', 
            'START TIME (HH:MM)', 
            'END DATE (YYYY-MM-DD)', 
            'END TIME (HH:MM)', 
            'BREAK TIME (MINUTES)', 
            'REMARK'
        ];
        
        $callback = function() use($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fputcsv($file, [
                '07317', 
                now()->format('Y-m-d'), 
                'MATERIAL HANDLE SHIFT 1, SABTU PRODUKSI FULL',
                now()->format('Y-m-d'), 
                '12:30', 
                now()->format('Y-m-d'), 
                '15:30', 
                '30', 
                ''
            ]);
            fclose($file);
        };

        return response()->streamDownload($callback, 'overtime_bulk_template.csv', [
            'Content-type' => 'text/csv',
        ]);
    }

    public function updatedFile()
    {
        $this->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        $this->processFile();
    }

    public function removeRow(int $index)
    {
        unset($this->stagedData[$index]);
        $this->stagedData = array_values($this->stagedData);
        $this->analyzeData();
    }

    public function processFile()
    {
        $this->isAnalyzing = true;
        $this->stagedData = [];

        try {
            $data = Excel::toArray(new class {}, $this->file->getRealPath());
            $rows = $data[0] ?? [];

            // Detect mode from header row
            // If col 0 header is 'EMPLOYEE ID' or 'NIK' -> auto-detect mode
            // If col 0 header is 'BRANCH' -> legacy manual mode
            $headerRow = array_map(fn($v) => strtoupper(trim((string)$v)), $rows[0] ?? []);
            $firstCol = $headerRow[0] ?? '';
            $this->importMode = in_array($firstCol, ['EMPLOYEE ID', 'NIK', 'EMPLOYEE_ID']) ? 'auto' : 'manual';

            // Slice header
            $rows = array_slice($rows, 1);

            foreach ($rows as $index => $row) {
                if (empty(array_filter($row))) continue;

                if ($this->importMode === 'auto') {
                    // Format: NIK | Overtime Date | Job Desc | Start Date | Start Time | End Date | End Time | Break | Remark
                    $this->stagedData[] = [
                        'original_index' => $index + 2,
                        'nik'            => $this->normalizeNik($row[0] ?? ''),
                        'overtime_date'  => $this->parseDate($row[1] ?? ''),
                        'task'           => trim($row[2] ?? ''),
                        'start_date'     => $this->parseDate($row[3] ?? ''),
                        'start_time'     => $this->parseTime($row[4] ?? ''),
                        'end_date'       => $this->parseDate($row[5] ?? ''),
                        'end_time'       => $this->parseTime($row[6] ?? ''),
                        'break'          => trim((string)($row[7] ?? '0')),
                        'remarks'        => trim($row[8] ?? ''),

                        // To be resolved from DB
                        'branch'         => null,
                        'department'     => null,
                        'dept_id'        => null,
                        'session_type'   => null,
                        'is_after_hour'  => 0,
                        'employee_name'  => null,
                        'is_valid'       => false,
                        'errors'         => [],
                        'group_signature'=> null,
                    ];
                } else {
                    // Legacy format: Branch | Department | Session | NIK | OT Date | Start Date | Start Time | End Date | End Time | Break | Task | Remarks
                    $this->stagedData[] = [
                        'original_index' => $index + 2,
                        'branch'         => trim($row[0] ?? ''),
                        'department'     => trim($row[1] ?? ''),
                        'session_type'   => trim($row[2] ?? ''),
                        'nik'            => $this->normalizeNik($row[3] ?? ''),
                        'overtime_date'  => $this->parseDate($row[4] ?? ''),
                        'start_date'     => $this->parseDate($row[5] ?? ''),
                        'start_time'     => $this->parseTime($row[6] ?? ''),
                        'end_date'       => $this->parseDate($row[7] ?? ''),
                        'end_time'       => $this->parseTime($row[8] ?? ''),
                        'break'          => trim($row[9] ?? '0'),
                        'task'           => trim($row[10] ?? ''),
                        'remarks'        => trim($row[11] ?? ''),
                        'dept_id'        => null,
                        'employee_name'  => null,
                        'is_after_hour'  => 0,
                        'is_valid'       => false,
                        'errors'         => [],
                        'group_signature'=> null,
                    ];
                }
            }

            $this->analyzeData();

        } catch (Throwable $e) {
            $this->dispatch('flash', type: 'error', message: 'Failed to process file: ' . $e->getMessage());
        }

        $this->isAnalyzing = false;
        $this->file = null;
    }

    public function analyzeData()
    {
        // Pre-fetch all departments keyed by dept_no (for auto-detect) and by name (for manual)
        if (empty($this->departmentsMap)) {
            $depts = Department::get();
            foreach ($depts as $d) {
                $this->departmentsMap['by_no'][strtoupper($d->dept_no ?? '')] = ['id' => $d->id, 'name' => $d->name];
                $this->departmentsMap['by_name'][strtoupper($d->name)] = ['id' => $d->id, 'name' => $d->name];
            }
        }

        $niks = array_column($this->stagedData, 'nik');
        $employees = Employee::with('department')->whereIn('nik', $niks)->get();
        foreach ($employees as $emp) {
            $this->employeesMap[$emp->nik] = [
                'name'      => $emp->name,
                'branch'    => $emp->branch,
                'dept_code' => $emp->dept_code,
                'dept_id'   => $emp->department?->id,
                'dept_name' => $emp->department?->name,
            ];
        }

        $this->groupedHeaders = [];
        $this->totalValid = 0;
        $this->totalErrors = 0;

        foreach ($this->stagedData as $i => &$row) {
            $errors = [];

            // 1. Resolve Employee
            $empData = $this->employeesMap[$row['nik']] ?? null;
            if (!$empData) {
                $errors[] = "Employee NIK not found or inactive";
            } else {
                $row['employee_name'] = $empData['name'];
            }

            // 2. Resolve Branch + Department (auto vs manual)
            if ($this->importMode === 'auto') {
                if ($empData) {
                    $row['branch']     = $empData['branch'] ?? 'Jakarta';
                    $row['dept_id']    = $empData['dept_id'];
                    $row['department'] = $empData['dept_name'] ?? 'Unknown';

                    if (!$row['dept_id']) {
                        $errors[] = "Employee has no department assigned in system";
                    }
                }

                // Auto-detect session type from start time
                if ($row['start_time']) {
                    $hour = (int) explode(':', $row['start_time'])[0];
                    // After hour = starts at or after 17:00, OR ends after midnight
                    $row['is_after_hour'] = ($hour >= 17 || $hour < 6) ? 1 : 0;
                    $row['session_type']  = $row['is_after_hour'] ? 'After-Hour' : 'Standard';
                } else {
                    $errors[] = "Invalid Start Time";
                }
            } else {
                // Manual mode: validate branch
                $branchUpper = strtoupper($row['branch'] ?? '');
                if (!in_array($branchUpper, ['JAKARTA', 'KARAWANG'])) {
                    $errors[] = "Invalid Branch (Must be Jakarta or Karawang)";
                } else {
                    $row['branch'] = ucfirst(strtolower($branchUpper));
                }

                // Manual mode: resolve department by name
                $deptUpper = strtoupper($row['department'] ?? '');
                if (isset($this->departmentsMap['by_name'][$deptUpper])) {
                    $row['dept_id']    = $this->departmentsMap['by_name'][$deptUpper]['id'];
                    $row['department'] = $this->departmentsMap['by_name'][$deptUpper]['name'];
                } else {
                    $errors[] = "Unknown Department: " . $row['department'];
                }

                // Manual mode: parse session type
                $sessionUpper = strtoupper($row['session_type'] ?? '');
                if (in_array($sessionUpper, ['1', 'AFTER', 'AFTER HOUR', 'AFTER_HOUR', 'AFTER-HOUR', 'YES', 'Y'])) {
                    $row['is_after_hour'] = 1;
                    $row['session_type'] = 'After-Hour';
                } else {
                    $row['is_after_hour'] = 0;
                    $row['session_type'] = 'Standard';
                }
            }

            // 3. Dates & Times Checks
            if (!$row['overtime_date']) $errors[] = "Invalid Overtime Date";
            if (!$row['start_date'])   $errors[] = "Invalid Start Date";
            if (!isset($errors[0]) && !$row['start_time']) $errors[] = "Invalid Start Time"; // Only if not already flagged above
            if (!$row['end_date'])     $errors[] = "Invalid End Date";
            if (!$row['end_time'])     $errors[] = "Invalid End Time";

            if ($row['start_date'] && $row['end_date']) {
                if (strtotime($row['end_date']) < strtotime($row['start_date'])) {
                    $errors[] = "End Date is before Start Date";
                }
                if ($row['start_date'] === $row['end_date'] && $row['start_time'] && $row['end_time']) {
                    if (strtotime($row['end_time']) <= strtotime($row['start_time'])) {
                        $errors[] = "End time must be after start time on same day";
                    }
                }
            }

            // Note: DB Duplicate check moved to Integrity Guard (runIntegrityCheck)
            // so results can be surfaced as a group before submission.

            $row['errors']   = $errors;
            $row['is_valid'] = count($errors) === 0;

            if ($row['is_valid']) {
                $this->totalValid++;
                // Grouping Signature: dept + session type (per-employee branch is used at creation)
                $sig = md5(($row['branch'] ?? '') . '_' . $row['dept_id'] . '_' . $row['is_after_hour']);
                $row['group_signature'] = $sig;

                if (!isset($this->groupedHeaders[$sig])) {
                    $this->groupedHeaders[$sig] = [
                        'branch'       => $row['branch'],
                        'department'   => $row['department'],
                        'dept_id'      => $row['dept_id'],
                        'session'      => $row['session_type'],
                        'is_after_hour'=> $row['is_after_hour'],
                        'count'        => 0,
                    ];
                }
                $this->groupedHeaders[$sig]['count']++;
            } else {
                $this->totalErrors++;
            }
        }

        $this->isAnalyzing = false;
        $this->isReady = ($this->totalValid > 0 && $this->totalErrors === 0);
        $this->resetIntegrity();
        $this->activeFilter = null; // Reset filter on new analysis
    }

    public function setFilter(?string $sig)
    {
        $this->activeFilter = ($this->activeFilter === $sig) ? null : $sig;
    }

    public function resetIntegrity(): void
    {
        $this->isIntegrityChecked = false;
        $this->integrityResults = [
            'structural' => 'pending',
            'local'      => 'pending',
            'payroll'    => 'pending',
        ];
    }

    public function submitBulk()
    {
        if (!$this->isIntegrityChecked) {
            $this->dispatch('flash', type: 'warning', message: 'Harap jalankan Integrity Guard terlebih dahulu sebelum mendaftarkan data.');
            return;
        }

        if (!$this->isReady) {
            $this->dispatch('flash', type: 'error', message: 'Tidak dapat melanjutkan: Terdapat konflik data dalam staging area.');
            return;
        }

        $this->isSubmitting = true;

        DB::beginTransaction();
        try {
            $createdGroups = 0;

            // 1. Group Valid Rows
            $rowsByGroup = [];
            foreach ($this->stagedData as $row) {
                if ($row['is_valid']) {
                    $rowsByGroup[$row['group_signature']][] = $row;
                }
            }

            foreach ($rowsByGroup as $sig => $rows) {
                $headerData = $this->groupedHeaders[$sig];
                
                // Aggregate description mapping (combine distinct tasks)
                $tasks = collect($rows)->pluck('task')->filter()->unique()->implode(', ');
                $desc = \Illuminate\Support\Str::limit("Bulk Assignment: " . $tasks, 250);

                // Determine if planned (if any start_date is in the future)
                $isPlanned = collect($rows)->contains(function($r) {
                     return $r['start_date'] && \Carbon\Carbon::parse($r['start_date'])->isFuture();
                });

                $form = OvertimeForm::create([
                    'user_id'       => auth()->id(),
                    'dept_id'       => $headerData['dept_id'],
                    'branch'        => $headerData['branch'],
                    'is_after_hour' => $headerData['is_after_hour'],
                    'is_design'     => 0,
                    'is_export'     => 0,
                    'is_planned'    => $isPlanned,
                    'description'   => $desc,
                ]);

                foreach ($rows as $r) {
                    OvertimeFormDetail::create([
                        'header_id'     => $form->id,
                        'NIK'           => $r['nik'],
                        'name'          => $r['employee_name'],
                        'overtime_date' => $r['overtime_date'],
                        'job_desc'      => \Illuminate\Support\Str::limit($r['task'], 250),
                        'start_date'    => $r['start_date'],
                        'start_time'    => $r['start_time'],
                        'end_date'      => $r['end_date'],
                        'end_time'      => $r['end_time'],
                        'break'         => $r['break'],
                        'remarks'       => \Illuminate\Support\Str::limit($r['remarks'], 250),
                    ]);
                }

                // 2. Trigger Approval Flow for each created header
                $context = [
                    'department_id' => (int) $form->dept_id,
                    'branch'        => $form->branch,
                    'is_design'     => false,
                ];

                try {
                    app(\App\Application\Approval\Contracts\Approvals::class)->submit($form, auth()->id(), $context);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Bulk Import Approval Error: ' . $e->getMessage());
                    // We continue even if one fails approval trigger, but it's logged.
                    // Ideally, we'd want to handle this better but since it's a bulk transaction,
                    // the error will actually roll back the whole thing because it's inside the catch(Throwable $e) of the outer block.
                    throw $e; 
                }

                $createdGroups++;
            }

            DB::commit();
            $this->dispatch('flash', type: 'success', message: "$createdGroups Batch Forms generated successfully.");
            return redirect()->route('overtime.index');

        } catch (Throwable $e) {
            DB::rollBack();
            $this->dispatch('flash', type: 'error', message: 'Failed to save bulk data: ' . $e->getMessage());
        } finally {
            $this->isSubmitting = false;
        }
    }


    public function cancel()
    {
        $this->stagedData = [];
        $this->isAnalyzing = false;
        $this->isReady = false;
        $this->resetIntegrity();
    }

    /**
     * Normalize a NIK to a fixed 5-digit zero-padded string.
     * e.g. "7073" -> "07073", "1" -> "00001", "07073" stays "07073"
     */
    private function normalizeNik($value): string
    {
        $nik = trim((string)$value);
        // Strip any non-digit characters (e.g. Excel may store as float like 7073.0)
        if (is_numeric($nik)) {
            $nik = (string)(int)$nik;
        }
        // Zero-pad to 5 digits
        return str_pad($nik, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Parse a date value from Excel.
     * Supports: Excel serial number, dd/mm/yyyy, dd-mm-yyyy, yyyy-mm-dd, and any Carbon-parseable string.
     */
    private function parseDate($value): ?string
    {
        if (empty($value) && $value !== '0') return null;

        // Excel serial number
        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$value)->format('Y-m-d');
            } catch (\Exception) {
                return null;
            }
        }

        $value = trim((string)$value);

        // Try dd/mm/yyyy or dd-mm-yyyy first (admin's primary format)
        if (preg_match('/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})$/', $value, $m)) {
            try {
                return \Carbon\Carbon::createFromFormat('d/m/Y', sprintf('%02d/%02d/%s', $m[1], $m[2], $m[3]))->format('Y-m-d');
            } catch (\Exception) {}
        }

        // Fallback to Carbon generic parse (handles yyyy-mm-dd etc.)
        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Parse a time value from Excel.
     * Supports: Excel fractional day, HH:MM, H:MM, HH:MM:SS, H.MM, 12-hour formats.
     */
    private function parseTime($value): ?string
    {
        if (empty($value) && $value !== '0') return null;

        // Excel fractional time (e.g. 0.5 = 12:00)
        if (is_numeric($value) && (float)$value < 1) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$value)->format('H:i');
            } catch (\Exception) {
                return null;
            }
        }

        $value = trim((string)$value);

        // HH:MM or H:MM (most common admin format)
        if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', $value, $m)) {
            return sprintf('%02d:%02d', (int)$m[1], (int)$m[2]);
        }

        // H.MM (dot separator)
        if (preg_match('/^(\d{1,2})\.(\d{2})$/', $value, $m)) {
            return sprintf('%02d:%02d', (int)$m[1], (int)$m[2]);
        }

        // 12-hour formats (e.g. "5:30 PM")
        try {
            return \Carbon\Carbon::createFromFormat('g:i A', $value)->format('H:i');
        } catch (\Exception) {}

        return null;
    }

    public function render()
    {
        return view('livewire.overtime.bulk-import');
    }

    /**
     * Integrity Guard Logic
     * Mirrors the manual Form.php integrity checks for consistency.
     */
    public function runIntegrityCheck(): void
    {
        if ($this->isCheckingPayroll) return;

        $this->isIntegrityChecked = false;
        $this->integrityResults = [
            'structural' => 'loading',
            'local'      => 'pending',
            'payroll'    => 'pending',
        ];

        // 1. Structural Check
        $hasStructuralError = false;
        foreach ($this->stagedData as $row) {
            if (count($row['errors']) > 0) {
                $hasStructuralError = true;
                break;
            }
        }

        if ($hasStructuralError) {
            $this->integrityResults['structural'] = 'failed';
            $this->dispatch('flash', type: 'error', message: 'Please resolve errors in the staging table first.');
            return;
        }
        $this->integrityResults['structural'] = 'passed';

        // 2. Local Duplicate Check
        $this->integrityResults['local'] = 'loading';
        $hasLocalConflict = false;
        
        // Batch check for speed
        $niks = array_column($this->stagedData, 'nik');
        $dates = array_column($this->stagedData, 'overtime_date');

        $existing = OvertimeFormDetail::query()
            ->whereIn('NIK', $niks)
            ->whereIn('overtime_date', $dates)
            ->whereNull('payroll_voucher_id') // Only check local drafts/pending
            ->get(['NIK', 'overtime_date'])
            ->map(fn($d) => $d->NIK . '|' . $d->overtime_date)
            ->toArray();

        foreach ($this->stagedData as $index => &$row) {
            $key = $row['nik'] . '|' . $row['overtime_date'];
            if (in_array($key, $existing)) {
                $row['errors'][] = "Already exists in a local Draft/Form.";
                $hasLocalConflict = true;
            }
        }

        if ($hasLocalConflict) {
            $this->integrityResults['local'] = 'failed';
            $this->recalculateCounts();
            $this->isReady = false;
            $this->dispatch('flash', type: 'error', message: 'Terdapat baris yang sudah ada di Database Lokal.');
            return;
        }
        $this->integrityResults['local'] = 'passed';

        // 3. Payroll (External) Check
        $this->integrityResults['payroll'] = 'loading';
        $this->isCheckingPayroll = true;
        $hasPayrollConflict = false;
        
        $payrollService = app(\App\Domain\Overtime\Services\OvertimeJPayrollService::class);
        
        foreach ($this->stagedData as &$row) {
            if (empty($row['nik']) || empty($row['overtime_date'])) continue;
            
            $res = $payrollService->checkDetailExists([
                'nik'           => $row['nik'],
                'overtime_date' => $row['overtime_date']
            ]);

            if ($res['exists']) {
                $row['errors'][] = "Already exists in JPayroll (" . ($res['transaction_id'] ?? 'Unknown ID') . ")";
                $hasPayrollConflict = true;
            }
        }

        $this->isCheckingPayroll = false;

        if ($hasPayrollConflict) {
            $this->integrityResults['payroll'] = 'failed';
            $this->recalculateCounts();
            $this->isReady = false;
            $this->dispatch('flash', type: 'error', message: 'Terdapat baris yang sudah ada di JPayroll.');
            return;
        }

        $this->integrityResults['payroll'] = 'passed';
        $this->isIntegrityChecked = true;
        $this->recalculateCounts();
        $this->isReady = ($this->totalValid > 0 && $this->totalErrors === 0);
        
        if ($this->isReady) {
            $this->dispatch('flash', type: 'success', message: 'Integrity Guard: Passed. All systems green.');
        }
    }

    private function recalculateCounts(): void
    {
        $this->totalValid = 0;
        $this->totalErrors = 0;
        foreach ($this->stagedData as $row) {
            if (count($row['errors']) > 0) {
                $this->totalErrors++;
            } else {
                $this->totalValid++;
            }
        }
    }
}
