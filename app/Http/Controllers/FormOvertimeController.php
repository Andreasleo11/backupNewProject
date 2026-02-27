<?php

namespace App\Http\Controllers;

use App\Domain\Overtime\Services\OvertimeApprovalService;
use App\Domain\Overtime\Services\OvertimeExportService;
use App\Domain\Overtime\Services\OvertimeImportService;
use App\Domain\Overtime\Services\OvertimeJPayrollService;
use App\Domain\Overtime\Services\OvertimeSummaryService;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Models\DetailFormOvertime;
use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use App\Models\HeaderFormOvertime;
use Illuminate\Http\Request;

class FormOvertimeController extends Controller
{
    public function __construct(
        private readonly OvertimeApprovalService $approvalService,
        private readonly OvertimeJPayrollService $jpayrollService,
        private readonly OvertimeImportService $importService,
        private readonly OvertimeExportService $exportService,
        private readonly OvertimeSummaryService $summaryService
    ) {}

    public function rejectDetailServerSide($id)
    {
        $result = $this->approvalService->rejectDetail($id);

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    public function downloadTemplate()
    {
        return $this->exportService->downloadTemplate();
    }

    public function detail($id)
    {
        $header = HeaderFormOvertime::with('user', 'department', 'approvals', 'approvals.step')->find($id);
        $datas = DetailFormOvertime::with('actualOvertimeDetail')->where('header_id', $id)->get();
        $employees = Employee::get();
        $departements = Department::get();

        return view('formovertime.detail', compact('header', 'datas', 'employees', 'departements'));
    }

    public function sign(Request $request, $id)
    {
        $result = $this->approvalService->sign($id, $request->step_id);

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['description' => 'required']);

        $result = $this->approvalService->reject($id, $request->approval_id, $request->description);

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    public function exportOvertime($headerId)
    {
        return $this->exportService->exportOvertime($headerId);
    }

    public function edit($id)
    {
        $header = HeaderFormOvertime::with('user', 'department')->find($id);
        $datas = DetailFormOvertime::where('header_id', $id)->get();
        $employees = Employee::get();
        $departements = Department::get();

        return view('formovertime.edit', compact('header', 'datas', 'employees', 'departements'));
    }

    public function update(Request $request, $id)
    {
        DetailFormOvertime::where('header_id', $id)->delete();

        if ($request->has('items') && is_array($request->input('items'))) {
            foreach ($request->input('items') as $employeedata) {
                DetailFormOvertime::create([
                    'header_id' => $id,
                    'NIK' => $employeedata['nik'],
                    'nama' => $employeedata['nama'],
                    'job_desc' => $employeedata['jobdesc'],
                    'start_date' => $employeedata['startdate'],
                    'start_time' => $employeedata['starttime'],
                    'end_date' => $employeedata['enddate'],
                    'end_time' => $employeedata['endtime'],
                    'break' => $employeedata['break'],
                    'remarks' => $employeedata['remark'],
                ]);
            }
        }

        return redirect()
            ->route('overtime.detail', ['id' => $id])
            ->with('success', 'Form Overtime updated successfully.');
    }

    public function destroy($id)
    {
        HeaderFormOvertime::find($id)->delete();
        DetailFormOvertime::where('header_id', $id)->delete();

        return redirect()->back()->with('success', 'Form Overtime deleted successfully!');
    }

    public function destroyDetail($id)
    {
        DetailFormOvertime::find($id)->delete();

        return redirect()->back()->with('success', 'Form Overtime Detail deleted successfully!');
    }

    public function pushSingleDetailToJPayroll($detailId, Request $request)
    {
        $detail = DetailFormOvertime::with('employee', 'header')->find($detailId);

        if (! $detail) {
            return response()->json(['error' => 'Detail tidak ditemukan'], 404);
        }

        $action = $request->query('action');
        $result = $this->jpayrollService->pushSingleDetail($detail, $action);

        return response()->json($result, $result['code'] ?? 200);
    }

    public function pushAllDetailsToJPayroll($headerId)
    {
        $result = $this->jpayrollService->pushAllDetails($headerId);

        return response()->json($result, $result['code'] ?? 200);
    }

    public function summaryView(Request $request)
    {
        $summary = collect();

        if ($request->filled(['start_date', 'end_date'])) {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $summary = $this->summaryService->generateSummary(
                $request->start_date,
                $request->end_date
            );
        }

        return view('formovertime.export_summary', compact('summary'));
    }

    public function exportSummaryExcel(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        return $this->exportService->exportSummary($request->start_date, $request->end_date);
    }

    public function showForm()
    {
        return view('formovertime.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $result = $this->importService->importFromExcel($request->file('file')->getRealPath());

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors($result['message']);
    }

    public function reapprove($id)
    {
        $header = HeaderFormOvertime::findOrFail($id);
        $header->is_push = 0;
        $header->save();

        $header->rejectedDetails()->update([
            'status' => null,
            'reason' => null,
        ]);

        $this->pushAllDetailsToJPayroll($id);
        
        return redirect()->back()->with('success', 'Reapproved successfully !');
    }
}
