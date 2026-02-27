<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exports\EvaluationDataWeeklyExp;
use App\Imports\EvaluationWeeklyDataImport;
use Maatwebsite\Excel\Facades\Excel;

class EvaluationDataWeeklyController extends Controller
{
    public function weeklyIndex()
    {
        return view('setting.weeklyindex');
    }

    public function updateWeekly(Request $request)
    {
        $uploadedFiles = $request->file('excel_files');

        $excelFileName = $this->processExcelFileWeekly($uploadedFiles);
        $this->importExcelFileWeekly($excelFileName);

        return redirect()->back();
    }

    private function processExcelFileWeekly($files)
    {
        $allData = [];
        foreach ($files as $file) {
            // Read the XLS file
            $data = Excel::toArray([], $file);
            // Remove the first row (header)
            array_shift($data[0]);
            array_shift($data[0]);

            // Remove the first element from each row
            foreach ($data[0] as &$row) {
                array_splice($row, 1, 1); // Remove the second element
            }

            foreach ($data[0] as &$row) {
                // Convert the date string to a DateTime object
                $date = DateTime::createFromFormat('d/m/Y', $row[1]);
                $row[1] = $date->format('Y-m-d');
            }
            // Append data from this file to the allData array
            $allData = array_merge($allData, $data[0]);
        }

        $excelFileName = 'EvaluationData.xlsx';
        $excelFilePath = public_path($excelFileName);

        Excel::store(new EvaluationDataWeeklyExp($allData), 'public/Evaluation/' . $excelFileName);

        // $filePath = Storage::url($fileName);
        return $excelFileName;
    }

    private function importExcelFileWeekly($excelFileName)
    {
        Excel::import(
            new EvaluationWeeklyDataImport,
            public_path('/storage/Evaluation/' . $excelFileName),
        );

        // If the import is successful, return a success message or any other response
        return 'Excel file imported successfully.';
    }
}
