<?php

namespace App\Imports;

use App\Models\DetailFormOvertime;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class OvertimeImport implements ToCollection
{
    protected $headerOvertimeId;

    protected $isAfterHour;

    public $createdCount = 0;

    public function __construct($headerOvertimeId, $isAfterHour)
    {
        $this->headerOvertimeId = $headerOvertimeId;
        $this->isAfterHour = $isAfterHour;
    }

    public function collection(Collection $rows)
    {
        $dataRows = $rows->slice(3); // Skip header rows

        foreach ($dataRows as $row) {
            $employeeId = $row[0];
            $employee = Employee::where('NIK', $employeeId)->first();

            if (! $employee) {
                continue;
            }

            $overtimeDate = $this->parseDate($row[1]);
            $jobDesc = $row[2];
            $startDate = $this->parseDate($row[3]);
            $startTime = $this->parseTime($row[4]);
            $endDate = $this->parseDate($row[5]);
            $endTime = $this->parseTime($row[6]);
            $break = $row[7];
            $remarks = $row[8];

            // ✅ Skip jika end_date < start_date
            if (strtotime($endDate) < strtotime($startDate)) {
                continue;
            }

            // ✅ Skip jika kombinasi NIK + overtime_date sudah ada
            $exists = DetailFormOvertime::where('NIK', $employee->NIK)
                ->where('overtime_date', $overtimeDate)
                ->whereHas('header', function ($query) {
                    $query->where('is_after_hour', $this->isAfterHour);
                })
                ->exists();

            if ($exists) {
                continue;
            }

            DetailFormOvertime::create([
                'header_id' => $this->headerOvertimeId,
                'NIK' => $employee->NIK,
                'name' => $employee->Nama,
                'overtime_date' => $overtimeDate,
                'job_desc' => $jobDesc,
                'start_date' => $startDate,
                'start_time' => $startTime,
                'end_date' => $endDate,
                'end_time' => $endTime,
                'break' => $break,
                'remarks' => $remarks,
            ]);
            $this->createdCount++;
        }
    }

    private function parseDate($value)
    {
        // Check if value is numeric (Excel date format)
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format(
                'Y-m-d',
            );
        } else {
            // Assume the value is a standard date string
            return \Carbon\Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        }
    }

    private function parseTime($value)
    {
        try {
            // Check if the value is a numeric time (Excel time format)
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format(
                    'H:i:s',
                );
            } else {
                // Attempt to parse various time formats
                $formats = ['H:i', 'H:i:s', 'g:i A', 'g:i:s A', 'H.i'];
                foreach ($formats as $format) {
                    $parsedTime = Carbon::createFromFormat($format, $value);
                    if ($parsedTime !== false) {
                        return $parsedTime->format('H:i:s');
                    }
                }

                // If none of the formats match, return null or handle the error as needed
                return null;
            }
        } catch (\Exception $e) {
            // Handle the exception if the format is invalid
            return null;
        }
    }
}
