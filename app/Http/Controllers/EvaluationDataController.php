<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EvaluationData;
use App\Exports\EvaluationDataExp;
use App\Imports\EvaluationDataImport;

use App\DataTables\EvaluationDataDataTable;

use Illuminate\Http\Request;


use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Redirect;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Storage;
use DateTime;

class EvaluationDataController extends Controller
{
    public function index(EvaluationDataDataTable $dataTable)
    {   
        $datas = EvaluationData::with('karyawan')->get();
        // dd($datas);
        return $dataTable->render("setting.evaluationindex", compact("datas"));
    }

    public function update(Request $request)
    {
        $uploadedFiles = $request->file('excel_files');
        
        $excelFileName = $this->processExcelFile($uploadedFiles);
        $this->importExcelFile($excelFileName);
        return redirect()->back();
    }

    public function processExcelFile($files)
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

        Excel::store(new EvaluationDataExp($allData), 'public/Evaluation/' . $excelFileName);

        // $filePath = Storage::url($fileName);
        return $excelFileName; 
       
    }



    public function importExcelFile($excelFileName)
    {
        Excel::import(new EvaluationDataImport,  public_path('/storage/Evaluation/' . $excelFileName));

        // If the import is successful, return a success message or any other response
        return 'Excel file imported successfully.';
    }
}
