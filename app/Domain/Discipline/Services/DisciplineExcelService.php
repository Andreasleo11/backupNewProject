<?php

namespace App\Domain\Discipline\Services;

use App\Exports\DesciplineDataExp;
use App\Imports\DesciplineDataImport;
use App\Imports\DesciplineYayasanDataImport;
use App\Models\EvaluationData;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class DisciplineExcelService
{
    /**
     * Import regular employee discipline data from Excel files.
     */
    public function importRegularData(array $files): string
    {
        $processedData = $this->processFiles($files, [2, 3]);
        $filename = 'DisciplineData.xlsx';

        Excel::store(new DesciplineDataExp($processedData), 'public/Evaluation/' . $filename);

        $this->importRegularFromFile($filename);

        return 'Excel file imported successfully.';
    }

    /**
     * Import Yayasan employee discipline data from Excel files.
     */
    public function importYayasanData(array $files): string
    {
        $processedData = $this->processYayasanFiles($files);
        $filename = 'DisciplineDataYayasan.xlsx';

        Excel::store(new DesciplineDataExp($processedData), 'public/Evaluation/' . $filename);

        $this->importYayasanFromFile($filename);

        return 'Excel file imported successfully.';
    }

    /**
     * Process uploaded Excel files by removing specified columns.
     */
    private function processFiles(array $files, array $columnsToRemove): array
    {
        $allData = [];

        foreach ($files as $file) {
            $data = Excel::toArray([], $file);
            array_shift($data[0]); // Remove header row

            foreach ($data[0] as &$row) {
                foreach ($columnsToRemove as $columnIndex) {
                    unset($row[$columnIndex]);
                }
                $row = array_values($row); // Re-index
            }

            $allData = array_merge($allData, $data[0]);
        }

        return $allData;
    }

    /**
     * Process Yayasan Excel files with specific column removal logic.
     */
    private function processYayasanFiles(array $files): array
    {
        $allData = [];

        foreach ($files as $file) {
            $data = Excel::toArray([], $file);
            array_shift($data[0]); // Remove header

            foreach ($data[0] as &$row) {
                unset($row[0], $row[2], $row[3], $row[4], $row[5], $row[7], $row[8]);
                $row = array_values($row);
            }

            $allData = array_merge($allData, $data[0]);
        }

        return $allData;
    }

    /**
     * Import regular employee data from stored Excel file.
     */
    private function importRegularFromFile(string $filename): void
    {
        $import = new DesciplineDataImport;
        $data = Excel::toArray($import, 'public/Evaluation/' . $filename)[0];

        $uniqueNIKs = array_unique(array_column($data, 1));
        $existingRecords = EvaluationData::whereIn('NIK', $uniqueNIKs)->get();

        // Replace nulls with zeros
        foreach ($data as &$dat) {
            foreach ($dat as &$value) {
                if ($value === null) {
                    $value = 0;
                }
            }
        }

        $this->processRegularImport($data, $existingRecords);
    }

    /**
     * Process regular employee import data and update database.
     */
    private function processRegularImport(array $data, $existingRecords): void
    {
        $maxpoint = 40;

        foreach ($data as $row) {
            foreach ($existingRecords as $record) {
                if ($record->NIK === $row[1] && $record->Month === $row[2]) {
                    $calculatedPoints = $record->Alpha * 10 + $record->Izin * 2 +
                                      $record->Sakit + $record->Telat * 0.5;
                    $totalPoints = $maxpoint - $calculatedPoints;

                    $total = 0;

                    // Calculate scores for columns 3-7
                    for ($k = 3; $k <= 7; $k++) {
                        if ($k == 7) {
                            // Prestasi column (special scoring)
                            $total += match ($row[$k]) {
                                'A' => 20, 'B' => 15, 'C' => 10, 'D' => 5, default => 0
                            };
                        } else {
                            $total += match ($row[$k]) {
                                'A' => 10, 'B' => 7.5, 'C' => 5, 'D' => 2.5, default => 0
                            };
                        }
                    }

                    $total += $totalPoints;

                    EvaluationData::where('id', $record->id)->update([
                        'kerajinan_kerja' => $row[3],
                        'kerapian_kerja' => $row[4],
                        'loyalitas' => $row[5],
                        'perilaku_kerja' => $row[6],
                        'prestasi' => $row[7],
                        'total' => $total,
                    ]);
                }
            }
        }
    }

    /**
     * Import Yayasan employee data from stored Excel file.
     */
    private function importYayasanFromFile(string $filename): void
    {
        $import = new DesciplineYayasanDataImport;
        $data = Excel::toArray($import, 'public/Evaluation/' . $filename)[0];
        $pengawas = Auth::user();

        $uniqueNIKs = array_unique(array_column($data, 0));
        $existingRecords = EvaluationData::whereIn('NIK', $uniqueNIKs)->get();

        // Replace nulls with zeros
        foreach ($data as &$dat) {
            foreach ($dat as &$value) {
                if ($value === null) {
                    $value = 0;
                }
            }
        }

        $this->processYayasanImport($data, $existingRecords, $pengawas);
    }

    /**
     * Process Yayasan employee import data and update database.
     */
    private function processYayasanImport(array $data, $existingRecords, $pengawas): void
    {
        $scoringSystem = [
            2 => ['A' => 17, 'B' => 14, 'C' => 11, 'D' => 8, 'E' => 0],
            3 => ['A' => 16, 'B' => 13, 'C' => 10, 'D' => 7, 'E' => 0],
            4 => ['A' => 11, 'B' => 9, 'C' => 7, 'D' => 4, 'E' => 0],
            5 => ['A' => 8, 'B' => 6, 'C' => 5, 'D' => 3, 'E' => 0],
            6 => ['A' => 10, 'B' => 8, 'C' => 6, 'D' => 4, 'E' => 0],
            7 => ['A' => 10, 'B' => 8, 'C' => 6, 'D' => 4, 'E' => 0],
            8 => ['A' => 10, 'B' => 8, 'C' => 6, 'D' => 4, 'E' => 0],
            9 => ['A' => 10, 'B' => 8, 'C' => 6, 'D' => 4, 'E' => 0],
            10 => ['A' => 8, 'B' => 6, 'C' => 5, 'D' => 3, 'E' => 0],
        ];

        foreach ($data as $row) {
            foreach ($existingRecords as $record) {
                if ($record->NIK === $row[0] && $record->Month === $row[1]) {
                    $calculatedPoints = $record->Alpha * 10 + $record->Izin * 2 +
                                      $record->Sakit + $record->Telat * 0.5;

                    $total = 0;
                    for ($k = 2; $k <= 10; $k++) {
                        $value = $row[$k];
                        if (isset($scoringSystem[$k][$value])) {
                            $total += $scoringSystem[$k][$value];
                        }
                    }

                    $total -= $calculatedPoints;

                    $isDifferent =
                        $row[2] != $record->kemampuan_kerja ||
                        $row[3] != $record->kecerdasan_kerja ||
                        $row[4] != $record->qualitas_kerja ||
                        $row[5] != $record->disiplin_kerja ||
                        $row[6] != $record->kepatuhan_kerja ||
                        $row[7] != $record->lembur ||
                        $row[8] != $record->efektifitas_kerja ||
                        $row[9] != $record->relawan ||
                        $row[10] != $record->integritas;

                    if ($isDifferent) {
                        EvaluationData::where('id', $record->id)->update([
                            'kemampuan_kerja' => $row[2],
                            'kecerdasan_kerja' => $row[3],
                            'qualitas_kerja' => $row[4],
                            'disiplin_kerja' => $row[5],
                            'kepatuhan_kerja' => $row[6],
                            'lembur' => $row[7],
                            'efektifitas_kerja' => $row[8],
                            'relawan' => $row[9],
                            'integritas' => $row[10],
                            'total' => $total,
                            'pengawas' => $pengawas->name,
                            'depthead' => null,
                            'generalmanager' => null,
                        ]);
                    } else {
                        EvaluationData::where('id', $record->id)->update([
                            'depthead' => null,
                            'generalmanager' => null,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Export Yayasan employee data with grade categorization.
     */
    public function exportYayasan(int $month): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $currentYear = \Carbon\Carbon::now()->year;
        $cutoffDate = \Carbon\Carbon::createFromDate($currentYear, $month, 1)
            ->copy()
            ->subMonths(6)
            ->startOfMonth();

        $employees = EvaluationData::with('karyawan')
            ->whereHas('karyawan', function ($query) use ($cutoffDate) {
                $query->whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG'])
                    ->where('start_date', '<', $cutoffDate);
            })
            ->whereMonth('month', $month)
            ->get();

        $result = $this->categorizeEmployeesByGrade($employees);

        $currentDate = \Carbon\Carbon::now()->format('d-m-y');
        $fileName = "DataYayasan_{$currentDate}.xlsx";

        return Excel::download(new \App\Exports\YayasanDisciplineExport($result), $fileName);
    }

    /**
     * Export full Yayasan employee data.
     */
    public function exportYayasanFull(int $month): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $currentYear = \Carbon\Carbon::now()->year;
        $cutoffDate = \Carbon\Carbon::createFromDate($currentYear, $month, 1)
            ->copy()
            ->subMonths(6)
            ->startOfMonth();

        $employees = EvaluationData::with('karyawan')
            ->whereHas('karyawan', function ($query) use ($cutoffDate) {
                $query->whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG'])
                    ->where('start_date', '<', $cutoffDate);
            })
            ->whereMonth('month', $month)
            ->get();

        $currentDate = \Carbon\Carbon::now()->format('d-m-y');
        $fileName = "DataYayasanFull_{$currentDate}.xlsx";

        return Excel::download(new \App\Exports\YayasanDisciplineFullExport($employees), $fileName);
    }

    /**
     * Export Yayasan data for Jpayroll with grade categorization.
     */
    public function exportYayasanJpayrollFunction(int $month, int $year): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $cutoffDate = \Carbon\Carbon::createFromDate($year, $month, 1)
            ->copy()
            ->subMonths(6)
            ->startOfMonth();

        $employees = EvaluationData::with('karyawan')
            ->whereHas('karyawan', function ($query) use ($cutoffDate) {
                $query->whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG'])
                    ->where('start_date', '<', $cutoffDate);
            })
            ->whereMonth('month', $month)
            ->get();

        $result = $this->categorizeEmployeesByGrade($employees);

        $currentDate = \Carbon\Carbon::now()->format('d-m-y');
        $fileName = "DataYayasan_{$currentDate}.xlsx";

        return Excel::download(new \App\Exports\YayasanDisciplineExport($result), $fileName);
    }

    /**
     * Categorize employees by their grade (A or B rating).
     */
    private function categorizeEmployeesByGrade($employees): array
    {
        $result = [];

        foreach ($employees as $data) {
            $employeeId = $data->karyawan->NIK;

            if (! isset($result[$employeeId])) {
                $result[$employeeId] = [
                    'employee_id' => $employeeId,
                    'nilai_A' => 0,
                    'nilai_B' => 0,
                ];
            }

            $total = $data->total;

            if ($total >= 91) {
                $result[$employeeId]['nilai_A'] = 1;
                $result[$employeeId]['nilai_B'] = 0;
            } elseif ($total >= 71 && $total <= 90) {
                $result[$employeeId]['nilai_A'] = 0;
                $result[$employeeId]['nilai_B'] = 1;
            } else {
                $result[$employeeId]['nilai_A'] = 0;
                $result[$employeeId]['nilai_B'] = 0;
            }
        }

        return array_values($result);
    }
}
