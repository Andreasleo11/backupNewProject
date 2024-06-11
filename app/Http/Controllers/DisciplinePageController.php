<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;

use App\DataTables\DisciplineTableDataTable;
use App\DataTables\DisciplineYayasanTableDataTable;
use App\DataTables\AllDisciplineTableDataTable;
use App\Exports\DesciplineDataExp;
use App\Imports\DesciplineDataImport;
use App\Models\EvaluationData;
use App\Models\Employee;

use Carbon\Carbon;


use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

use App\Exports\YayasanDisciplineExport;


class DisciplinePageController extends Controller
{
    public function index(DisciplineTableDataTable $dataTable)
    {
        $user = Auth::user();

        $employees = null;
        //PEER LOGIC UNTUK HANDLE ORANG ORANG DIBAWAH DEPT HEADNYA SAJA - HARUS DIHANDLE MANUAL
        if($user->is_head == 1 && $user->department_id == 2)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '340')
                ->where('status', '!=', 'YAYASAN');
            })
            ->get();
        }
        elseif($user->is_head == 1 && $user->department_id == 1)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '341')
                ->where('status', '!=', 'YAYASAN');
            })
            ->get();
        }
        elseif($user->is_head == 1 && $user->department_id == 3)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '100')
                ->where('status', '!=', 'YAYASAN');
            })
            ->get();
        }
        elseif($user->is_head == 1 && $user->department_id == 8)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '200')
                ->where('status', '!=', 'YAYASAN');
            })
            ->get();
        }
        elseif($user->is_head == 1 && $user->department_id == 7)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '310')
                ->where('status', '!=', 'YAYASAN');
            })
            ->get();
        }
        elseif($user->email === "ani_apriani@daijo.co.id")
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '310')
                ->where('status', '!=', 'YAYASAN');
            })
            ->get();
        }
        elseif($user->is_head == 1 && $user->department_id == 5)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '320')
                ->where('status', '!=', 'YAYASAN');
            })
            ->get();
        }
        elseif($user->is_head == 1 && $user->department_id == 17)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '330')
                ->where('status', '!=', 'YAYASAN');
            })
            ->get();
        }
        elseif($user->is_head == 1 && $user->department_id == 24)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '331')
                ->where('status', '!=', 'YAYASAN');
            })
            ->get();
        }
        elseif($user->is_head == 1 && $user->department_id == 18)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '350')
                ->where('status', '!=', 'YAYASAN');
            })
            ->get();
        }
        elseif($user->is_head == 1 && $user->department_id == 19)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '361')
                ->where('status', '!=', 'YAYASAN');
            })
            ->get();
        }
        elseif($user->is_head == 1 && $user->department_id == 20)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '362')
                ->where('status', '!=', 'YAYASAN');
            })
            ->get();
        }
        elseif($user->is_head == 1 && $user->department_id == 16)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '363')
                ->where('status', '!=', 'YAYASAN');
            })
            ->get();
        }
        elseif($user->is_head == 1 && $user->department_id == 11)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '390')
                ->where('status', '!=', 'YAYASAN');
            })
            ->get();
        }
        elseif($user->is_head == 1 && $user->department_id == 9)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '500')
                ->where('status', '!=', 'YAYASAN');
            })
            ->get();
        }
        elseif($user->is_head == 1 && $user->department_id == 15)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '600')
                ->where('status', '!=', 'YAYASAN');
            })
            ->get();
        }
        elseif($user->is_head == 1 && $user->department_id == 6)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '311');
            })
            ->get();
        }
        elseif($user->is_head == 1 && $user->department_id == 25)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '351');
            })
            ->get();
        }

        return $dataTable->render("setting.disciplineindex", compact("employees", "user"));
        // return view("setting.disciplineindex", compact("employees"));
    }

    public function allindex(AllDisciplineTableDataTable $dataTable)
    {
        $employees = EvaluationData::with('karyawan')->get();
        return $dataTable->render("setting.alldisciplineindex", compact("employees"));
    }

    public function setFilterValue(Request $request)
    {
        $filterValue = $request->input('filterValue');
        session(['filterValue' => $filterValue]);
        return response()->json(['filterValue' => $filterValue]);
    }

    public function getFilterValue()
    {
        $filterValue = session('filterValue');
        return response()->json(['filterValue' => $filterValue]);
    }

    public function update(Request $request, $id)
    {
        $dis = EvaluationData::where('id', $id)->get();
        $total = 40;


        foreach($dis as $di)

        $total = 40 - (($di->Alpha * 10) + ($di->Izin * 2) + ($di->Sakit) + ($di->Telat * 0.5));
        if($request->kerajinan_kerja === "A")
        {
            $total += 10;
        }
        elseif($request->kerajinan_kerja === "B")
        {
            $total += 7.5;
        }
        elseif($request->kerajinan_kerja === "C")
        {
            $total += 5;
        }
        elseif($request->kerajinan_kerja === "D")
        {
            $total += 2.5;
        }
        elseif($request->kerajinan_kerja === "E")
        {
            $total += 0;
        }
        if($request->kerapian_pakaian === "A")
        {
            $total += 10;
        }
        elseif($request->kerapian_pakaian === "B")
        {
            $total += 7.5;
        }
        elseif($request->kerapian_pakaian === "C")
        {
            $total += 5;
        }
        elseif($request->kerapian_pakaian === "D")
        {
            $total += 2.5;
        }
        elseif($request->kerapian_pakaian === "E")
        {
            $total += 0;
        }

        if($request->kerapian_rambut === "A")
        {
            $total += 10;
        }
        elseif($request->kerapian_rambut === "B")
        {
            $total += 7.5;
        }
        elseif($request->kerapian_rambut === "C")
        {
            $total += 5;
        }
        elseif($request->kerapian_rambut === "D")
        {
            $total += 2.5;
        }
        elseif($request->kerapian_rambut === "E")
        {
            $total += 0;
        }

        if($request->kerapian_sepatu === "A")
        {
            $total += 10;
        }
        elseif($request->kerapian_sepatu === "B")
        {
            $total += 7.5;
        }
        elseif($request->kerapian_sepatu === "C")
        {
            $total += 5;
        }
        elseif($request->kerapian_sepatu === "D")
        {
            $total += 2.5;
        }
        elseif($request->kerapian_sepatu === "E")
        {
            $total += 0;
        }

        if($request->prestasi === "A")
        {
            $total += 10;
        }
        elseif($request->prestasi === "B")
        {
            $total += 7.5;
        }
        elseif($request->prestasi === "C")
        {
            $total += 5;
        }
        elseif($request->prestasi === "D")
        {
            $total += 2.5;
        }
        elseif($request->prestasi === "E")
        {
            $total += 0;
        }

        if($request->loyalitas === "A")
        {
            $total += 10;
        }
        elseif($request->loyalitas === "B")
        {
            $total += 7.5;
        }
        elseif($request->loyalitas === "C")
        {
            $total += 5;
        }
        elseif($request->loyalitas === "D")
        {
            $total += 2.5;
        }
        elseif($request->loyalitas === "E")
        {
            $total += 0;
        }

        $di->where('id', $request->id)->update(
            [
                'kerajinan_kerja' =>$request->kerajinan_kerja,
                'kerapian_pakaian' =>$request->kerapian_pakaian,
                'kerapian_rambut' =>$request->kerapian_rambut,
                'kerapian_sepatu' =>$request->kerapian_sepatu,
                'prestasi' =>$request->prestasi,
                'loyalitas' =>$request->loyalitas,
                'total' => $total,
            ]);

        return redirect()->route('discipline.index')->with('success', 'Line added successfully');
    }

    public function import(Request $request)
    {
        $uploadedFiles = $request->file('excel_files');

        $excelFileName = $this->processExcelFile($uploadedFiles);
        $this->importExcelFile($excelFileName);
        return redirect()->route('discipline.index')->with('success', 'Line added successfully');
    }

    public function processExcelFile($files)
    {

        $allData = [];
        foreach ($files as $file) {
            // Read the XLS file
            $data = Excel::toArray([], $file);
            // Remove the first row (header)
            array_shift($data[0]);

            foreach ($data[0] as &$row) {
                // Remove cells C and D from the current row
                unset($row[2]); // Remove cell C (index 2)
                unset($row[3]); // Remove cell D (index 3)
                // Re-index the array after removing cells
                $row = array_values($row);
            }

            $allData = array_merge($allData, $data[0]);

        }

        $excelFileName = 'DisciplineData.xlsx';
        $excelFilePath = public_path($excelFileName);

        Excel::store(new DesciplineDataExp($allData), 'public/Evaluation/' . $excelFileName);

        // $filePath = Storage::url($fileName);
        return $excelFileName;

    }

    public function importExcelFile($excelFileName)
    {
        $import = new DesciplineDataImport();
        $data = Excel::toArray($import, 'public/Evaluation/DisciplineData.xlsx')[0];



        // Extract unique NIKs from the imported data
        $uniqueNIKs = array_unique(array_column($data, 1)); // Assuming NIK is at index 1
        // dd($uniqueNIKs);
        // Fetch existing records based on NIK
        $existingRecords = EvaluationData::whereIn('NIK', $uniqueNIKs)->get();

        foreach ($data as &$dat) {
            foreach ($dat as &$value) {
                if ($value === null) {
                    $value = 0;
                }
            }
        }

        $i = 0;
        $j = 0;
        $maxpoint = 40; // Set the maxpoint value


        foreach ($data as $row) {

            foreach ($existingRecords as $record) {
                if ($record->NIK === $row[1] && $record->Month === $row[2]) { // Check if NIK matches
                    // Fetch the values for alpha, izin, sakit, and terlambat from the database
                    $alpha = $record->Alpha;
                    $izin = $record->Izin;
                    $sakit = $record->Sakit;
                    $terlambat = $record->Telat;

                    // Calculate the points
                    $calculatedPoints = ($alpha * 10) + ($izin * 2) + ($sakit * 1) + ($terlambat * 0.5);
                    $totalPoints = $maxpoint - $calculatedPoints;
                    // dd($totalPoints);
                    // Calculate other totals
                    $total = 0; // Reset total for each row

                    // Calculate the total based on other columns in the row
                    for ($k = 3; $k <= 8; $k++) {
                        switch ($row[$k]) {
                            case "A":
                                $total += 10;
                                break;
                            case "B":
                                $total += 7.5;
                                break;
                            case "C":
                                $total += 5;
                                break;
                            case "D":
                                $total += 2.5;
                                break;
                            case "E":
                                $total += 0;
                                break;
                        }
                    }

                    // Add the calculated totalPoints to the total
                    $total += $totalPoints;

                    // Update the attributes with new values
                    EvaluationData::where('id', $record->id)->update([
                        'kerajinan_kerja' => $row[3],
                        'kerapian_pakaian' => $row[4],
                        'kerapian_rambut' => $row[5],
                        'kerapian_sepatu' => $row[6],
                        'prestasi' => $row[7],
                        'loyalitas' => $row[8],
                        'total' => $total,
                    ]);
                    $i += 1;
                }
            }
        }
        // If the import is successful, return a success message or any other response
        return 'Excel file imported successfully.';

    }


    public function exportYayasan(Request $request)
    {
        $selectedMonth = $request->input('filter_status');

        $currentYear = Carbon::now()->year;

        // Create a Carbon instance for the selected month and year
        $selectedDate = Carbon::createFromDate($currentYear, $selectedMonth, 1);

        // Calculate the cutoff date, 6 months before the selected month
        $cutoffDate = $selectedDate->copy()->subMonths(6)->startOfMonth();


        $employees = EvaluationData::with('karyawan')
        ->whereHas('karyawan', function ($query) use ($cutoffDate) {
            $query->where('status', 'YAYASAN')
                  ->where('start_date', '<', $cutoffDate);
        })
        ->whereMonth('month', $selectedMonth)
        ->get();



        $result = [];

        foreach ($employees as $data) {
            $employeeId = $data->karyawan->NIK;


            if (!isset($result[$employeeId])) {
                $result[$employeeId] = [
                    'employee_id' => $employeeId,
                    'nilai_A' => 0,
                    'nilai_B' => 0
                ];
            }

            $total = $data->total;
            if ($total >= 91) {
                $result[$employeeId]['nilai_A'] = 1;
                $result[$employeeId]['nilai_B'] = 0; // Ensure nilai_B is set to 0
            } elseif ($total >= 71 && $total <= 90) {
                $result[$employeeId]['nilai_A'] = 0; // Ensure nilai_A is set to 0
                $result[$employeeId]['nilai_B'] = 1;
            } else {
                $result[$employeeId]['nilai_A'] = 0;
                $result[$employeeId]['nilai_B'] = 0;
            }

            // Initialize nilai_A and nilai_B if not already set
            if (!isset($result[$employeeId]['nilai_A'])) {
                $result[$employeeId]['nilai_A'] = 0;
            }
            if (!isset($result[$employeeId]['nilai_B'])) {
                $result[$employeeId]['nilai_B'] = 0;
            }
        }

        // Convert the result associative array to a sequential array
        $result = array_values($result);

        // Output the result
        // dd($result);

        $currentDate = Carbon::now()->format('d-m-y'); // or any format you prefer

        $fileName = "DataYayasan_{$currentDate}.xlsx";

        return Excel::download(new YayasanDisciplineExport($result), $fileName);
    }

    public function indexyayasan(DisciplineYayasanTableDataTable $dataTable)
    {
        $user = Auth::user();

        if($user->is_head == 1 && $user->department_id == 2)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '340')
                ->where('status', 'YAYASAN');
            })
            ->get();
        }
        elseif($user->is_head == 1 && $user->department_id == 11){
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '390')
                ->where('status', 'YAYASAN');
            })
            ->get();
        }

        elseif($user->is_head == 1 && $user->department_id == 24){
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '331')
                ->where('status', 'YAYASAN');
            })
            ->get();
        }

        elseif($user->is_head == 1 && $user->department_id == 16){
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '363')
                ->where('status', 'YAYASAN');
            })
            ->get();
        }

        elseif($user->is_head == 1 && $user->department_id == 17){
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '330')
                ->where('status', 'YAYASAN');
            })
            ->get();
        }

        elseif($user->is_head == 1 && $user->department_id == 25){
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '351')
                ->where('status', 'YAYASAN');
            })
            ->get();
        }

        elseif($user->is_head == 1 && $user->department_id == 19){
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '361')
                ->where('status', 'YAYASAN');
            })
            ->get();
        }

        elseif($user->is_head == 1 && $user->department_id == 20){
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '362')
                ->where('status', 'YAYASAN');
            })
            ->get();
        }

        elseif($user->is_head == 1 && $user->department_id == 18){
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '350')
                ->where('status', 'YAYASAN');
            })
            ->get();
        }

        elseif($user->is_head == 1 && $user->department_id == 6){
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '311')
                ->where('status', 'YAYASAN');
            })
            ->get();
        }

        return $dataTable->render("setting.disciplineyayasanindex", compact( "employees","user"));
    }


    public function updateyayasan(Request $request, $id)
    {
        $evaluationData = EvaluationData::find($id);



        // Update the specific fields for this user
        $evaluationData->update([
            'kemampuan_kerja' => $request->kemampuan_kerja,
            'kecerdasan_kerja' => $request->kecerdasan_kerja,
            'qualitas_kerja' => $request->qualitas_kerja,
            'disiplin_kerja' => $request->disiplin_kerja,
            'kepatuhan_kerja' => $request->kepatuhan_kerja,
            'lembur' => $request->lembur,
            'efektifitas_kerja' => $request->efektifitas_kerja,
            'relawan' => $request->relawan,
            'integritas' => $request->integritas,
            // Add other fields you want to update
        ]);



        // Calculate total score
        $scoreMaps = [
            'kemampuan_kerja' => ['A' => 17, 'B' => 14, 'C' => 11, 'D' => 8, 'E' => 0],
            'kecerdasan_kerja' => ['A' => 16, 'B' => 13, 'C' => 10, 'D' => 7, 'E' => 0],
            'qualitas_kerja' => ['A' => 11, 'B' => 9, 'C' => 7, 'D' => 4, 'E' => 0],
            'disiplin_kerja' => ['A' => 8, 'B' => 6, 'C' => 5, 'D' => 3, 'E' => 0],
            'kepatuhan_kerja' => ['A' => 10, 'B' => 8, 'C' => 6, 'D' => 4, 'E' => 0],
            'lembur' =>  ['A' => 10, 'B' => 8, 'C' => 6, 'D' => 4, 'E' => 0],
            'efektifitas_kerja' =>  ['A' => 10, 'B' => 8, 'C' => 6, 'D' => 4, 'E' => 0],
            'relawan' =>  ['A' => 10, 'B' => 8, 'C' => 6, 'D' => 4, 'E' => 0],
            'integritas' => ['A' => 8, 'B' => 6, 'C' => 5, 'D' => 3, 'E' => 0]
        ];

        $total = 0;

        foreach ($request->only(array_keys($scoreMaps)) as $field => $value) {
            $total += $scoreMaps[$field][$value] ?? 0;
        }

        // Subtract penalties
        $total -= (($evaluationData->Alpha * 10) + ($evaluationData->Izin * 2) + ($evaluationData->Sakit) + ($evaluationData->Telat * 0.5));

        // dd($evaluationData);
        // Update total score for the user
        $evaluationData->update(['total' => $total]);

        return redirect()->route('yayasan.table')->with('success', 'Data updated successfully');
    }



    public function lockdata(Request $request)
    {
        // dd($request->all());
        $filterMonth = $request->filter_month ;
        // dd($filterMonth);
        $deptNo = Auth::user()->department->dept_no;

        // dd($deptNo);
        $filterMonth = $request->input('filter_month');

        // dd($filterMonth);
        $employees =EvaluationData::whereHas('karyawan', function ($query) use ($deptNo) {
            $query->where('Dept', $deptNo);
        })
        ->whereMonth('Month', $filterMonth)
        ->get();

        foreach ($employees as $employee) {
            $employee->is_lock = true;
            $employee->save();
        }

        return redirect()->back();
    }

    public function fetchFilteredEmployees(Request $request)
    {
        // Get the filter month from the request
        $filterMonth = $request->input('filter_month');

        $deptNo = Auth::user()->department->dept_no;


        $employees =EvaluationData::whereHas('karyawan', function ($query) use ($deptNo) {
            $query->where('Dept', $deptNo);
        })
        ->whereMonth('Month', $filterMonth)
        ->get();

        // Return the filtered employee data as JSON response
        return response()->json($employees);
    }
}
