<?php

namespace App\Livewire\Overtime;

use App\Domain\Overtime\Models\OvertimeForm;
use App\Domain\Overtime\Services\OvertimeJPayrollService;
use Illuminate\Support\Collection;
use Throwable;

trait WithOvertimeActions
{
    /**
     * ── ROSTER LOGIC ────────────────────────────────────────────────────────
     */

    public function addEmptyRow(): void
    {
        $this->items[] = [
            'id'            => null, 'nik' => '', 'name' => '',
            'overtime_date' => $this->global_overtime_date,
            'job_desc'      => $this->global_job_desc,
            'start_date'    => $this->global_overtime_date,
            'start_time'    => $this->global_start_time,
            'end_date'      => $this->global_end_date,
            'end_time'      => $this->global_end_time,
            'break'         => $this->global_break,
            'remarks'       => $this->global_remarks,
            'payroll_status' => 'pending', 'payroll_voucher_id' => null,
            'is_imported'   => false,
        ];
    }

    public function removeRow(int $index): void
    {
        if (count($this->items) <= 1) {
            $this->dispatch('flash', type: 'warning', message: 'At least one employee is required.');
            return;
        }

        $row = $this->items[$index];
        if (! empty($row['id'])) {
            $this->removedDetailIds[] = (int) $row['id'];
        }

        array_splice($this->items, $index, 1);

        // SYNC VALIDATION: Re-check remaining rows to clear stale errors or shift them correctly
        if ($this->getErrorBag()->any()) {
            $this->resetErrorBag();
            // We don't force full validation here (it might show errors on untouched rows),
            // but we at least clear the shifted indices so the user can see the new state.
        }
        
        $this->items = array_values($this->items);
        $this->resetIntegrity();
    }

    public function checkPayrollStatus(bool $silent = false): void
    {
        $this->isCheckingPayroll = true;
        $service = app(OvertimeJPayrollService::class);
        
        foreach ($this->items as $index => &$item) {
            if (empty($item['nik']) || empty($item['overtime_date'])) continue;
            
            $result = $service->checkDetailExists([
                'nik' => $item['nik'],
                'overtime_date' => $item['overtime_date']
            ]);
            
            $item['payroll_status'] = $result['exists'] ? 'exists' : 'safe';
            $item['payroll_voucher_id'] = $result['transaction_id'] ?? null;
            $item['payroll_msg'] = $result['message'] ?? '';
        }
        
        $this->isCheckingPayroll = false;
        
        if (!$silent) {
            if (collect($this->items)->contains('payroll_status', 'exists')) {
                $this->dispatch('flash', type: 'warning', message: 'Terdapat data yang sudah ada di JPayroll.');
            } else {
                $this->dispatch('flash', type: 'success', message: 'Seluruh data aman.');
            }
        }
    }

    /**
     * ── GLOBAL OBSERVERS ────────────────────────────────────────────────────
     */

    public function updatedGlobalOvertimeDate($value): void {
        if (!$this->syncEnabled) return;
        $this->global_end_date = $value;
        foreach($this->items as $i => $item) { 
            $this->items[$i]['overtime_date'] = $value; 
            $this->items[$i]['start_date'] = $value; 
            $this->items[$i]['end_date'] = $value;
        }
        $this->resetIntegrity();
    }
    public function updatedGlobalEndDate($value): void {
        if (!$this->syncEnabled) return;
        foreach($this->items as $i => $item) { $this->items[$i]['end_date'] = $value; }
        $this->resetIntegrity();
    }
    public function updatedGlobalStartTime($value): void {
        if (!$this->syncEnabled) return;
        foreach($this->items as $i => $item) { $this->items[$i]['start_time'] = $value; }
        $this->resetIntegrity();
    }
    public function updatedGlobalEndTime($value): void {
        if (!$this->syncEnabled) return;
        foreach($this->items as $i => $item) { $this->items[$i]['end_time'] = $value; }
        $this->resetIntegrity();
    }
    public function updatedGlobalBreak($value): void {
        if (!$this->syncEnabled) return;
        foreach($this->items as $i => $item) { $this->items[$i]['break'] = $value; }
        $this->resetIntegrity();
    }
    public function updatedGlobalJobDesc($value): void {
        if (!$this->syncEnabled) return;
        foreach($this->items as $i => $item) { $this->items[$i]['job_desc'] = $value; }
        $this->resetIntegrity();
    }
    public function updatedGlobalRemarks($value): void {
        if (!$this->syncEnabled) return;
        foreach($this->items as $i => $item) { $this->items[$i]['remarks'] = $value; }
        $this->resetIntegrity();
    }
}
