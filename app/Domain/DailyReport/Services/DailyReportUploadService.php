<?php

declare(strict_types=1);

namespace App\Domain\DailyReport\Services;

use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use App\Infrastructure\Persistence\Eloquent\Models\EmployeeDailyReport;
use App\Infrastructure\Persistence\Eloquent\Models\EmployeeDailyReportLog;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

final class DailyReportUploadService
{
    /**
     * Process Excel file upload and return preview data.
     */
    public function processExcelUpload($file): array
    {
        $spreadsheets = Excel::toArray(new \stdClass(), $file);
        $data = $spreadsheets[0] ?? [];

        if (count($data) < 2) {
            return [
                'success' => false,
                'message' => 'File kosong atau tidak valid.',
                'data' => [],
            ];
        }

        $headerRow = $data[0];
        unset($data[0]);

        $previewData = [];

        foreach ($data as $rowIndex => $row) {
            $record = $this->parseRowData($row, $headerRow);

            if (! $this->isValidEmployeeRecord($record)) {
                continue;
            }

            $employeeData = $this->extractEmployeeData($record);
            $sessions = $this->extractWorkSessions($record, $employeeData);

            $previewData = array_merge($previewData, $sessions);
        }

        return [
            'success' => true,
            'data' => $previewData,
        ];
    }

    /**
     * Confirm and save uploaded data.
     */
    public function confirmUpload(array $rows): array
    {
        $logs = [];

        foreach ($rows as $row) {
            $logEntry = $row;
            $logEntry['logged_at'] = now();

            try {
                $isDuplicate = EmployeeDailyReport::where($row)->exists();

                if ($isDuplicate) {
                    $logEntry['status'] = 'Duplikat';
                    $logEntry['message'] = 'Data sudah ada, dilewati.';
                } else {
                    EmployeeDailyReport::create($row);
                    $logEntry['status'] = 'Berhasil';
                    $logEntry['message'] = null;
                }

                $logs[] = $logEntry;
                EmployeeDailyReportLog::create($logEntry);
            } catch (\Exception $e) {
                $logEntry['status'] = 'Gagal';
                $logEntry['message'] = $e->getMessage();
                $logs[] = $logEntry;
                EmployeeDailyReportLog::create($logEntry);
            }
        }

        return $logs;
    }

    /**
     * Parse row data with headers.
     */
    private function parseRowData(array $row, array $headerRow): array
    {
        $record = [];
        $usedColumns = [];

        foreach ($row as $colIndex => $value) {
            $originalKey = $headerRow[$colIndex] ?? null;

            if ($originalKey === null) {
                continue;
            }

            $key = trim($originalKey);

            // Handle duplicate column names
            if (isset($usedColumns[$key])) {
                $suffix = ++$usedColumns[$key];
                $key .= '.' . $suffix;
            } else {
                $usedColumns[$key] = 0;
            }

            $record[$key] = $value;
        }

        return $record;
    }

    /**
     * Validate employee record.
     */
    private function isValidEmployeeRecord(array $record): bool
    {
        if (! isset($record['ID Karyawan (8 Digit) (Contoh: 39001234)'])) {
            return false;
        }

        $employeeIdRaw = trim($record['ID Karyawan (8 Digit) (Contoh: 39001234)']);
        $normalizedId = str_replace([' ', '-'], '', $employeeIdRaw);

        return preg_match('/^\d{8}$/', $normalizedId);
    }

    /**
     * Extract employee data from record.
     */
    private function extractEmployeeData(array $record): array
    {
        $employeeIdRaw = trim($record['ID Karyawan (8 Digit) (Contoh: 39001234)']);
        $normalizedId = str_replace([' ', '-'], '', $employeeIdRaw);

        $department = substr($normalizedId, 0, 3);
        $employeeId = substr($normalizedId, 3);

        $employee = Employee::where('nik', $employeeId)->first();

        try {
            $submittedAt = is_numeric($record['Timestamp'])
                ? Carbon::instance(Date::excelToDateTimeObject($record['Timestamp']))
                : Carbon::parse($record['Timestamp']);

            $workDate = is_numeric($record['Tanggal Bekerja'])
                ? Carbon::instance(Date::excelToDateTimeObject($record['Tanggal Bekerja']))->toDateString()
                : Carbon::parse($record['Tanggal Bekerja'])->toDateString();
        } catch (\Exception $e) {
            return [];
        }

        return [
            'employee_id' => $employeeId,
            'employee_name' => $employee?->name ?? 'Unknown',
            'department_id' => $employee?->dept_code ?? 'Unknown',
            'submitted_at' => $submittedAt->format('Y-m-d H:i:s'),
            'work_date' => $workDate,
            'record' => $record,
        ];
    }

    /**
     * Extract work sessions from record.
     */
    private function extractWorkSessions(array $record, array $employeeData): array
    {
        if (empty($employeeData)) {
            return [];
        }

        $sessions = [];

        for ($i = 1; $i <= 12; $i++) {
            $jamKey = "Jam Sesi $i";
            $descKey = $i === 1
                ? 'Deskripsi Pekerjaan yang dilakukan'
                : 'Deskripsi Pekerjaan yang dilakukan.' . ($i - 1);
            $proofKey = $i === 1 ? 'Bukti Pekerjaan' : 'Bukti Pekerjaan.' . ($i - 1);

            if (empty($employeeData['record'][$jamKey]) || empty($employeeData['record'][$descKey])) {
                continue;
            }

            $sessions[] = [
                'submitted_at' => $employeeData['submitted_at'],
                'report_type' => 'Baru',
                'employee_id' => $employeeData['employee_id'],
                'employee_name' => $employeeData['employee_name'],
                'departement_id' => $employeeData['department_id'],
                'work_date' => $employeeData['work_date'],
                'work_time' => trim($employeeData['record'][$jamKey]),
                'work_description' => trim($employeeData['record'][$descKey]),
                'proof_url' => $employeeData['record'][$proofKey] ?? null,
                'sort_datetime' => $this->calculateSortDatetime($employeeData['work_date'], trim($employeeData['record'][$jamKey])),
            ];
        }

        return $sessions;
    }

    /**
     * Consistently calculate sort_datetime from date and time string.
     */
    private function calculateSortDatetime(string $date, string $time): ?Carbon
    {
        $timePart = '00:00:00';

        // Handle "HH:MM - HH:MM"
        if (str_contains($time, '-')) {
            $parts = explode('-', $time);
            $endTime = trim(end($parts));
            if (preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]/', $endTime, $matches)) {
                $timePart = $matches[0] . ':00';
            }
        } 
        // Handle "HH:MM"
        elseif (preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]/', $time, $matches)) {
            $timePart = $matches[0] . ':00';
        }

        try {
            return Carbon::parse("$date $timePart");
        } catch (\Exception $e) {
            return Carbon::parse($date);
        }
    }
}
