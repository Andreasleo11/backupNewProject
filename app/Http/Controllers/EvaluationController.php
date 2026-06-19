<?php

namespace App\Http\Controllers;

use App\DataTables\EvaluationDataTable;
use App\Domain\Evaluation\Services\DepartmentEmployeeResolver;
use App\Domain\Evaluation\Services\EvaluationApprovalService;
use App\Domain\Evaluation\Services\EvaluationScoreCalculatorService;
use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\EvaluationData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * EvaluationController
 *
 * Unified entry point for the /evaluation page.
 * Handles all three employee types (regular, yayasan, magang) on one page,
 * identified by tabs, with a period selector (month + year).
 *
 * Data source pivot (2025-06):
 *   Attendance figures (Alpha/Telat/Izin/Sakit) are now read directly from
 *   the live `attendance_records` table. The legacy P&E Monthly Excel-upload
 *   pipeline has been removed.
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
        private EvaluationApprovalService $approvalService,
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
        $user      = Auth::user();
        $prevMonth = now()->subMonth();

        // Default: previous month (so grading in current month evaluates prior period).
        $month ??= (int) $prevMonth->format('m');
        $year  ??= (int) $prevMonth->format('Y');

        $isElevated = $user->canAny(['evaluation.view-any', 'evaluation.approve-final']);

        // Non-elevated users are restricted to the YTD window of the previous month's year.
        if (! $isElevated && ! $this->isPeriodAllowed($month, $year, $prevMonth)) {
            return redirect()->route('evaluation.index');
        }

        $deptNo  = $isElevated ? null : $user->department?->dept_no;
        $summary = $this->approvalService->statusSummary($month, $year, $deptNo);
       
        $allowedTabs = $this->resolveAllowedTabs($user);

        if (empty($allowedTabs)) {
            abort(403, 'No evaluation tabs accessible for your account.');
        }

        $canApproveDept  = $user->can('evaluation.approve-department');
        $canApproveFinal = $user->can('evaluation.approve-final');
        $canGrade        = $user->can('evaluation.grade');

        $exportStatus = [];
        foreach ($allowedTabs as $t) {
            $exportStatus[$t] = $this->approvalService->canExport($month, $year, $deptNo, $t, $user);
        }

        return view('evaluation.index', compact(
            'month',
            'year',
            'user',
            'summary',
            'exportStatus',
            'canApproveDept',
            'canApproveFinal',
            'allowedTabs',
            'isElevated',
            'canGrade'
        ));
    }

    // ──────────────────────────────────────────────────────
    // DataTable AJAX endpoints (one per type tab)
    // ──────────────────────────────────────────────────────

    /**
     * DataTable data for the Regular tab.
     * Route: GET /evaluation/data/regular
     */
    public function dataRegular(EvaluationDataTable $dataTable, Request $request)
    {
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);

        return $dataTable->forType('regular')->forPeriod($month, $year)->ajax();
    }

    /**
     * DataTable data for the Yayasan tab.
     * Route: GET /evaluation/data/yayasan
     */
    public function dataYayasan(EvaluationDataTable $dataTable, Request $request)
    {
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);

        return $dataTable->forType('yayasan')->forPeriod($month, $year)->ajax();
    }

    /**
     * DataTable data for the Magang tab.
     * Route: GET /evaluation/data/magang
     */
    public function dataMagang(EvaluationDataTable $dataTable, Request $request)
    {
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);

        return $dataTable->forType('magang')->forPeriod($month, $year)->ajax();
    }

    // ──────────────────────────────────────────────────────
    // Grade (Pengawas)
    // ──────────────────────────────────────────────────────

    /**
     * Save grades for a single employee record (by EvaluationData ID).
     * Route: PUT /evaluation/{id}/grade
     */
    public function grade(Request $request, int $id)
    {
        $record = EvaluationData::with('karyawan')->findOrFail($id);

        return $this->processGrading($request, $record);
    }

    /**
     * Save/create grades using NIK instead of ID.
     * Supports grading employees who do not yet have an evaluation_datas row.
     * Attendance figures are sourced live from attendance_records and stored
     * as a snapshot so the score calculator has the correct penalty values.
     * Route: PUT /evaluation/grade-by-nik/{nik}/{month}/{year}
     */
    public function gradeByNik(Request $request, string $nik, int $month, int $year)
    {
        $employee = Employee::where('nik', $nik)->firstOrFail();

        $record = EvaluationData::firstOrNew([
            'NIK'   => $nik,
            'Month' => Carbon::create($year, $month, 1)->format('Y-m-d'),
        ]);

        if (! $record->exists) {
            // Aggregate live attendance for the period — stored as a snapshot on
            // the evaluation record so the penalty calculator uses correct values.
            $attendance = $this->aggregateAttendance($nik, $month, $year);

            $record->pe_id          = null;
            $record->department_id  = $employee->department->id ?? null;
            $record->level          = $employee->level ?? 5;
            $record->evaluation_type = $record->evaluationType();
            $record->Alpha          = $attendance['alpha'];
            $record->Telat          = $attendance['telat'];
            $record->Izin           = $attendance['izin'];
            $record->Sakit          = $attendance['sakit'];
            $record->setRelation('karyawan', $employee);
        } else {
            $record->load('karyawan');

            // Refresh attendance snapshot from live data on every re-grade.
            $attendance = $this->aggregateAttendance($nik, $month, $year);
            $record->Alpha = $attendance['alpha'];
            $record->Telat = $attendance['telat'];
            $record->Izin  = $attendance['izin'];
            $record->Sakit = $attendance['sakit'];
        }

        return $this->processGrading($request, $record);
    }

    private function processGrading(Request $request, EvaluationData $record): \Illuminate\Http\JsonResponse
    {
        $this->authorize('evaluation.grade');

        $user = Auth::user();

        $fields = $record->useNewScoringSystem()
            ? ['kemampuan_kerja', 'kecerdasan_kerja', 'qualitas_kerja', 'disiplin_kerja',
                'kepatuhan_kerja', 'lembur', 'efektifitas_kerja', 'relawan', 'integritas']
            : ['kerajinan_kerja', 'kerapian_kerja', 'prestasi', 'loyalitas', 'perilaku_kerja'];

        $scores = $request->only($fields);

        // Calculate the total — the calculator reads Alpha/Telat/Izin/Sakit from $record
        // which are now populated from attendance_records before this point.
        $calculator = app(EvaluationScoreCalculatorService::class);
        $total = $record->useNewScoringSystem()
            ? $calculator->calculateTotal($scores, $record)
            : $calculator->calculateTotalOld($scores, $record);

        $scores['total'] = $total;

        // Include the attendance snapshot so it is persisted alongside the grades.
        $scores['Alpha'] = $record->Alpha ?? 0;
        $scores['Telat'] = $record->Telat ?? 0;
        $scores['Izin']  = $record->Izin  ?? 0;
        $scores['Sakit'] = $record->Sakit ?? 0;

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
        $month  = $request->integer('month');
        $year   = $request->integer('year');
        $type   = $request->input('type'); // regular|yayasan|magang|null (all)

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

        $user  = Auth::user();
        $month = $request->integer('month');
        $year  = $request->integer('year');
        $type  = $request->input('type');

        $count = $this->approvalService->approveHrd($month, $year, $user, $type);

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
     * Fetch EvaluationData by NIK, or return a new unpersisted instance pre-filled with
     * live attendance aggregates from attendance_records.
     * This makes the Grade Modal "Employee-Centric" — it always succeeds and opens.
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

        // If not found, build an empty template pre-filled with live attendance aggregates
        // from attendance_records — no longer depends on the legacy P&E Monthly upload.
        if (! $record) {
            $attendance = $this->aggregateAttendance($nik, $month, $year);

            $record = new EvaluationData([
                'NIK'   => $nik,
                'Month' => Carbon::create($year, $month, 1)->format('Y-m-d'),
                'Alpha' => $attendance['alpha'],
                'Telat' => $attendance['telat'],
                'Izin'  => $attendance['izin'],
                'Sakit' => $attendance['sakit'],
            ]);
            $record->setRelation('karyawan', $employee);
        }

        // YTD (Year-to-Date) attendance totals — summed directly from attendance_records
        // for the full year up to the selected month. No longer uses evaluation_datas.
        $ytd = AttendanceRecord::where('nik', $nik)
            ->whereYear('shift_date', $year)
            ->whereMonth('shift_date', '<=', $month)
            ->selectRaw('
                COALESCE(SUM(alpha), 0) AS ytd_alpha,
                COALESCE(SUM(telat), 0) AS ytd_telat,
                COALESCE(SUM(izin),  0) AS ytd_izin,
                COALESCE(SUM(sakit), 0) AS ytd_sakit,
                COUNT(DISTINCT DATE_FORMAT(shift_date, "%Y-%m")) AS ytd_months
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
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);
        $type  = $request->input('type');

        $deptNo = Auth::user()->canAny(['evaluation.view-any', 'evaluation.approve-final'])
            ? null
            : Auth::user()->department?->dept_no;

        return response()->json(
            $this->approvalService->statusSummary($month, $year, $deptNo, $type)
        );
    }

    // ──────────────────────────────────────────────────────
    // Activity History (super-admin only)
    // ──────────────────────────────────────────────────────

    /**
     * Return paginated Spatie activity log entries for evaluation records.
     * Route: GET /evaluation/history
     */
    public function historyData(Request $request)
    {
        abort_unless(auth()->user()?->hasRole('super-admin'), 403);

        $query = \Spatie\Activitylog\Models\Activity::with('causer')
            ->where('log_name', 'evaluation')
            ->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('subject_id', 'like', "%{$search}%")
                    ->orWhereHas('causer', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $perPage   = (int) $request->get('per_page', 50);
        $page      = (int) $request->get('page', 1);
        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $paginated->getCollection()->map(function ($activity) {
            $props   = $activity->properties ?? collect();
            $old     = $props->get('old', []);
            $new     = $props->get('attributes', []);
            $changes = [];

            foreach ($new as $field => $val) {
                $prev      = $old[$field] ?? '—';
                $changes[] = "{$field}: {$prev} → {$val}";
            }

            return [
                'id'          => $activity->id,
                'date'        => $activity->created_at->format('d M Y H:i'),
                'causer'      => $activity->causer?->name ?? 'System',
                'description' => $activity->description,
                'subject_id'  => $activity->subject_id,
                'changes'     => implode("\n", $changes) ?: '—',
            ];
        });

        return response()->json([
            'data'         => $data,
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'total'        => $paginated->total(),
        ]);
    }

    // ──────────────────────────────────────────────────────
    // Export (Excel)
    // ──────────────────────────────────────────────────────

    /**
     * Export the evaluation data for a specific period to Excel.
     * Route: GET /evaluation/export
     */
    public function export(Request $request, EvaluationDataTable $dataTable)
    {
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);
        $type  = $request->query('type', 'regular');

        return $dataTable->forType($type)->forPeriod($month, $year)->excel();
    }

    // ──────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────

    /**
     * Aggregate attendance figures for a single employee for a given month/year
     * directly from the live `attendance_records` table.
     *
     * @return array{alpha: int, telat: int, izin: int, sakit: int}
     */
    private function aggregateAttendance(string $nik, int $month, int $year): array
    {
        $row = AttendanceRecord::where('nik', $nik)
            ->whereYear('shift_date', $year)
            ->whereMonth('shift_date', $month)
            ->selectRaw('
                COALESCE(SUM(alpha), 0) AS agg_alpha,
                COALESCE(SUM(telat), 0) AS agg_telat,
                COALESCE(SUM(izin),  0) AS agg_izin,
                COALESCE(SUM(sakit), 0) AS agg_sakit
            ')
            ->first();

        return [
            'alpha' => (int) ($row->agg_alpha ?? 0),
            'telat' => (int) ($row->agg_telat ?? 0),
            'izin'  => (int) ($row->agg_izin  ?? 0),
            'sakit' => (int) ($row->agg_sakit ?? 0),
        ];
    }

    /**
     * Determine whether the requested period falls within the allowed YTD window
     * for non-elevated users.
     *
     * Allowed window: Jan 1 of prevMonth.year → end of prevMonth.
     * This correctly handles Dec→Jan cross-year transitions.
     */
    private function isPeriodAllowed(int $month, int $year, Carbon $prevMonth): bool
    {
        $allowedStart = $prevMonth->copy()->startOfYear();
        $selected     = Carbon::createFromDate($year, $month, 1);

        return $selected->gte($allowedStart) && $selected->lte($prevMonth->copy()->endOfMonth());
    }

    /**
     * Resolve which evaluation tabs the current user may see.
     *
     * @return list<'regular'|'yayasan'|'magang'>
     */
    private function resolveAllowedTabs($user): array
    {
        return array_values(array_filter([
            $user->can('evaluation.view-regular') ? 'regular'  : null,
            $user->can('evaluation.view-yayasan') ? 'yayasan'  : null,
            $user->can('evaluation.view-magang')  ? 'magang'   : null,
        ]));
    }
}
