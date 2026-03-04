<?php

namespace App\Http\Controllers;

use App\DataTables\DisciplineDataTable;
use App\Domain\Discipline\Services\DepartmentEmployeeResolver;
use App\Domain\Discipline\Services\DisciplineScoreCalculatorService;
use App\Domain\Discipline\Services\EvaluationApprovalService;
use App\Models\EvaluationData;
use App\Policies\DisciplineAccessPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * EvaluationController
 *
 * Unified entry point for the /evaluation page.
 * Handles all three employee types (regular, yayasan, magang) on one page,
 * identified by tabs, with a period selector (month + year).
 *
 * Access control:
 *   - Grader (pengawas):  can grade records for their assigned employees
 *   - Dept Head:          can approve all graded records for their dept
 *   - HRD:                can final-approve and export
 */
class EvaluationController extends Controller
{
    public function __construct(
        private DepartmentEmployeeResolver $resolver,
        private DisciplineAccessPolicy     $policy,
        private EvaluationApprovalService  $approvalService,
    ) {}

    // ──────────────────────────────────────────────────────
    // Main Page
    // ──────────────────────────────────────────────────────

    /**
     * Show the unified evaluation page for a given period.
     * Route: GET /evaluation
     * Route: GET /evaluation/{month}/{year}
     */
    public function index(Request $request, ?int $month = null, ?int $year = null)
    {
        $month  ??= (int) now()->format('m');
        $year   ??= (int) now()->format('Y');
        $user     = Auth::user();
        $deptNo   = $user->department?->dept_no;

        // Status summary for the header chips
        $summary = $this->approvalService->statusSummary($month, $year, $deptNo);

        // Can export? Only when all records for this dept+period are fully_approved
        $canExport = $deptNo
            ? $this->approvalService->canExport($month, $year, $deptNo)
            : false;

        return view('evaluation.index', compact('month', 'year', 'user', 'summary', 'canExport'));
    }

    // ──────────────────────────────────────────────────────
    // DataTable AJAX endpoints (one per type tab)
    // ──────────────────────────────────────────────────────

    /**
     * DataTable data for the Regular tab.
     * Route: GET /evaluation/data/regular
     */
    public function dataRegular(DisciplineDataTable $dataTable, Request $request)
    {
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year',  now()->year);

        return $dataTable->forType('regular')->forPeriod($month, $year)->ajax();
    }

    /**
     * DataTable data for the Yayasan tab.
     * Route: GET /evaluation/data/yayasan
     */
    public function dataYayasan(DisciplineDataTable $dataTable, Request $request)
    {
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year',  now()->year);

        return $dataTable->forType('yayasan')->forPeriod($month, $year)->ajax();
    }

    /**
     * DataTable data for the Magang tab.
     * Route: GET /evaluation/data/magang
     */
    public function dataMagang(DisciplineDataTable $dataTable, Request $request)
    {
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year',  now()->year);

        return $dataTable->forType('magang')->forPeriod($month, $year)->ajax();
    }

    // ──────────────────────────────────────────────────────
    // Grade (Pengawas)
    // ──────────────────────────────────────────────────────

    /**
     * Save grades for a single employee record.
     * Route: PUT /evaluation/{id}/grade
     */
    public function grade(Request $request, int $id)
    {
        $record = EvaluationData::with('karyawan')->findOrFail($id);
        $user   = Auth::user();

        $fields = $record->useNewScoringSystem()
            ? ['kemampuan_kerja','kecerdasan_kerja','qualitas_kerja','disiplin_kerja',
               'kepatuhan_kerja','lembur','efektifitas_kerja','relawan','integritas']
            : ['kerajinan_kerja','kerapian_kerja','prestasi','loyalitas','perilaku_kerja'];

        $scores = $request->only($fields);

        // Recalculate total
        $calculator = app(DisciplineScoreCalculatorService::class);
        $total = $record->useNewScoringSystem()
            ? $calculator->calculateTotal($scores, $record)
            : $calculator->calculateTotalOld($scores, $record);

        $scores['total'] = $total;

        // If previously rejected, use regrade (clears rejection state)
        if ($record->isRejected()) {
            $this->approvalService->regrade($record, $scores, $user);
        } else {
            $this->approvalService->grade($record, $scores, $user);
        }

        return response()->json(['message' => 'Graded successfully', 'status' => 'graded']);
    }

    // ──────────────────────────────────────────────────────
    // Approve (Dept Head)
    // ──────────────────────────────────────────────────────

    /**
     * Dept head batch-approves all graded records for their dept+period.
     * Route: POST /evaluation/approve-dept
     */
    public function approveDept(Request $request)
    {
        $user    = Auth::user();
        $deptNo  = $user->department->dept_no;
        $month   = $request->integer('month');
        $year    = $request->integer('year');
        $type    = $request->input('type'); // regular|yayasan|magang|null (all)

        $count = $this->approvalService->approveDept($month, $year, $deptNo, $user, $type);

        return back()->with('success', "{$count} records approved.");
    }

    // ──────────────────────────────────────────────────────
    // Final Approve (HRD / GM)
    // ──────────────────────────────────────────────────────

    /**
     * HRD final-approves all dept-approved records for a dept+period.
     * Route: POST /evaluation/approve-hrd
     */
    public function approveHrd(Request $request)
    {
        $user   = Auth::user();
        $deptNo = $user->department->dept_no;
        $month  = $request->integer('month');
        $year   = $request->integer('year');
        $type   = $request->input('type');

        $count = $this->approvalService->approveHrd($month, $year, $deptNo, $user, $type);

        return back()->with('success', "{$count} records fully approved.");
    }

    // ──────────────────────────────────────────────────────
    // Reject
    // ──────────────────────────────────────────────────────

    /**
     * Reject a single evaluation record (dept head or HRD).
     * Route: POST /evaluation/{id}/reject
     */
    public function reject(Request $request, int $id)
    {
        $request->validate(['remark' => 'required|string|max:500']);

        $record = EvaluationData::findOrFail($id);
        $this->approvalService->reject($record, $request->input('remark'), Auth::user());

        return response()->json(['message' => 'Record rejected.', 'status' => 'rejected']);
    }

    // ──────────────────────────────────────────────────────
    // Fetch single record (AJAX — for the edit modal)
    // ──────────────────────────────────────────────────────

    /**
     * Return a single evaluation record as JSON (for the edit/grade modal).
     * Route: GET /evaluation/{id}/data
     */
    public function show(int $id)
    {
        $record = EvaluationData::with('karyawan')->findOrFail($id);

        return response()->json($record);
    }

    // ──────────────────────────────────────────────────────
    // Status summary (AJAX — for header chips refresh)
    // ──────────────────────────────────────────────────────

    /**
     * Return approval status counts for a dept+period as JSON.
     * Route: GET /evaluation/summary
     */
    public function summary(Request $request)
    {
        $month  = $request->integer('month', now()->month);
        $year   = $request->integer('year',  now()->year);
        $deptNo = Auth::user()->department?->dept_no;
        $type   = $request->input('type');

        return response()->json(
            $this->approvalService->statusSummary($month, $year, $deptNo, $type)
        );
    }

    // ──────────────────────────────────────────────────────
    // Export (Excel)
    // ──────────────────────────────────────────────────────

    /**
     * Export the evaluation data for a specific period to Excel.
     * Route: GET /evaluation/export
     */
    public function export(Request $request, DisciplineDataTable $dataTable)
    {
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year',  now()->year);
        $type  = $request->query('type', 'regular');

        return $dataTable->forType($type)->forPeriod($month, $year)->excel();
    }
}
