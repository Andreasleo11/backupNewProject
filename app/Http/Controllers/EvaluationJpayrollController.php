<?php

namespace App\Http\Controllers;

use App\Domain\Evaluation\Services\EvaluationDepartmentStatusService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * EvaluationJpayrollController
 *
 * Handles the Yayasan JPayroll export flow:
 *   1. select  — month/year picker
 *   2. index   — department readiness grid
 *   3. download — streams the categorised Excel file
 *   4. summary — AJAX JSON readiness data (used by external callers)
 */
class EvaluationJpayrollController extends Controller
{
    public function __construct(
        private EvaluationDepartmentStatusService $statusService
    ) {}

    // ──────────────────────────────────────────────────────
    // Step 1 — Period selector
    // ──────────────────────────────────────────────────────

    /**
     * Show the month/year period picker.
     * Route: GET /evaluation/jpayroll/select
     */
    public function select()
    {
        return view('evaluation.jpayroll.select');
    }

    // ──────────────────────────────────────────────────────
    // Step 2 — Department readiness grid
    // ──────────────────────────────────────────────────────

    /**
     * Show department readiness status for the selected period.
     * Route: GET /evaluation/jpayroll
     */
    public function index(Request $request)
    {
        $month = (int) ($request->input('month') ?? now()->month);
        $year = (int) ($request->input('year') ?? now()->year);

        $departmentStatus = $this->statusService->getDepartmentStatusForMonth($month, $year);
        $readyCount = count(array_filter($departmentStatus, fn ($s) => $s === 'Ready'));

        return view('evaluation.jpayroll.index', compact('departmentStatus', 'month', 'year', 'readyCount'));
    }

    // ──────────────────────────────────────────────────────
    // Step 3 — Excel download
    // ──────────────────────────────────────────────────────

    /**
     * Stream the JPayroll-formatted Excel file.
     * Route: POST /evaluation/jpayroll/download
     */
    public function download(Request $request)
    {
        $month = (int) $request->input('month');
        $year = (int) $request->input('year');

        $rows = $this->statusService->exportJpayrollCollection($month, $year);
        $fileName = sprintf('EvaluasiYayasan-JPayroll_%d-%02d.xlsx', $year, $month);

        return Excel::download(
            new \App\Exports\YayasanEvaluationExport($rows),
            $fileName
        );
    }

    // ──────────────────────────────────────────────────────
    // AJAX — readiness JSON
    // ──────────────────────────────────────────────────────

    /**
     * Return department readiness data as JSON (AJAX).
     * Route: GET /evaluation/jpayroll/summary
     */
    public function summary(Request $request)
    {
        try {
            $month = (int) ($request->input('month') ?? now()->month);
            $year = (int) ($request->input('year') ?? now()->year);

            $data = $this->statusService->getDepartmentStatusForMonth($month, $year);

            return response()->json(['status' => 'success', 'data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
