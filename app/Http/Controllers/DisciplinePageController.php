<?php

namespace App\Http\Controllers;

use App\DataTables\AllDisciplineTableDataTable;
use App\DataTables\DisciplineMagangDataTable;
use App\DataTables\DisciplineTableDataTable;
use App\DataTables\DisciplineYayasanTableDataTable;
use App\Domain\Discipline\Repositories\EvaluationDataRepositoryContract;
use App\Domain\Discipline\Services\DepartmentEmployeeResolver;
use App\Domain\Discipline\Services\DisciplineApprovalService;
use App\Domain\Discipline\Services\DisciplineDataLockService;
use App\Domain\Discipline\Services\DisciplineDataSyncService;
use App\Domain\Discipline\Services\DisciplineDepartmentStatusService;
use App\Domain\Discipline\Services\DisciplineExcelService;
use App\Domain\Discipline\Services\DisciplineScoreCalculatorService;
use App\Models\EvaluationData;
use App\Policies\DisciplineAccessPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * DisciplinePageController
 *
 * Handles all HTTP layer concerns for the Performance & Evaluation feature.
 * Delegates ALL business logic to Domain services — no raw queries here.
 *
 * Route groups:
 *   Regular employees  → index, approve_depthead, approve_gm, import
 *   Yayasan employees  → indexyayasan, updateyayasan, lockdata, approve/reject buttons
 *   Magang employees   → indexmagang, updatemagang, addlineMagang
 *   All discipline     → allindex
 *   Exports            → exportYayasan, exportYayasanFull, exportYayasanJpayroll, exportYayasanJpayrollFunction
 *   AJAX               → fetchFilteredEmployees, fetchFilteredEmployeesGM, fetchFilteredYayasanEmployees, getDepartmentStatusYayasan
 */
class DisciplinePageController extends Controller
{
    public function __construct(
        private DepartmentEmployeeResolver $resolver,
        private DisciplineAccessPolicy $policy
    ) {}

    // ──────────────────────────────────────────────────────
    // Regular Employee Discipline
    // ──────────────────────────────────────────────────────

    /**
     * Show the regular discipline DataTable for the authenticated dept head.
     * Route: GET /discipline/index  (discipline.index)
     */
    public function index(DisciplineTableDataTable $dataTable)
    {
        $user = Auth::user();

        if (! $this->policy->viewAnyDiscipline($user)) {
            abort(403, 'Only Department Heads can access this');
        }

        $employees = $this->resolver->resolveForUser($user);

        return $dataTable->render('setting.disciplineindex', compact('employees', 'user'));
    }

    /**
     * Dept head approves regular discipline for their department.
     * Route: POST /discipline/adddepthead  (discipline.adddepthead)
     */
    public function approve_depthead(Request $request)
    {
        $approvalService = app(DisciplineApprovalService::class);

        $deptNo = Auth::user()->department->dept_no;
        $month  = $request->input('filter_month');
        $year   = $request->input('filter_year');

        $approvalService->approveDeptHead($deptNo, $month, $year, lockData: true);

        return redirect()->back();
    }

    /**
     * GM approves regular discipline data.
     * Route: POST /discipline/addGm  (discipline.addGM)
     */
    public function approve_gm(Request $request)
    {
        $approvalService = app(DisciplineApprovalService::class);

        $deptNo = $request->filter_dept;
        $month  = $request->input('filter_month');

        $approvalService->approveGeneralManager($deptNo, $month);

        return redirect()->back();
    }

    /**
     * Import regular employee attendance data from Excel.
     * Route: POST /import-file  (discipline.import)
     */
    public function import(Request $request)
    {
        $excelService = app(DisciplineExcelService::class);

        $excelService->importRegularData($request->file('excel_files'));

        return redirect()->route('discipline.index')->with('success', 'Data imported successfully');
    }

    // ──────────────────────────────────────────────────────
    // Yayasan Employee Discipline
    // ──────────────────────────────────────────────────────

    /**
     * Show the Yayasan discipline DataTable.
     * Route: GET /discipline/yayasan/table  (yayasan.table)
     */
    public function indexyayasan(DisciplineYayasanTableDataTable $dataTable)
    {
        $user = Auth::user();

        if (! $this->policy->viewYayasanDiscipline($user)) {
            abort(403, 'Department does not have Yayasan employees');
        }

        try {
            $employees = $this->resolver->resolveYayasanForUser($user);
            $files = [];

            return $dataTable->render('setting.disciplineyayasanindex', compact('employees', 'user', 'files'));
        } catch (\Throwable $th) {
            abort(403, 'Department does not have Yayasan employees');
        }
    }

    /**
     * Update a single Yayasan evaluation record's grade scores.
     * Route: POST /discipline/yayasan/update/{id}  (discipline.yayasan.update)
     */
    public function updateyayasan(Request $request, $id)
    {
        $this->updateEvaluationRecord($request, $id);

        return redirect()->route('yayasan.table')->with('success', 'Data updated successfully');
    }

    /**
     * Update a single Magang evaluation record's grade scores.
     * Route: POST /discipline/magang/update/{id}  (discipline.magang.update)
     */
    public function updatemagang(Request $request, $id)
    {
        $this->updateEvaluationRecord($request, $id);

        return redirect()->route('magang.table')->with('success', 'Data updated successfully');
    }

    /**
     * Shared update logic for Yayasan and Magang evaluation records.
     * Saves grade scores, recalculates total, stamps pengawas, resets prior rejections.
     */
    private function updateEvaluationRecord(Request $request, int|string $id): void
    {
        $evaluationData = EvaluationData::findOrFail($id);
        $pengawas       = Auth::user();

        $evaluationData->update([
            'kemampuan_kerja'   => $request->kemampuan_kerja,
            'kecerdasan_kerja'  => $request->kecerdasan_kerja,
            'qualitas_kerja'    => $request->qualitas_kerja,
            'disiplin_kerja'    => $request->disiplin_kerja,
            'kepatuhan_kerja'   => $request->kepatuhan_kerja,
            'lembur'            => $request->lembur,
            'efektifitas_kerja' => $request->efektifitas_kerja,
            'relawan'           => $request->relawan,
            'integritas'        => $request->integritas,
        ]);

        $scoreCalculator = app(DisciplineScoreCalculatorService::class);
        $scores = $request->only($scoreCalculator->getScoredFields());
        $total  = $scoreCalculator->calculateTotal($scores, $evaluationData->fresh());

        $evaluationData->update([
            'total'    => $total,
            'pengawas' => $pengawas->name,
        ]);

        // Reset any prior rejections so the record re-enters the approval flow
        $approvalService = app(DisciplineApprovalService::class);
        $approvalService->resetRejectedApprovals($evaluationData);
    }

    /**
     * Lock/freeze Yayasan data for a department+month so no edits can be made.
     * Route: POST /discipline/yayasan/lock  (discipline.yayasan.lock)
     * Route: POST /lock-data/discipline     (lock.data)
     */
    public function lockdata(Request $request)
    {
        $lockService = app(DisciplineDataLockService::class);

        $deptNo = Auth::user()->department->dept_no;
        $month  = $request->input('filter_month');

        $lockService->lockByDepartmentAndMonth($deptNo, $month);

        return redirect()->back();
    }

    /**
     * Stub: Add a new line/row to the Yayasan evaluation table.
     * Route: POST /discipline/yayasan/addline  (discipline.yayasan.addline)
     *
     * @todo Implement line creation logic when the business requirement is defined.
     */
    public function addlineYayasan(Request $request)
    {
        // Stub — not yet implemented
        return redirect()->route('yayasan.table')->with('info', 'Add line feature coming soon');
    }

    /**
     * Stub: Add a new line/row to the Magang evaluation table.
     * Route: POST /discipline/magang/addline  (discipline.magang.addline)
     *
     * @todo Implement line creation logic when the business requirement is defined.
     */
    public function addlineMagang(Request $request)
    {
        // Stub — not yet implemented
        return redirect()->route('magang.table')->with('info', 'Add line feature coming soon');
    }

    /**
     * Dept head approves Yayasan or Magang evaluation records for their dept+month.
     * Route: POST /discipline/yayasan/approvalDepthead  (approve.depthead.yayasan)
     * Route: POST /discipline/magang/approval            (approve.data.depthead)
     *
     * The `redirect_to` hidden field in the form controls which table to return to.
     */
    public function approve_depthead_button(Request $request)
    {
        $approvalService = app(DisciplineApprovalService::class);

        $deptNo = Auth::user()->department->dept_no;
        $month  = $request->input('filter_month');
        $year   = $request->input('filter_year');

        $approvalService->approveDeptHead($deptNo, $month, $year);

        // Redirect back to the correct table based on which form submitted
        $redirectTo = $request->input('redirect_to', 'yayasan.table');

        return redirect()->route($redirectTo)->with('success', 'Approved by Dept Head');
    }

    /**
     * Dept head rejects Yayasan evaluation records with a remark.
     * Route: POST /discipline/yayasan/rejectDepthead  (reject.depthead.yayasan)
     */
    public function reject_depthead_button(Request $request)
    {
        $approvalService = app(DisciplineApprovalService::class);

        $deptNo = Auth::user()->department->dept_no;
        $month  = $request->input('filter_month');
        $year   = $request->input('filter_year');
        $remark = $request->input('remark');

        $approvalService->rejectDeptHead($deptNo, $month, $year, $remark);

        return redirect()->route('yayasan.table')->with('success', 'Rejected by Dept Head');
    }

    /**
     * HRD rejects Yayasan evaluation records (resets both depthead + GM).
     * Route: POST /discipline/yayasan/rejectHRD  (reject.hrd.yayasan)
     */
    public function reject_hrd_button(Request $request)
    {
        $approvalService = app(DisciplineApprovalService::class);

        $deptNo = $request->input('filter_dept');
        $month  = $request->input('filter_month');
        $year   = $request->input('filter_year');
        $remark = $request->input('remark');

        $approvalService->rejectHRD($deptNo, $month, $year, $remark);

        return redirect()->route('yayasan.table')->with('success', 'Rejected by HRD');
    }

    /**
     * HRD (or GM) approves Yayasan evaluation records as general manager.
     * Route: POST /discipline/yayasan/approvalHRD  (approve.hrd.yayasan)
     */
    public function approve_hrd_button(Request $request)
    {
        $approvalService = app(DisciplineApprovalService::class);

        $deptNo = $request->input('filter_dept');
        $month  = $request->input('filter_month');
        $year   = $request->input('filter_year');

        $approvalService->approveGeneralManager($deptNo, $month, $year);

        return redirect()->route('yayasan.table')->with('success', 'Approved by HRD');
    }

    // ──────────────────────────────────────────────────────
    // Magang (Internship) Employee Discipline
    // ──────────────────────────────────────────────────────

    /**
     * Show the Magang discipline DataTable.
     * Route: GET /discipline/magang/table  (magang.table)
     */
    public function indexmagang(DisciplineMagangDataTable $dataTable)
    {
        $user = Auth::user();

        if (! $this->policy->viewYayasanDiscipline($user)) {
            abort(403, 'Department does not have Magang employees');
        }

        try {
            $employees = $this->resolver->resolveMagangForUser($user);

            return $dataTable->render('setting.disciplineMagangindex', compact('employees', 'user'));
        } catch (\Throwable $th) {
            abort(403, 'Department does not have Magang employees');
        }
    }


    // ──────────────────────────────────────────────────────
    // All Discipline (HR / Super-Admin view)
    // ──────────────────────────────────────────────────────

    /**
     * Show all discipline records across all departments.
     * Route: GET /all/discipline  (alldiscipline.index)
     */
    public function allindex(AllDisciplineTableDataTable $dataTable)
    {
        $user = Auth::user();

        if (! $this->policy->viewAllDiscipline($user)) {
            abort(403, 'Unauthorized access');
        }

        $employees = $this->resolver->repository->getAllNonYayasan();

        return $dataTable->render('setting.alldisciplineindex', compact('employees'));
    }

    // ──────────────────────────────────────────────────────
    // AJAX Endpoints — Filtered Employee Lists
    // ──────────────────────────────────────────────────────

    /**
     * Return JSON list of regular employees for a dept head filtered by month.
     */
    public function fetchFilteredEmployees(Request $request)
    {
        $deptNo = Auth::user()->department->dept_no;
        $month  = $request->input('filter_month');

        $employees = $this->resolver->fetchForDepartmentHead($deptNo, $month);

        return response()->json($employees);
    }

    /**
     * Return JSON list of Yayasan employees for GM filtered by dept+month.
     */
    public function fetchFilteredEmployeesGM(Request $request)
    {
        $deptNo = $request->input('filter_dept');
        $month  = $request->input('filter_month');

        $employees = $this->resolver->fetchForGeneralManager($deptNo, $month);

        return response()->json($employees);
    }

    /**
     * Return JSON list of Yayasan employees filtered by month+year (GM sees all, head sees their dept).
     */
    public function fetchFilteredYayasanEmployees(Request $request)
    {
        $month  = $request->input('filter_month');
        $year   = $request->input('filter_year');
        $isGM   = Auth::user()->is_gm;
        $deptNo = $isGM ? null : Auth::user()->department->dept_no;

        $employees = $this->resolver->fetchYayasanEmployees($month, $year, $isGM, $deptNo);

        return response()->json($employees);
    }

    /**
     * Return JSON department approval status for a given month+year (for export readiness).
     * Route: GET /discipline/yayasan/status  (department.status.yayasan)
     * Route: GET /exportyayasan/summary      (exportyayasan.summary)
     */
    public function getDepartmentStatusYayasan(Request $request)
    {
        try {
            $statusService = app(DisciplineDepartmentStatusService::class);

            $month = $request->input('month') ?? $request->input('filter_month');
            $year  = $request->input('year') ?? $request->input('filter_year');

            $departmentStatus = $statusService->getDepartmentStatusForMonth($month, $year);

            return response()->json([
                'status' => 'success',
                'data'   => $departmentStatus,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ──────────────────────────────────────────────────────
    // Export Routes
    // ──────────────────────────────────────────────────────

    /**
     * Show the date/month input form before exporting Yayasan data to JPayroll.
     * Route: GET /exportyayasantodateinput  (exportyayasan.dateinput)
     */
    public function dateExport()
    {
        return view('setting.inputDateExportYayasan');
    }

    /**
     * Show the JPayroll export page listing per-department approval status.
     * Route: GET /exportyayasan  (export.yayasan.jpayroll)
     */
    public function exportYayasanJpayroll(Request $request)
    {
        $statusService = app(DisciplineDepartmentStatusService::class);

        $selectedMonth    = $request->input('month');
        $currentYear      = $request->input('year');
        $departmentStatus = $statusService->getJpayrollDepartmentStatus($selectedMonth, $currentYear);

        return view('setting.exportYayasanJpayroll', compact('departmentStatus', 'selectedMonth', 'currentYear'));
    }

    /**
     * Download the JPayroll Excel export file.
     * Route: POST /exportyayasan/download  (exportyayasan.download)
     */
    public function exportYayasanJpayrollFunction(Request $request)
    {
        $excelService = app(DisciplineExcelService::class);

        $selectedMonth = $request->input('filter_status');
        $currentYear   = $request->input('year');

        return $excelService->exportYayasanJpayrollFunction($selectedMonth, $currentYear);
    }

    /**
     * Download a basic Yayasan export (grade categorized).
     * Route: GET /firstimeexport/yayasan/discipline  (export.yayasan.first.time)
     */
    public function exportYayasan(Request $request)
    {
        $excelService  = app(DisciplineExcelService::class);
        $selectedMonth = $request->input('filter_status');

        return $excelService->exportYayasan($selectedMonth);
    }

    /**
     * Download the full Yayasan export (all data, no categorization).
     * Route: GET /export/yayasan-full/discipline  (export.yayasan.full)
     */
    public function exportYayasanFull(Request $request)
    {
        $excelService  = app(DisciplineExcelService::class);
        $selectedMonth = $request->input('filter_status');

        return $excelService->exportYayasanFull($selectedMonth);
    }

    // ──────────────────────────────────────────────────────
    // Data Sync Utilities (Admin / HR use)
    // ──────────────────────────────────────────────────────

    /**
     * Sync all department codes in evaluation_datas from the Employee master.
     * Used after bulk employee department changes.
     */
    public function updateDeptColumn()
    {
        $syncService = app(DisciplineDataSyncService::class);
        $result = $syncService->syncAllDepartments();

        return response()->json([
            'message' => 'Dept column updated successfully.',
            'stats'   => $result,
        ]);
    }

    /**
     * Show the locked data management page (admin only).
     */
    public function unlockdata()
    {
        $lockService = app(DisciplineDataLockService::class);
        $datas = $lockService->getLockedData();

        return view('admin.unlockdata', compact('datas'));
    }

    // ──────────────────────────────────────────────────────
    // Import Routes (Excel upload for Yayasan/Magang data)
    // ──────────────────────────────────────────────────────

    /**
     * Import Yayasan discipline data from Excel.
     */
    public function importyayasan(Request $request)
    {
        $excelService = app(DisciplineExcelService::class);
        $excelService->importYayasanData($request->file('excel_files'));

        return redirect()->route('yayasan.table')->with('success', 'Data imported successfully');
    }

    /**
     * Import Magang discipline data from Excel.
     */
    public function magangimport(Request $request)
    {
        $excelService = app(DisciplineExcelService::class);
        $excelService->importYayasanData($request->file('excel_files'));

        return redirect()->route('magang.table')->with('success', 'Data imported successfully');
    }

    // ──────────────────────────────────────────────────────
    // Evaluation Data API (AJAX / JSON)
    // ──────────────────────────────────────────────────────

    /**
     * Return a single evaluation record with relationships loaded.
     */
    public function getEvaluationData($id)
    {
        $repository = app(EvaluationDataRepositoryContract::class);
        $employee   = $repository->findWithRelations($id);

        if (! $employee) {
            return response()->json(['error' => 'Evaluation data not found'], 404);
        }

        return response()->json($employee);
    }


}
