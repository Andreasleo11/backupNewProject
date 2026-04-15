<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\EvaluationDataImport;
use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use App\Models\EvaluationData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class EvaluationDataManagementController extends Controller
{
    /**
     * Display a listing of the Evaluation Data.
     */
    public function index(\App\DataTables\Admin\EvaluationDataManagementDataTable $dataTable)
    {
        return $dataTable->render('administration.evaluation-data.index');
    }

    /**
     * Store and scan the uploaded Excel/CSV file for integrity reporting.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('temp_imports');

        try {
            // Read data as simple array/collection for scanning
            $data = Excel::toCollection(new EvaluationDataImport, $file)->first();

            $report = [
                'new' => 0,
                'updates' => 0,
                'errors' => [],
                'total' => count($data),
                'temp_path' => $path,
            ];

            foreach ($data as $index => $row) {
                $nik = trim($row['nik'] ?? $row['NIK'] ?? $row['employee_id'] ?? '');
                $monthInput = $row['month'] ?? $row['Month'] ?? $row['periode'] ?? $row['Periode'] ?? null;

                if (empty($nik)) {
                    continue;
                }

                // Integrity Check: Employee Existence
                $employee = Employee::where('nik', $nik)->first();
                if (! $employee) {
                    $report['errors'][] = 'Row ' . ($index + 2) . ": NIK '$nik' not found in Employee database.";
                    continue;
                }

                // Integrity Check: Month Format
                $month = null;
                try {
                    if ($monthInput) {
                        if (is_numeric($monthInput)) {
                            $month = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($monthInput)->format('Y-m-01');
                        } else {
                            try {
                                $month = Carbon::createFromFormat('d/m/Y', $monthInput)->startOfMonth()->format('Y-m-d');
                            } catch (\Exception $e) {
                                $month = Carbon::parse($monthInput)->startOfMonth()->format('Y-m-d');
                            }
                        }
                    }
                } catch (\Exception $e) {
                }

                if (! $month) {
                    $report['errors'][] = 'Row ' . ($index + 2) . ": Invalid month format ($monthInput). Use YYYY-MM.";
                    continue;
                }

                // Rule: If NIK + Month combo is unique, it's a NEW record. If it exists, it's an UPDATE.
                $exists = EvaluationData::where('NIK', $nik)->where('Month', $month)->exists();
                if ($exists) {
                    $report['updates']++;
                } else {
                    $report['new']++;
                }
            }

            return response()->json([
                'success' => true,
                'report' => $report,
                'message' => 'Integrity scan complete.',
            ]);

        } catch (\Exception $e) {
            Storage::delete($path);

            return response()->json(['success' => false, 'message' => 'Scan failed: ' . $e->getMessage()], 422);
        }
    }

    /**
     * Commit the scanned data to the database.
     */
    public function commit(Request $request)
    {
        $path = $request->temp_path;
        if (! $path || ! Storage::exists($path)) {
            return response()->json(['success' => false, 'message' => 'Temporary file not found or expired.'], 422);
        }

        try {
            Excel::import(new EvaluationDataImport, Storage::path($path));
            Storage::delete($path);

            return response()->json(['success' => true, 'message' => 'Data successfully imported to database.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Import failed: ' . $e->getMessage()], 422);
        }
    }

    /**
     * Remove the specified row from storage.
     */
    public function destroy($id)
    {
        $evaluation = EvaluationData::findOrFail($id);
        $evaluation->delete();

        return response()->json(['success' => true, 'message' => 'Data evaluation berhasil dihapus.']);
    }

    /**
     * Truncate or bulk delete evaluation data.
     */
    public function truncate()
    {
        EvaluationData::truncate();

        return response()->json(['success' => true, 'message' => 'Seluruh data berhasil dihapus.']);
    }
}
