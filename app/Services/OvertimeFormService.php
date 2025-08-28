<?php

namespace App\Services;

use App\Models\{HeaderFormOvertime, ApprovalFlow, DetailFormOvertime};
use App\Support\ApprovalFlowResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\OvertimeImport;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OvertimeFormService
{
    public static function create(Collection $data): HeaderFormOvertime
    {
        return DB::transaction(function () use ($data) {
            $excelFile = $data->get("excel_file");
            $isPlanned = self::determineIsPlanned($data, $excelFile);
            $headerData = self::buildHeaderData($data, $isPlanned);

            // Resolve the approval flow
            $flowSlug = ApprovalFlowResolver::for($headerData);
            $flow = ApprovalFlow::where("slug", $flowSlug)->firstOrFail();
            $headerData["approval_flow_id"] = $flow->id;

            // Create header first to obtain ID, but DO NOT seed approvals yet
            $header = HeaderFormOvertime::create($headerData);

            // Create details (Excel vs manual)
            $createdCount = 0;
            if ($excelFile instanceof UploadedFile && $excelFile->isValid()) {
                $createdCount = self::importFromExcel(
                    $excelFile,
                    $header->id,
                    (bool) $header->is_after_hour,
                );
            } else {
                $createdCount = self::createManualDetails(
                    $data->get("items", []),
                    $header->id,
                    (bool) $header->is_after_hour,
                );
            }

            if ($createdCount === 0) {
                // force rollback, no header or approvals persist
                throw ValidationException::withMessages([
                    "items" => ["Tidak ada baris lembur yang valid untuk disimpan"],
                ]);
            }

            // Sees approval steps only when details exist
            foreach ($flow->steps as $step) {
                $header->approvals()->create([
                    "flow_step_id" => $step->id,
                    "status" => "pending",
                ]);
            }

            return $header;
        });
    }

    private static function buildHeaderData(Collection $data, bool $isPlanned): array
    {
        return [
            "user_id" => Auth::id(),
            "dept_id" => $data->get("dept_id"),
            "branch" => $data->get("branch"),
            "is_design" => $data->get("design"),
            "is_export" => 0,
            "is_planned" => $isPlanned,
            "is_after_hour" => $data->get("is_after_hour"),
            "status" => "waiting-creator",
        ];
    }

    private static function determineIsPlanned(Collection $data, $excelFile): bool
    {
        if ($excelFile instanceof UploadedFile && $excelFile->isValid()) {
            $rows = Excel::toArray([], $excelFile);
            $sheet0 = $rows[0] ?? [];
            $dataRows = array_slice($sheet0, 3); // skip header rows if your template uses 0-2
            $firstRow = $dataRows[0] ?? null;

            if ($firstRow && isset($firstRow[3])) {
                $start = self::excelSerialToCarbon($firstRow[3]);
                return $start->greaterThan(now());
            }
            return false;
        }

        $items = $data->get("items", []);
        $first = $items[0] ?? null;

        if ($first && !empty($first["start_date"])) {
            // If you store seperate time fields, you can include them here too
            return Carbon::parse($first["start_date"])->greaterThan(now("Asia/Jakarta"));
        }

        return false;
    }

    private static function excelSerialToCarbon($serial): Carbon
    {
        $serial = (float) $serial;
        $days = (int) floor($serial);
        $seconds = (int) round(($serial - $days) * 86400);

        $base = Carbon::create(1899, 12, 30, 0, 0, 0, config("app.timezone")); // 1900 system
        return $base->copy()->addDays($days)->addSeconds($seconds);
    }

    private static function importFromExcel($file, int $headerId, bool $isAfterHour): int
    {
        $path = $file->store("temp");
        $import = new OvertimeImport($headerId, $isAfterHour);

        // use absolute path
        Excel::import($import, storage_path("app/" . $path));
        Storage::delete($path);

        return (int) ($import->createdCount ?? 0);
    }

    private static function createManualDetails(array $items, int $headerId, bool $isAfterHour): int
    {
        // Normalize & pre-validate rows
        $rows = collect($items)
            ->filter(
                fn($i) => !empty($i["nik"]) &&
                    !empty($i["overtime_date"]) &&
                    !empty($i["start_date"]) &&
                    !empty($i["end_date"]),
            )
            ->map(function ($i) {
                // Combine date & time safely
                $start = Carbon::parse(
                    trim(($i["start_date"] ?? "") . " " . ($i["start_time"] ?? "00:00")),
                );
                $end = Carbon::parse(
                    trim(($i["end_date"] ?? "") . " " . ($i["end_time"] ?? "00:00")),
                );

                if ($end->lt($start)) {
                    return null;
                }

                $i["_start"] = $start;
                $i["_end"] = $end;
            })
            ->filter();

        if ($rows->isEmpty()) {
            return 0;
        }

        // Build unique pair list and preftech existing once (avoid N+1)
        $pairs = $rows->map(fn($i) => [$i["nik"], $i["overtime_date"]])->unique()->values();

        $existing = DetailFormOvertime::query()
            ->whereHas("header", fn($q) => $q->where("is_after_hour", $isAfterHour))
            ->whereIn("NIK", $pairs->pluck(0))
            ->whereIn("overtime_date", $pairs->pluck(1))
            ->get(["NIK", "overtime_date"])
            ->map(fn($d) => $d["NIK"] . "|" . $d["overtime_date"])
            ->all();

        $inserts = [];
        foreach ($rows as $i) {
            $key = $i["nik"] . "|" . $i["overtime_date"];
            if (in_array($key, $existing, true)) {
                continue;
            }
            $inserts[] = [
                "header_id" => $headerId,
                "NIK" => $i["nik"],
                "name" => $i["name"] ?? null,
                "overtime_date" => $i["overtime_date"],
                "job_desc" => $i["job_desc"] ?? null,
                "start_date" => $i["_start"]->toDateString(),
                "start_time" => $i["_start"]->format("H:i:s"),
                "end_date" => $i["_end"]->toDateString(),
                "end_time" => $i["_end"]->format("H:i:s"),
                "break" => $i["break"] ?? 0,
                "remarks" => $i["remarks"] ?? null,
                "created_at" => now(),
                "updated_at" => now(),
            ];
        }

        if (empty($inserts)) {
            return 0;
        }

        DetailFormOvertime::insert($inserts);
        return count($inserts);
    }
}
