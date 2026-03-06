<?php

namespace App\Http\Controllers;

use App\DataTables\DisciplineDataTable;
use App\Domain\Discipline\Services\DepartmentEmployeeResolver;
use App\Domain\Discipline\Services\DisciplineScoreCalculatorService;
use App\Domain\Discipline\Services\EvaluationApprovalService;
use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use App\Models\EvaluationData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
        $user = Auth::user();

        $month  ??= (int) now()->format('m');
        $year   ??= (int) now()->format('Y');
        $deptNo   = $user->department?->dept_no;

        // Status summary for the header chips
        $summary = $this->approvalService->statusSummary($month, $year, $deptNo);

        // Can export? Only when all records for this dept+period are fully_approved
        $canExport       = $deptNo ? $this->approvalService->canExport($month, $year, $deptNo) : false;
        $canApproveDept  = $user->can('evaluation.approve-department');
        $canApproveFinal = $user->can('evaluation.approve-final');

        // Which tabs is this user allowed to see?
        $allowedTabs = array_values(array_filter([
            $user->can('evaluation.view-regular') ? 'regular' : null,
            $user->can('evaluation.view-yayasan') ? 'yayasan' : null,
            $user->can('evaluation.view-magang')  ? 'magang'  : null,
        ]));

        if (empty($allowedTabs)) {
            abort(403, 'No evaluation tabs accessible for your account.');
        }

        return view('evaluation.index', compact(
            'month', 'year', 'user', 'summary',
            'canExport', 'canApproveDept', 'canApproveFinal',
            'allowedTabs'
        ));
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
        return $this->processGrading($request, $record);
    }

    /**
     * Save/Create grades using NIK instead of ID.
     * This supports "grading" employees who do not have an existing evaluation_datas row yet.
     * Route: PUT /evaluation/grade-by-nik/{nik}/{month}/{year}
     */
    public function gradeByNik(Request $request, string $nik, int $month, int $year)
    {
        $employee = Employee::where('nik', $nik)->firstOrFail();
        
        $record = EvaluationData::firstOrNew([
            'NIK' => $nik,
            'Month' => Carbon::create($year, $month, 1)->format('Y-m-d'),
        ]);

        if (!$record->exists) {
            // Need to populate relations and base values for a new record to pass validation
            $record->pe_id = null; // Generated normally in some flow? It's nullable or default.
            $record->department_id = $employee->department->id ?? null;
            $record->level = $employee->level ?? 5; // Defaulting level
            $record->setRelation('karyawan', $employee);
        } else {
            $record->load('karyawan');
        }

        return $this->processGrading($request, $record);
    }

    private function processGrading(Request $request, EvaluationData $record)
    {
        $this->authorize('evaluation.grade');

        $user = Auth::user();

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
        $this->authorize('evaluation.approve-department');

        $user   = Auth::user();
        $deptNo = $user->department->dept_no;
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
        $this->authorize('evaluation.approve-final');

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
        if (! Auth::user()->canAny(['evaluation.approve-department', 'evaluation.approve-final'])) {
            abort(403, 'Unauthorized to reject evaluations.');
        }

        $request->validate(['remark' => 'required|string|max:500']);

        $record = EvaluationData::findOrFail($id);
        $this->approvalService->reject($record, $request->input('remark'), Auth::user());

        return response()->json(['message' => 'Record rejected.', 'status' => 'rejected']);
    }

    // ──────────────────────────────────────────────────────
    // Fetch single record (AJAX — for the edit modal)
    // ──────────────────────────────────────────────────────

    /**
     * Show data for a single record to populate the grade modal.
     * Route: GET /evaluation/{id}/data
     */
    public function show(int $id)
    {
        $this->authorize('evaluation.grade');

        $record = EvaluationData::with('karyawan')->findOrFail($id);

        return response()->json($record);
    }

    /**
     * Fetch EvaluationData by NIK, or return a new unpersisted instance with default values.
     * This makes the Frontend Modal "Employee-Centric" — it always succeeds and opens.
     * Route: GET /evaluation/data-by-nik/{nik}/{month}/{year}
     */
    public function showByNik(string $nik, int $month, int $year)
    {
        $this->authorize('evaluation.grade');

        $employee = Employee::where('nik', $nik)->firstOrFail();
        $record = EvaluationData::with('karyawan')
            ->where('NIK', $nik)
            ->whereMonth('Month', $month)
            ->whereYear('Month', $year)
            ->first();

        // If not found, return an empty template structure attached to the employee.
        if (! $record) {
            $record = new EvaluationData([
                'NIK' => $nik,
                'Month' => Carbon::create($year, $month, 1)->format('Y-m-d'),
                'Alpha' => 0,
                'Telat' => 0,
                'Izin' => 0,
                'Sakit' => 0,
            ]);
            $record->setRelation('karyawan', $employee); // Attach employee so JS can read employment_scheme/name
        }

        // YTD (Year-to-Date) attendance totals — sum from Jan up to the selected month
        $ytd = EvaluationData::where('NIK', $nik)
            ->whereYear('Month', $year)
            ->where('Month', '<=', Carbon::create($year, $month, 1)->endOfMonth())
            ->selectRaw('
                COALESCE(SUM(Alpha), 0) AS ytd_alpha,
                COALESCE(SUM(Telat), 0) AS ytd_telat,
                COALESCE(SUM(Izin),  0) AS ytd_izin,
                COALESCE(SUM(Sakit), 0) AS ytd_sakit,
                COUNT(*)               AS ytd_months
            ')
            ->first();

        return response()->json(array_merge(
            $record->toArray(),
            [
                'ytd_alpha'  => (int) ($ytd->ytd_alpha  ?? 0),
                'ytd_telat'  => (int) ($ytd->ytd_telat  ?? 0),
                'ytd_izin'   => (int) ($ytd->ytd_izin   ?? 0),
                'ytd_sakit'  => (int) ($ytd->ytd_sakit  ?? 0),
                'ytd_months' => (int) ($ytd->ytd_months ?? 0),
            ]
        ));
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
        $this->authorize('evaluation.approve-final');

        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year',  now()->year);
        $type  = $request->query('type', 'regular');

        return $dataTable->forType($type)->forPeriod($month, $year)->excel();
    }

    // ──────────────────────────────────────────────────────
    // Bulk Excel Import (Regular Employees)
    // ──────────────────────────────────────────────────────

    /**
     * Bulk-import graded scores for regular employees from Excel.
     * Graders with 100+ employees can fill in a template and upload here.
     * Route: POST /evaluation/import
     */
    public function import(Request $request)
    {
        $this->authorize('evaluation.grade');

        $request->validate([
            'excel_files'   => 'required|array|min:1',
            'excel_files.*' => 'required|file|mimes:xlsx,xls',
            'month'         => 'required|integer|between:1,12',
            'year'          => 'required|integer|min:2000',
        ]);

        $excelService = app(\App\Domain\Discipline\Services\DisciplineExcelService::class);
        $excelService->importRegularData(
            $request->file('excel_files'),
            $request->integer('month'),
            $request->integer('year'),
        );

        return back()->with('success', 'Evaluation data imported successfully.');
    }
}
