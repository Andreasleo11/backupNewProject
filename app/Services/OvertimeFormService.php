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
        $isPlanned = self::isPlanned($data);
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
            $createdCount = self::importFromExcel($excelFile, $header->id);
            if ($createdCount === 0) {
                $header->delete();
                return 'excel-empty';
            }
        } else {
            $createdCount = self::createManualDetails($data->get('items', []), $header->id);
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
            'status' => 'waiting-creator',
        ];
    }

    private static function isPlanned(Collection $data): bool
    {
        $items = $data->get('items', []);
        return isset($items[0]['start_date']) && $items[0]['start_date'] > now();
    }

    private static function importFromExcel($file, int $headerId): int
    {
        $path = $file->store('temp');
        $import = new OvertimeImport($headerId);
        Excel::import($import, $path);
        Storage::delete($path);
        return $import->createdCount;
    }

    private static function createManualDetails(array $items, int $headerId): int
    {
        $createdCount = 0;

        foreach ($items as $data) {
            if (strtotime($data['end_date']) < strtotime($data['start_date'])) continue;

            $exists = DetailFormOvertime::where('NIK', $data['nik'])
                ->where('overtime_date', $data['overtime_date'])
                ->exists();

            if ($exists) continue;

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
