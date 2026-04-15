<?php

namespace App\Services;

use App\Domain\Overtime\Models\OvertimeForm;
use App\Domain\Overtime\Models\OvertimeFormDetail;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OvertimeFormService
{
    /**
     * Create a new Overtime Form from manual entry data.
     * Purged legacy Excel logic for the new high-density manual workflow.
     */
    public static function create(Collection $data): OvertimeForm
    {
        return DB::transaction(function () use ($data) {
            $isPlanned = self::determineIsPlanned($data);
            $headerData = [
                'user_id' => Auth::id(),
                'dept_id' => $data->get('dept_id'),
                'branch' => $data->get('branch'),
                'is_design' => $data->get('is_design'),
                'is_export' => 0,
                'is_planned' => $isPlanned,
                'is_after_hour' => $data->get('is_after_hour'),
            ];

            $header = OvertimeForm::create($headerData);

            $createdCount = self::createManualDetails(
                $data->get('items', []),
                $header->id,
                (bool) $header->is_after_hour
            );

            if ($createdCount === 0) {
                throw ValidationException::withMessages([
                    'items' => ['Tidak ada baris lembur yang valid untuk disimpan atau semua baris merupakan duplikat.'],
                ]);
            }

            // Submit to Unified Approval Engine
            $context = [
                'department_id' => (int) $header->dept_id,
                'branch' => $header->branch,
                'is_design' => (bool) $header->is_design,
            ];

            try {
                app(\App\Application\Approval\Contracts\Approvals::class)->submit($header, Auth::id(), $context);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Overtime Approval Engine Error: ' . $e->getMessage());
                throw ValidationException::withMessages([
                    'items' => ['Sistem tidak dapat menentukan alur persetujuan: ' . $e->getMessage()],
                ]);
            }

            return $header;
        });
    }

    private static function determineIsPlanned(Collection $data): bool
    {
        $items = $data->get('items', []);
        $first = $items[0] ?? null;

        if ($first && ! empty($first['start_date'])) {
            return Carbon::parse($first['start_date'])->greaterThan(now('Asia/Jakarta'));
        }

        return false;
    }

    private static function createManualDetails(array $items, int $headerId, bool $isAfterHour): int
    {
        $rows = collect($items)
            ->filter(fn ($i) => ! empty($i['nik']) && ! empty($i['overtime_date']))
            ->map(function ($i) {
                $start = Carbon::parse(trim(($i['start_date'] ?? '') . ' ' . ($i['start_time'] ?? '00:00')));
                $end = Carbon::parse(trim(($i['end_date'] ?? '') . ' ' . ($i['end_time'] ?? '00:00')));

                if ($end->lt($start)) {
                    return;
                }

                $i['_start'] = $start;
                $i['_end'] = $end;
                $i['break'] = (int) ($i['break'] ?? 0);

                return $i;
            })
            ->filter();

        if ($rows->isEmpty()) {
            return 0;
        }

        // TIRED CONFLICT GUARD: Perform final check against local database before bulk insert
        // This handles cases where two users might submit similar entries simultaneously
        $pairs = $rows->map(fn ($i) => [$i['nik'], $i['overtime_date']])->unique()->values();

        $existing = OvertimeFormDetail::query()
            ->whereIn('NIK', $pairs->pluck(0))
            ->whereIn('overtime_date', $pairs->pluck(1))
            ->where('header_id', '!=', $headerId) // Edit Mode Safety
            ->get(['NIK', 'overtime_date'])
            ->map(fn ($d) => $d['NIK'] . '|' . $d['overtime_date'])
            ->all();

        $inserts = [];
        foreach ($rows as $i) {
            $key = $i['nik'] . '|' . $i['overtime_date'];
            if (in_array($key, $existing, true)) {
                continue;
            }

            $inserts[] = [
                'header_id' => $headerId,
                'NIK' => $i['nik'],
                'name' => $i['name'] ?? null,
                'overtime_date' => $i['overtime_date'],
                'job_desc' => $i['job_desc'] ?? null,
                'start_date' => $i['_start']->toDateString(),
                'start_time' => $i['_start']->format('H:i:s'),
                'end_date' => $i['_end']->toDateString(),
                'end_time' => $i['_end']->format('H:i:s'),
                'break' => $i['break'] ?? 0,
                'remarks' => $i['remarks'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (empty($inserts)) {
            return 0;
        }

        OvertimeFormDetail::insert($inserts);

        return count($inserts);
    }
}
