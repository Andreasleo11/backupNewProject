<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Discipline\Repositories\EvaluationDataRepositoryContract;
use App\Domain\Discipline\Services\EvaluationLegacyApprovalService;
use App\Domain\Discipline\Services\EvaluationExcelService;
use App\Http\Resources\V1\EvaluationDataResource;
use App\Models\EvaluationData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class EvaluationApiController extends Controller
{
    public function __construct(
        private readonly EvaluationDataRepositoryContract $repository,
        private readonly EvaluationLegacyApprovalService $approvalService,
        private readonly EvaluationExcelService $excelService
    ) {}

    /**
     * List evaluation data with filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = EvaluationData::with(['karyawan', 'department'])->latest('Month');

        // Apply filters
        if ($request->filled('department')) {
            $query->where('dept_code', $request->department);
        }

        if ($request->filled('month')) {
            $query->whereMonth('Month', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('Month', $request->year);
        }

        if ($request->filled('status')) {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        if ($request->filled('is_locked')) {
            $query->where('is_lock', $request->boolean('is_locked'));
        }

        $perPage = min($request->input('per_page', 15), 100);
        $evaluations = $query->paginate($perPage);

        return EvaluationDataResource::collection($evaluations);
    }

    /**
     * Show single evaluation.
     */
    public function show(int $id): JsonResponse
    {
        $evaluation = $this->repository->findWithRelations($id);

        if (! $evaluation) {
            return $this->errorResponse('Evaluation data not found', 404);
        }

        return $this->successResponse(
            new EvaluationDataResource($evaluation)
        );
    }

    /**
     * Create new evaluation.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'string', 'exists:employees,NIK'],
            'department' => ['required', 'string'],
            'month' => ['required', 'date'],
            'kemampuan_kerja' => ['required', 'in:A,B,C,D'],
            'kecerdasan_kerja' => ['required', 'in:A,B,C,D'],
            'qualitas_kerja' => ['required', 'in:A,B,C,D'],
            'disiplin_kerja' => ['required', 'in:A,B,C,D'],
            'kepatuhan_kerja' => ['required', 'in:A,B,C,D'],
            'lembur' => ['required', 'in:A,B,C,D'],
            'efektifitas_kerja' => ['required', 'in:A,B,C,D'],
            'relawan' => ['required', 'in:A,B,C,D'],
            'integritas' => ['required', 'in:A,B,C,D'],
            'alpha' => ['nullable', 'integer', 'min:0'],
            'telat' => ['nullable', 'integer', 'min:0'],
            'sakit' => ['nullable', 'integer', 'min:0'],
            'izin' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $evaluation = EvaluationData::create([
                'NIK' => $validated['nik'],
                'dept' => $validated['department'],
                'Month' => $validated['month'],
                'kemampuan_kerja' => $validated['kemampuan_kerja'],
                'kecerdasan_kerja' => $validated['kecerdasan_kerja'],
                'qualitas_kerja' => $validated['qualitas_kerja'],
                'disiplin_kerja' => $validated['disiplin_kerja'],
                'kepatuhan_kerja' => $validated['kepatuhan_kerja'],
                'lembur' => $validated['lembur'],
                'efektifitas_kerja' => $validated['efektifitas_kerja'],
                'relawan' => $validated['relawan'],
                'integritas' => $validated['integritas'],
                'Alpha' => $validated['alpha'] ?? 0,
                'Telat' => $validated['telat'] ?? 0,
                'Sakit' => $validated['sakit'] ?? 0,
                'Izin' => $validated['izin'] ?? 0,
                'is_lock' => false,
            ]);

            return $this->successResponse(
                new EvaluationDataResource($evaluation->load(['karyawan', 'department'])),
                'Evaluation created successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create evaluation: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Update evaluation.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $evaluation = EvaluationData::find($id);

        if (! $evaluation) {
            return $this->errorResponse('Evaluation data not found', 404);
        }

        if ($evaluation->is_lock) {
            return $this->errorResponse(
                'Cannot update locked evaluation data',
                422
            );
        }

        $validated = $request->validate([
            'kemampuan_kerja' => ['sometimes', 'in:A,B,C,D'],
            'kecerdasan_kerja' => ['sometimes', 'in:A,B,C,D'],
            'qualitas_kerja' => ['sometimes', 'in:A,B,C,D'],
            'disiplin_kerja' => ['sometimes', 'in:A,B,C,D'],
            'kepatuhan_kerja' => ['sometimes', 'in:A,B,C,D'],
            'lembur' => ['sometimes', 'in:A,B,C,D'],
            'efektifitas_kerja' => ['sometimes', 'in:A,B,C,D'],
            'relawan' => ['sometimes', 'in:A,B,C,D'],
            'integritas' => ['sometimes', 'in:A,B,C,D'],
            'alpha' => ['sometimes', 'integer', 'min:0'],
            'telat' => ['sometimes', 'integer', 'min:0'],
            'sakit' => ['sometimes', 'integer', 'min:0'],
            'izin' => ['sometimes', 'integer', 'min:0'],
        ]);

        $evaluation->update($validated);
        $evaluation->load(['karyawan', 'department']);

        return $this->successResponse(
            new EvaluationDataResource($evaluation),
            'Evaluation updated successfully'
        );
    }

    /**
     * Approve evaluations as department head.
     */
    public function approveDeptHead(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'department' => ['required', 'string'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020'],
            'lock_data' => ['nullable', 'boolean'],
        ]);

        $count = $this->approvalService->approveDeptHead(
            $validated['department'],
            $validated['month'],
            $validated['year'],
            $validated['lock_data'] ?? false
        );

        return $this->successResponse([
            'approved_count' => $count,
            'locked' => $validated['lock_data'] ?? false,
        ], "Approved {$count} evaluations");
    }

    /**
     * Approve evaluations as general manager.
     */
    public function approveGM(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'department' => ['required', 'string'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020'],
        ]);

        $count = $this->approvalService->approveGeneralManager(
            $validated['department'],
            $validated['month'],
            $validated['year']
        );

        return $this->successResponse([
            'approved_count' => $count,
        ], "Approved {$count} evaluations");
    }

    /**
     * Reject evaluations as department head.
     */
    public function rejectDeptHead(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'department' => ['required', 'string'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020'],
            'remark' => ['required', 'string'],
        ]);

        $count = $this->approvalService->rejectDeptHead(
            $validated['department'],
            $validated['month'],
            $validated['year'],
            $validated['remark']
        );

        return $this->successResponse([
            'rejected_count' => $count,
        ], "Rejected {$count} evaluations");
    }

    /**
     * Export evaluations to Excel.
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'min:2020'],
            'type' => ['nullable', 'in:full,jpayroll'],
        ]);

        $month = $validated['month'];
        $year = $validated['year'] ?? now()->year;
        $type = $validated['type'] ?? 'full';

        try {
            if ($type === 'jpayroll') {
                return $this->excelService->exportYayasanJpayrollFunction($month, $year);
            }

            return $this->excelService->exportYayasanFull($month);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Export failed: ' . $e->getMessage(),
                500
            );
        }
    }
}
