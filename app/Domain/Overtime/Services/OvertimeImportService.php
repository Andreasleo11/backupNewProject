<?php

declare(strict_types=1);

namespace App\Domain\Overtime\Services;

use App\Models\ActualOvertimeDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

final class OvertimeImportService
{
    /**
     * Import overtime data from Excel file.
     */
    public function importFromExcel(string $filePath): array
    {
        $data = IOFactory::load($filePath);
        $sheet = $data->getActiveSheet();
        $rows = $sheet->toArray();

        DB::beginTransaction();

        try {
            $imported = 0;
            $skipped = 0;

            foreach ($rows as $index => $row) {
                // Skip header rows (0-3) and empty rows
                if ($index < 4 || empty($row[0])) {
                    $skipped++;
                    continue;
                }

                // Extract key from first column (LINE X ID format)
                if (preg_match("/LINE\s*(\d+)\s*ID/", $row[0], $matches)) {
                    $key = intval($matches[1]);
                } else {
                    $skipped++;
                    continue;
                }

                $overtimeData = $this->parseRowData($row);

                if ($overtimeData) {
                    ActualOvertimeDetail::updateOrCreate(
                        ['key' => $key],
                        $overtimeData
                    );
                    $imported++;
                } else {
                    $skipped++;
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'File berhasil diimpor',
                'imported' => $imported,
                'skipped' => $skipped,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Gagal impor: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Parse Excel row data into overtime detail attributes.
     */
    private function parseRowData(array $row): ?array
    {
        try {
            return [
                'voucher' => strval($row[1]),
                'in_date' => $this->parseDate($row[2] ?? null),
                'in_time' => $this->parseTime($row[3] ?? null),
                'out_date' => $this->parseDate($row[4] ?? null),
                'out_time' => $this->parseTime($row[5] ?? null),
                'nett_overtime' => is_numeric($row[6] ?? null) ? floatval($row[6]) : null,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse date from Excel (numeric or string format).
     */
    private function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Excel numeric date
        if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value)->format('Y-m-d');
        }

        // String date in dd/mm/yyyy format
        if (preg_match("/\d{2}\/\d{2}\/\d{4}/", $value)) {
            try {
                return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Parse time from Excel (numeric or string format).
     */
    private function parseTime($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Excel numeric time
        if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value)->format('H:i:s');
        }

        // Already string time
        return $value;
    }
}
