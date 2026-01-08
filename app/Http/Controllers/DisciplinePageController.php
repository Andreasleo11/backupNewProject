<?php

namespace App\Http\Controllers;

use App\DataTables\AllDisciplineTableDataTable;
use App\DataTables\DisciplineMagangDataTable;
use App\DataTables\DisciplineTableDataTable;
use App\DataTables\DisciplineYayasanTableDataTable;
use App\Domain\Discipline\Repositories\EvaluationDataRepository;
use App\Domain\Discipline\Services\DepartmentEmployeeResolver;
use App\Domain\Discipline\Services\DisciplineDepartmentStatusService;
use App\Domain\Discipline\Services\DisciplineDataSyncService;
use App\Models\EvaluationData;
use App\Policies\DisciplineAccessPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisciplinePageController extends Controller
{
    public function __construct(
        private DepartmentEmployeeResolver $resolver,
        private DisciplineAccessPolicy $policy
    ) {}

    public function index(DisciplineTableDataTable $dataTable)
    {
        $user = Auth::user();

        // Check authorization using policy
        if (! $this->policy->viewAnyDiscipline($user)) {
            abort(403, 'Only Department Heads can access this');
        }

        // Use resolver service to get employees based on user's role/department
        $employees = $this->resolver->resolveForUser($user);

        return $dataTable->render('setting.disciplineindex', compact('employees', 'user'));
    }

    public function allindex(AllDisciplineTableDataTable $dataTable)
    {
        $user = Auth::user();

        // Check authorization - only special users can view all discipline records
        if (! $this->policy->viewAllDiscipline($user)) {
            abort(403, 'Unauthorized access');
        }

        $employees = $this->resolver->repository->getAllNonYayasan();

        return $dataTable->render('setting.alldisciplineindex', compact('employees'));
    }

    public function yayasanallindex(DisciplineYayasanTableDataTable $dataTable)
    {
        $user = Auth::user();

        // Check authorization
        if (! $this->policy->viewAllDiscipline($user)) {
            abort(403, 'Unauthorized access');
        }

        $repository = app(EvaluationDataRepository::class);
        $employees = $repository->getAllYayasanEmployees();

        return $dataTable->render('setting.allyayasandisciplineindex', compact('employees'));
    }

    public function setFilterValue(Request $request)
    {
        $filterMonth = $request->input('filterMonth');
        $filterYear = $request->input('filterYear');

        // Store both filter month and year in the session
        session(['filterMonth' => $filterMonth, 'filterYear' => $filterYear]);

        return response()->json([
            'filterMonth' => $filterMonth,
            'filterYear' => $filterYear,
        ]);
    }

    public function getFilterValue()
    {
        $filterValue = session('filterValue');

        return response()->json(['filterValue' => $filterValue]);
    }

    public function update(Request $request, $id)
    {
        $evaluationData = EvaluationData::find($id);

        // Update the evaluation fields
        $evaluationData->update([
            'kerajinan_kerja' => $request->kerajinan_kerja,
            'kerapian_kerja' => $request->kerapian_kerja,
            'prestasi' => $request->prestasi,
            'loyalitas' => $request->loyalitas,
            'perilaku_kerja' => $request->perilaku_kerja,
        ]);

        // Calculate total score using service (OLD scoring system)
        $scoreCalculator = app(\App\Domain\Discipline\Services\DisciplineScoreCalculatorService::class);
        $scores = $request->only($scoreCalculator->getOldScoredFields());
        $total = $scoreCalculator->calculateTotalOld($scores, $evaluationData);

        // Update total score
        $evaluationData->update(['total' => $total]);

        return redirect()->route('discipline.index')->with('success', 'Line added successfully');
    }

    public function import(Request $request)
    {
        $excelService = app(\App\Domain\Discipline\Services\DisciplineExcelService::class);

        $uploadedFiles = $request->file('excel_files');
        $excelService->importRegularData($uploadedFiles);

        return redirect()->route('discipline.index')->with('success', 'Line added successfully');
    }

    public function exportYayasan(Request $request)
    {
        $excelService = app(\App\Domain\Discipline\Services\DisciplineExcelService::class);

        $selectedMonth = $request->input('filter_status');

        return $excelService->exportYayasan($selectedMonth);
    }

    public function exportYayasanFull(Request $request)
    {
        $excelService = app(\App\Domain\Discipline\Services\DisciplineExcelService::class);

        $selectedMonth = $request->input('filter_status');

        return $excelService->exportYayasanFull($selectedMonth);
    }

    public function indexyayasan(DisciplineYayasanTableDataTable $dataTable)
    {
        $user = Auth::user();

        // Check authorization
        if (! $this->policy->viewYayasanDiscipline($user)) {
            abort(403, 'Department does not have Yayasan employees');
        }

        try {
            // Use resolver service to get Yayasan employees based on user's role/department
            $employees = $this->resolver->resolveYayasanForUser($user);
            $files = [];

            return $dataTable->render(
                'setting.disciplineyayasanindex',
                compact('employees', 'user', 'files')
            );
        } catch (\Throwable $th) {
            abort(403, 'Department does not have Yayasan employees');
        }
    }

    public function indexmagang(DisciplineMagangDataTable $dataTable)
    {
        $user = Auth::user();

        // Check authorization
        if (! $this->policy->viewYayasanDiscipline($user)) {
            abort(403, 'Department does not have Magang employees');
        }

        try {
            // Use resolver service to get Magang employees based on user's role/department
            $employees = $this->resolver->resolveMagangForUser($user);

            return $dataTable->render('setting.disciplineMagangindex', compact('employees', 'user'));
        } catch (\Throwable $th) {
            abort(403, 'Department does not have Magang employees');
        }
    }

    public function updatemagang(Request $request, $id)
    {
        $evaluationData = EvaluationData::find($id);
        $pengawas = Auth::user();

        // Update the evaluation fields
        $evaluationData->update([
            'kemampuan_kerja' => $request->kemampuan_kerja,
            'kecerdasan_kerja' => $request->kecerdasan_kerja,
            'qualitas_kerja' => $request->qualitas_kerja,
            'disiplin_kerja' => $request->disiplin_kerja,
            'kepatuhan_kerja' => $request->kepatuhan_kerja,
            'lembur' => $request->lembur,
            'efektifitas_kerja' => $request->efektifitas_kerja,
            'relawan' => $request->relawan,
            'integritas' => $request->integritas,
        ]);

        // Calculate total score using service
        $scoreCalculator = app(\App\Domain\Discipline\Services\DisciplineScoreCalculatorService::class);
        $scores = $request->only($scoreCalculator->getScoredFields());
        $total = $scoreCalculator->calculateTotal($scores, $evaluationData);

        // Update total score and supervisor
        $evaluationData->update([
            'total' => $total,
            'pengawas' => $pengawas->name,
        ]);

        return redirect()->route('magang.table')->with('success', 'Data updated successfully');
    }

    public function updateDept()
    {
        $syncService = app(DisciplineDataSyncService::class);
        $syncService->syncDepartmentsUsingRelationships();

        return redirect()->route('home')->with('success', 'Data updated successfully');
    }

    public function updateyayasan(Request $request, $id)
    {
        $evaluationData = EvaluationData::find($id);
        $pengawas = Auth::user();

        // Update the evaluation fields
        $evaluationData->update([
            'kemampuan_kerja' => $request->kemampuan_kerja,
            'kecerdasan_kerja' => $request->kecerdasan_kerja,
            'qualitas_kerja' => $request->qualitas_kerja,
            'disiplin_kerja' => $request->disiplin_kerja,
            'kepatuhan_kerja' => $request->kepatuhan_kerja,
            'lembur' => $request->lembur,
            'efektifitas_kerja' => $request->efektifitas_kerja,
            'relawan' => $request->relawan,
            'integritas' => $request->integritas,
        ]);

        // Calculate total score using service
        $scoreCalculator = app(\App\Domain\Discipline\Services\DisciplineScoreCalculatorService::class);
        $scores = $request->only($scoreCalculator->getScoredFields());
        $total = $scoreCalculator->calculateTotal($scores, $evaluationData);

        // Update total score and supervisor
        $evaluationData->update([
            'total' => $total,
            'pengawas' => $pengawas->name,
        ]);

        // Reset approvals if previously rejected
        if (
            $evaluationData->generalmanager === 'rejected' ||
            $evaluationData->depthead === 'rejected'
        ) {
            $evaluationData->update([
                'depthead' => null,
                'generalmanager' => null,
            ]);
        }

        return redirect()->route('yayasan.table')->with('success', 'Data updated successfully');
    }

    public function lockdata(Request $request)
    {
        $lockService = app(\App\Domain\Discipline\Services\DisciplineDataLockService::class);

        $deptNo = Auth::user()->department->dept_no;
        $month = $request->input('filter_month');

        $lockService->lockByDepartmentAndMonth($deptNo, $month);

        return redirect()->back();
    }

    public function approve_depthead(Request $request)
    {
        $approvalService = app(\App\Domain\Discipline\Services\DisciplineApprovalService::class);

        $deptNo = Auth::user()->department->dept_no;
        $month = $request->input('filter_month');
        $year = $request->input('filter_year');

        $approvalService->approveDeptHead($deptNo, $month, $year, lockData: true);

        return redirect()->back();
    }

    public function approve_gm(Request $request)
    {
        $approvalService = app(\App\Domain\Discipline\Services\DisciplineApprovalService::class);

        $deptNo = $request->filter_dept;
        $month = $request->input('filter_month');

        $approvalService->approveGeneralManager($deptNo, $month);

        return redirect()->back();
    }

    public function fetchFilteredEmployees(Request $request)
    {
        $repository = app(\App\Domain\Discipline\Repositories\EvaluationDataRepository::class);

        $deptNo = Auth::user()->department->dept_no;
        $month = $request->input('filter_month');

        $employees = $repository->getByDepartmentAndMonth($deptNo, $month);

        return response()->json($employees);
    }

    public function fetchFilteredEmployeesGM(Request $request)
    {
        $repository = app(\App\Domain\Discipline\Repositories\EvaluationDataRepository::class);

        $deptNo = $request->input('filter_dept');
        $month = $request->input('filter_month');

        $employees = $repository->getByDepartmentAndMonth(
            $deptNo,
            $month,
            statuses: ['YAYASAN', 'YAYASAN KARAWANG']
        );

        return response()->json($employees);
    }

    public function fetchFilteredYayasanEmployees(Request $request)
    {
        $repository = app(\App\Domain\Discipline\Repositories\EvaluationDataRepository::class);

        $month = $request->input('filter_month');
        $year = $request->input('filter_year');
        $isGM = Auth::user()->is_gm;

        if ($isGM) {
            $employees = $repository->getYayasanByMonthAndYear($month, $year);
        } else {
            $deptNo = Auth::user()->department->dept_no;
            $employees = $repository->getByDepartmentAndMonth(
                $deptNo,
                $month,
                $year,
                ['YAYASAN', 'YAYASAN KARAWANG']
            );
        }

        return response()->json($employees);
    }

    public function unlockdata()
    {
        $lockService = app(\App\Domain\Discipline\Services\DisciplineDataLockService::class);

        $datas = $lockService->getLockedData();

        return view('admin.unlockdata', compact('datas'));
    }

    public function importyayasan(Request $request)
    {
        $excelService = app(\App\Domain\Discipline\Services\DisciplineExcelService::class);

        $uploadedFiles = $request->file('excel_files');
        $excelService->importYayasanData($uploadedFiles);

        return redirect()->route('yayasan.table')->with('success', 'Line added successfully');
    }

    public function magangimport(Request $request)
    {
        $excelService = app(\App\Domain\Discipline\Services\DisciplineExcelService::class);

        $uploadedFiles = $request->file('excel_files');
        $excelService->importYayasanData($uploadedFiles);

        return redirect()->route('magang.table')->with('success', 'Line added successfully');
    }

    // function untuk update isi dept di Evaluation Data dari data employee master
    public function updateDeptColumn()
    {
        $syncService = app(DisciplineDataSyncService::class);
        $result = $syncService->syncAllDepartments();

        return response()->json([
            'message' => 'Dept column updated successfully.',
            'stats' => $result,
        ]);
    }

    public function approve_depthead_button(Request $request)
    {
        $approvalService = app(\App\Domain\Discipline\Services\DisciplineApprovalService::class);

        $deptNo = Auth::user()->department->dept_no;
        $month = $request->input('filter_month');
        $year = $request->input('filter_year');

        $approvalService->approveDeptHead($deptNo, $month, $year);

        return redirect()->route('yayasan.table')->with('success', 'Approved by depthead');
    }

    public function reject_depthead_button(Request $request)
    {
        $approvalService = app(\App\Domain\Discipline\Services\DisciplineApprovalService::class);

        $deptNo = Auth::user()->department->dept_no;
        $month = $request->input('filter_month');
        $year = $request->input('filter_year');
        $remark = $request->input('remark');

        $approvalService->rejectDeptHead($deptNo, $month, $year, $remark);

        return redirect()->route('yayasan.table')->with('success', 'Rejected by depthead');
    }

    public function reject_hrd_button(Request $request)
    {
        $approvalService = app(\App\Domain\Discipline\Services\DisciplineApprovalService::class);

        $deptNo = $request->input('filter_dept');
        $month = $request->input('filter_month');
        $year = $request->input('filter_year');
        $remark = $request->input('remark');

        $approvalService->rejectHRD($deptNo, $month, $year, $remark);

        return redirect()->route('yayasan.table')->with('success', 'Rejected by HRD');
    }

    public function approve_hrd_button(Request $request)
    {
        $approvalService = app(\App\Domain\Discipline\Services\DisciplineApprovalService::class);

        $deptNo = $request->input('filter_dept');
        $month = $request->input('filter_month');
        $year = $request->input('filter_year');

        $approvalService->approveGeneralManager($deptNo, $month, $year);

        return redirect()->route('yayasan.table')->with('success', 'Approved by HRD');
    }

    public function dateExport()
    {
        return view('setting.inputDateExportYayasan');
    }

    public function exportYayasanJpayroll(Request $request)
    {
        $statusService = app(DisciplineDepartmentStatusService::class);

        $selectedMonth = $request->input('month');
        $currentYear = $request->input('year');

        $departmentStatus = $statusService->getJpayrollDepartmentStatus($selectedMonth, $currentYear);

        return view(
            'setting.exportYayasanJpayroll',
            compact('departmentStatus', 'selectedMonth', 'currentYear'),
        );
    }

    public function getDepartmentStatusYayasan(Request $request)
    {
        try {
            $statusService = app(DisciplineDepartmentStatusService::class);

            $selectedMonth = $request->input('month');
            $currentYear = $request->input('year');

            $departmentStatus = $statusService->getDepartmentStatusForMonth($selectedMonth, $currentYear);

            return response()->json([
                'status' => 'success',
                'data' => $departmentStatus,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function exportYayasanJpayrollFunction(Request $request)
    {
        $excelService = app(\App\Domain\Discipline\Services\DisciplineExcelService::class);

        $selectedMonth = $request->input('filter_status');
        $currentYear = $request->input('year');

        return $excelService->exportYayasanJpayrollFunction($selectedMonth, $currentYear);
    }

    public function getEvaluationData($id)
    {
        $repository = app(EvaluationDataRepository::class);
        $employee = $repository->findWithRelations($id);

        if (!$employee) {
            return response()->json(['error' => 'Evaluation data not found'], 404);
        }

        return response()->json($employee);
    }
}
