<?php


namespace App\Imports;

use App\Models\Employee;
use App\Models\DetailFormOvertime;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class OvertimeImport implements ToCollection
{
    protected $headerOvertimeId;

    public function __construct($headerOvertimeId)
    {
        $this->headerOvertimeId = $headerOvertimeId;
    }

    public function collection(Collection $rows)
    {
        // Skip the first three rows (headers)
        $dataRows = $rows->slice(3);

        foreach ($dataRows as $row)
        {
            $employeeId = $row[0];
            $employee = Employee::where('NIK', $employeeId)->first();

            if ($employee) {
                DetailFormOvertime::create([
                    'header_id' => $this->headerOvertimeId,
                    'NIK' => $employee->NIK,
                    'nama' =>$employee->Nama,
                    'overtime_date' => $this->parseDate($row[1]),
                    'job_desc' => $row[2],
                    'start_date' => $this->parseDate($row[3]),
                    'start_time' => $this->parseTime($row[4]),
                    'end_date' => $this->parseDate($row[5]),
                    'end_time' => $this->parseTime($row[6]),
                    'break' => $row[7],
                    'remarks' => $row[8],
                ]);
            }
        }
    }

    private function parseDate($value)
    {
        // Check if value is numeric (Excel date format)
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
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
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('H:i:s');
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
