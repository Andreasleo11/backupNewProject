<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EvaluationData;
use App\Models\Employee;
use App\Models\Department;
use App\Exports\EvaluationDataExp;
use App\Imports\EvaluationDataImport;
use App\Models\EvaluationDataWeekly;
use App\Exports\EvaluationDataWeeklyExp;
use App\Imports\EvaluationWeeklyDataImport;

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

    public function delete(Request $request)
    {
        $selectedMonth = $request->input('filter_status');
        $selectedYear = date('Y');

        $startDate = $selectedYear . '-' . $selectedMonth . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        EvaluationData::whereBetween('Month', [$startDate, $endDate])->delete();


        return redirect()->back()->with('success', 'Data for selected month has been deleted.');
        // dd($request->all());
    }


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

    public function processExcelFileWeekly($files)
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



    public function importExcelFileWeekly($excelFileName)
    {
        Excel::import(new EvaluationWeeklyDataImport,  public_path('/storage/Evaluation/' . $excelFileName));

        // If the import is successful, return a success message or any other response
        return 'Excel file imported successfully.';
    }

    public function singleEmployee(Request $request)
    {
        // Get the year input from the user, default to the current year if not provided
        $year = $request->input('year', now()->year - 1);

        // Fetch the user and filter the evaluation data by the selected year
        $user = Employee::with(['evaluationData' => function ($query) use ($year) {
            $query->whereYear('Month', $year); // Filter data by year
        }],'department')->first();
        // dd($user);

        // Pass the user and year to the view
        return view('test', compact('user', 'year'));
    }

    public function evaluationformatrequestpageYayasan()
    {
        $statuses = Employee::where('employee_status', 'YAYASAN')
        ->distinct()
        ->pluck('status');


        $departments = Department::whereHas('employees', function ($query) {
            $query->whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG']);
        })->select('dept_no', 'name')->distinct()->get();

        return view('setting.formatrequestyayasan', compact('statuses', 'departments'));
    }


    public function evaluationformatrequestpageAllin()
    {
        $statuses = Employee::whereIn('employee_status', ['KONTRAK', 'TETAP'])
        ->distinct()
        ->pluck('status');


        $departments = Department::whereHas('employees', function ($query) use ($statuses) {
            $query->whereIn('status', $statuses);
        })->select('dept_no', 'name')->distinct()->get();

        return view('setting.formatrequestallin', compact('statuses', 'departments'));
    }

    public function evaluationformatrequestpageMagang()
    {
        $statuses = Employee::whereIn('employee_status', ['MAGANG'])
        ->distinct()
        ->pluck('status');


        $departments = Department::whereHas('employees', function ($query) use ($statuses) {
            $query->whereIn('status', $statuses);
        })->select('dept_no', 'name')->distinct()->get();


        return view('setting.formatrequestmagang', compact('statuses', 'departments'));
    }


    public function allEmployees(Request $request)
    {
        // Get the year input, default to the previous year if not provided
        $year = $request->input('year', now()->year - 1);

        // Fetch all employees with their evaluation data filtered by the selected year
        $employees = Employee::with([
            'evaluationData' => function ($query) use ($year) {
                $query->whereYear('Month', $year);
            },
            'department'
        ])->get();

        // Pass employees and the selected year to the view
        return view('test', compact('employees', 'year'));
    }

    public function getFormatYearallin(Request $request)
    {
        $dept = $request->input('dept');
        $year = $request->input('year');

        $statuses = Employee::whereIn('employee_status', ['KONTRAK', 'TETAP'])
        ->distinct()
        ->pluck('status');

        // Get department codes where status is 'YAYASAN' or 'YAYASAN KARAWANG'
        $allowedDepartments = Employee::whereIn('status', $statuses)
            ->pluck('Dept'); // Get department codes

        // Fetch employees who belong to the selected department and have the correct status
        $employees = Employee::with([
            'evaluationData' => function ($query) use ($year) {
                $query->whereYear('Month', $year);
            },
            'department'
        ])->whereIn('Dept', $allowedDepartments) // Ensure employees belong to the correct departments
        ->whereIn('status', $statuses) // Ensure employees also have the correct status
        ->where('Dept', $dept) // Filter by user-selected department
        ->get();


         // Pass employees and the selected year to the view
         return view('test', compact('employees', 'year'));
    }

    public function getFormatYearmagang(Request $request)
    {
        $dept = $request->input('dept');
        $year = $request->input('year');

        $statuses = Employee::whereIn('employee_status', ['MAGANG'])
        ->distinct()
        ->pluck('status');

        // Get department codes where status is 'YAYASAN' or 'YAYASAN KARAWANG'
        $allowedDepartments = Employee::whereIn('status', $statuses)
            ->pluck('Dept'); // Get department codes

        // Fetch employees who belong to the selected department and have the correct status
        $employees = Employee::with([
            'evaluationData' => function ($query) use ($year) {
                $query->whereYear('Month', $year);
            },
            'department'
        ])->whereIn('Dept', $allowedDepartments) // Ensure employees belong to the correct departments
        ->whereIn('status', $statuses) // Ensure employees also have the correct status
        ->where('Dept', $dept) // Filter by user-selected department
        ->get();


         // Pass employees and the selected year to the view
         return view('test', compact('employees', 'year'));
    }

    public function getFormatYearYayasan(Request $request)
    {
        // dd($request->all());
        $dept = $request->input('dept');
        $year = $request->input('year');

        // Get department codes where status is 'YAYASAN' or 'YAYASAN KARAWANG'
        $allowedDepartments = Employee::whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG'])
            ->pluck('Dept'); // Get department codes

        // Fetch employees who belong to the selected department and have the correct status
        $employees = Employee::with([
            'evaluationData' => function ($query) use ($year) {
                $query->whereYear('Month', $year);
            },
            'department'
        ])->whereIn('Dept', $allowedDepartments) // Ensure employees belong to the correct departments
        ->whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG']) // Ensure employees also have the correct status
        ->where('Dept', $dept) // Filter by user-selected department
        ->get();


         // Pass employees and the selected year to the view
         return view('test', compact('employees', 'year'));
    }
}
