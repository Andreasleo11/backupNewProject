<?php

namespace App\Services;

use App\Models\{HeaderFormOvertime, ApprovalFlow, DetailFormOvertime};
use App\Support\ApprovalFlowResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\OvertimeImport;

class OvertimeFormService
{
    public static function create(Collection $data): HeaderFormOvertime|string
    {
        $excelFile = $data->get('excel_file');
        $isPlanned = false;

        // Determine isPlanned based on Excel or manual input
        if ($excelFile instanceof \Illuminate\Http\UploadedFile && $excelFile->isValid()) {
            $rows = Excel::toArray([], $excelFile);
            $dataRows = array_slice($rows[0], 3);

            $firstRow = $dataRows[0] ?? null;

            if ($firstRow && isset($firstRow[3])) {
                $startDate = self::excelDateToCarbon($firstRow[3]);
                $isPlanned = $startDate->greaterThan(now());
            }
        } else {
            $isPlanned = self::isPlanned($data);
        }
        $headerData = self::buildHeaderData($data, $isPlanned);

        // Determine approval flow
        $flowSlug = ApprovalFlowResolver::for($headerData);
        $flow = ApprovalFlow::where('slug', $flowSlug)->firstOrFail();
        $headerData['approval_flow_id'] = $flow->id;

        // Create header
        $header = HeaderFormOvertime::create($headerData);

        // Pre-seed approval steps
        foreach ($flow->steps as $step) {
            $header->approvals()->create([
                'flow_step_id' => $step->id,
                'status' => 'pending',
            ]);
        }

        // Process details
        $excelFile = $data->get('excel_file');

        if ($excelFile instanceof \Illuminate\Http\UploadedFile && $excelFile->isValid()) {
            $createdCount = self::importFromExcel($excelFile, $header->id, $header->is_after_hour);
            if ($createdCount === 0) {
                $header->delete();
                return 'excel-empty';
            }
        } else {
            $createdCount = self::createManualDetails($data->get('items', []), $header->id, $header->is_after_hour);
            if ($createdCount === 0) {
                $header->delete();
                return 'manual-empty';
            }
        }

        return $header;
    }

    private static function buildHeaderData(Collection $data, bool $isPlanned): array
    {
        return [
            'user_id' => Auth::id(),
            'dept_id' => $data->get('dept_id'),
            'branch' => $data->get('branch'),
            'is_design' => $data->get('design'),
            'is_export' => 0,
            'is_planned' => $isPlanned,
            'is_after_hour' => $data->get('is_after_hour'),
            'status' => 'waiting-creator',
        ];
    }

    private static function isPlanned(Collection $data): bool
    {
        $items = $data->get('items', []);
        return isset($items[0]['start_date']) && $items[0]['start_date'] < now();
    }

    private static function excelDateToCarbon($serial)
    {
        return \Carbon\Carbon::createFromDate(1899, 12, 30)->addDays($serial);
    }

    private static function importFromExcel($file, int $headerId, bool $isAfterHour): int
    {
        $path = $file->store('temp');
        $import = new OvertimeImport($headerId, $isAfterHour);
        Excel::import($import, $path);
        Storage::delete($path);
        return $import->createdCount;
    }

    private static function createManualDetails(array $items, int $headerId, bool $isAfterHour): int
    {
        $createdCount = 0;

        foreach ($items as $data) {
            if (strtotime($data['end_date']) < strtotime($data['start_date'])) continue;

            $duplicate = DetailFormOvertime::where('NIK', $data['nik'])
                ->where('overtime_date', $data['overtime_date'])
                ->whereHas('header', function ($query) use ($isAfterHour) {
                    $query->where('is_after_hour', $isAfterHour);
                })
                ->exists();

            if ($duplicate) continue;

            DetailFormOvertime::create([
                'header_id' => $headerId,
                'NIK' => $data['nik'],
                'name' => $data['name'],
                'overtime_date' => $data['overtime_date'],
                'job_desc' => $data['job_desc'],
                'start_date' => $data['start_date'],
                'start_time' => $data['start_time'],
                'end_date' => $data['end_date'],
                'end_time' => $data['end_time'],
                'break' => $data['break'],
                'remarks' => $data['remarks'],
            ]);

            $createdCount++;
        }

        return $createdCount;
    }
}
