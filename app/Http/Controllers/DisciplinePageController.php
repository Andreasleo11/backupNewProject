<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;

use App\DataTables\DisciplineTableDataTable;
use App\Exports\DesciplineDataExp;
use App\Imports\DesciplineDataImport;
use App\Models\EvaluationData;
use App\Models\Employee;


use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

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
                $query->where('Dept', '340');
            })
            ->get();

            
        }

        elseif($user->is_head == 1 && $user->department_id == 1)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '341');
            })
            ->get();
        }

        elseif($user->is_head == 1 && $user->department_id == 3)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '100');
            })
            ->get();
        }

        elseif($user->is_head == 1 && $user->department_id == 8)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '200');
            })
            ->get();

        }

        elseif($user->is_head == 1 && $user->department_id == 7)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '310');
            })
            ->get();

         
        }

        elseif($user->is_head == 1 && $user->department_id == 5)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '320');
            })
            ->get();

        }

        elseif($user->is_head == 1 && $user->department_id == 17)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '330');
            })
            ->get();

        }

        elseif($user->is_head == 1 && $user->department_id == 24)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '331');
            })
            ->get();

          
        }

        elseif($user->is_head == 1 && $user->department_id == 18)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '350');
            })
            ->get();

          
        }

        elseif($user->is_head == 1 && $user->department_id == 19)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '361');
            })
            ->get();

            
        }

        elseif($user->is_head == 1 && $user->department_id == 20)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '362');
            })
            ->get();

          
        }

        elseif($user->is_head == 1 && $user->department_id == 16)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '363');
            })
            ->get();
        }

        elseif($user->is_head == 1 && $user->department_id == 11)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '390');
            })
            ->get();

            
        }

        elseif($user->is_head == 1 && $user->department_id == 9)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '500');
            })
            ->get();

           
        }

        elseif($user->is_head == 1 && $user->department_id == 15)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '600');
            })
            ->get();
            

           

            // $nonZeroEmployees = $employees->reject(function ($employee) {
            //     return $employee->total === 0;
            // });

            // // Find the highest total
            // $highestTotal = $nonZeroEmployees->max('total');
            // // dd($highestTotal);

            // // Filter employees with the highest total
            // $employeesWithHighestTotal = $employees->filter(function ($employee) use ($highestTotal) {
            //     return $employee->total === $highestTotal;
            // });
            
            
            // $minimumTotal = $nonZeroEmployees->min('total');


            // $employeesWithLowestTotal = $employees->filter(function ($employee) use ($minimumTotal) {
            //     return $employee->total === $minimumTotal;
            // });

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

        if($employees == null)
        {
            return redirect()->back();
        }else{
           
        return $dataTable->render("setting.disciplineindex", compact("employees"));
        }
        // return view("setting.disciplineindex", compact("employees"));
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
        $total = 40;
        foreach ($data as $row) {
            foreach ($existingRecords as $record) {
                $j +=1;
              
                $total = 40 - (($row[3] * 10) + ($row[4] * 0.5) + ($row[5]*2) + ($row[6]));
                if($row[7] === "A")
                {
                    $total += 10;   
                }
                elseif($row[7] === "B")
                {
                    $total += 7.5;   
                }
                elseif($row[7] === "C")
                {
                    $total += 5;   
                }
                elseif($row[7] === "D")
                {
                    $total += 2.5;   
                }
                elseif($row[7] === "E")
                {
                    $total += 0;   
                }
                if($row[8] === "A")
                {
                    $total += 10;   
                }
                elseif($row[8] === "B")
                {
                    $total += 7.5;   
                }
                elseif($row[8] === "C")
                {
                    $total += 5;   
                }
                elseif($row[8] === "D")
                {
                    $total += 2.5;   
                }
                elseif($row[8] === "E")
                {
                    $total += 0;   
                }
        
                if($row[9] === "A")
                {
                    $total += 10;   
                }
                elseif($row[9] === "B")
                {
                    $total += 7.5;   
                }
                elseif($row[9] === "C")
                {
                    $total += 5;   
                }
                elseif($row[9] === "D")
                {
                    $total += 2.5;   
                }
                elseif($row[9] === "E")
                {
                    $total += 0;   
                }
        
                if($row[10] === "A")
                {
                    $total += 10;   
                }
                elseif($row[10] === "B")
                {
                    $total += 7.5;   
                }
                elseif($row[10] === "C")
                {
                    $total += 5;   
                }
                elseif($row[10] === "D")
                {
                    $total += 2.5;   
                }
                elseif($row[10] === "E")
                {
                    $total += 0;   
                }
        
                if($row[11] === "A")
                {
                    $total += 10;   
                }
                elseif($row[11] === "B")
                {
                    $total += 7.5;   
                }
                elseif($row[11] === "C")
                {
                    $total += 5;   
                }
                elseif($row[11] === "D")
                {
                    $total += 2.5;   
                }
                elseif($row[11] === "E")
                {
                    $total += 0;   
                }
        
                if($row[12] === "A")
                {
                    $total += 10;   
                }
                elseif($row[12] === "B")
                {
                    $total += 7.5;   
                }
                elseif($row[12] === "C")
                {
                    $total += 5;   
                }
                elseif($row[12] === "D")
                {
                    $total += 2.5;   
                }
                elseif($row[12] === "E")
                {
                    $total += 0;   
                }
               
                if ($record->NIK === $row[1] && $record->Month === $row[2]) { // Check if NIK matches
                    // Update the attributes with new values
                   EvaluationData::where('id', $record->id)->update([
                    'kerajinan_kerja'=> $row[7], 
                    'kerapian_pakaian' => $row[8],
                    'kerapian_rambut' => $row[9],
                    'kerapian_sepatu'=> $row[10],
                    'prestasi' => $row[11],
                    'loyalitas' =>  $row[12],
                    'total' => $total,
                   ]);
                    $i += 1;
                }
            }
        }
        // If the import is successful, return a success message or any other response
        return 'Excel file imported successfully.';




        //BERIKUT ADALAH LOGIC UNTUK MENGHAPUS DATA TIAP UPDATE - DEPRECATED
        // // Extract unique NIKs from the imported data
        // $uniqueNIKs = array_unique(array_column($data, 1)); // Assuming NIK is at index 1

        // // Delete existing records with matching NIKs
        // EvaluationData::whereIn('NIK', $uniqueNIKs)->delete();

        // // Import the new data
        // foreach ($data as $row) {
        //     $import->model($row)->save();
        // }
        // dd($data);
    }

    public function step1(DisciplineTableDataTable $dataTable)
    {
        $user = Auth::user();



        $employees = null;
        //PEER LOGIC UNTUK HANDLE ORANG ORANG DIBAWAH DEPT HEADNYA SAJA - HARUS DIHANDLE MANUAL 
        if($user->is_head == 1 && $user->department_id == 2)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '340');
            })
            ->get();

            foreach ($employees as $employee) {
                $employee->total = $employee->Alpha + $employee->Telat + $employee->Izin;
            //    dd($employee);
            }
        }

        elseif($user->is_head == 1 && $user->department_id == 3)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '100');
            })
            ->get();

            foreach ($employees as $employee) {
                $employee->total = $employee->Alpha + $employee->Telat + $employee->Izin;
            //    dd($employee);
            }
        }

        elseif($user->is_head == 1 && $user->department_id == 8)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '200');
            })
            ->get();

            foreach ($employees as $employee) {
                $employee->total = $employee->Alpha + $employee->Telat + $employee->Izin;
            //    dd($employee);
            }
        }

        elseif($user->is_head == 1 && $user->department_id == 7)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '310');
            })
            ->get();

            foreach ($employees as $employee) {
                $employee->total = $employee->Alpha + $employee->Telat + $employee->Izin;
            //    dd($employee);
            }
        }

        elseif($user->is_head == 1 && $user->department_id == 5)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '320');
            })
            ->get();

            foreach ($employees as $employee) {
                $employee->total = $employee->Alpha + $employee->Telat + $employee->Izin;
            //    dd($employee);
            }
        }

        elseif($user->is_head == 1 && $user->department_id == 17)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '330');
            })
            ->get();

            foreach ($employees as $employee) {
                $employee->total = $employee->Alpha + $employee->Telat + $employee->Izin;
            //    dd($employee);
            }
        }

        elseif($user->is_head == 1 && $user->department_id == 24)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '331');
            })
            ->get();

            foreach ($employees as $employee) {
                $employee->total = $employee->Alpha + $employee->Telat + $employee->Izin;
            //    dd($employee);
            }
        }

        elseif($user->is_head == 1 && $user->department_id == 18)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '350');
            })
            ->get();

            foreach ($employees as $employee) {
                $employee->total = $employee->Alpha + $employee->Telat + $employee->Izin;
            //    dd($employee);
            }
        }

        elseif($user->is_head == 1 && $user->department_id == 19)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '361');
            })
            ->get();

            foreach ($employees as $employee) {
                $employee->total = $employee->Alpha + $employee->Telat + $employee->Izin;
            //    dd($employee);
            }
        }

        elseif($user->is_head == 1 && $user->department_id == 20)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '362');
            })
            ->get();

            foreach ($employees as $employee) {
                $employee->total = $employee->Alpha + $employee->Telat + $employee->Izin;
            //    dd($employee);
            }
        }

        elseif($user->is_head == 1 && $user->department_id == 16)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '363');
            })
            ->get();

            foreach ($employees as $employee) {
                $employee->total = $employee->Alpha + $employee->Telat + $employee->Izin;
            //    dd($employee);
            }
        }

        elseif($user->is_head == 1 && $user->department_id == 11)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '390');
            })
            ->get();

            foreach ($employees as $employee) {
                $employee->total = $employee->Alpha + $employee->Telat + $employee->Izin;
            //    dd($employee);
            }
        }

        elseif($user->is_head == 1 && $user->department_id == 9)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '500');
            })
            ->get();

            foreach ($employees as $employee) {
                $employee->total = $employee->Alpha + $employee->Telat + $employee->Izin;
            //    dd($employee);
            }
        }

        elseif($user->is_head == 1 && $user->department_id == 15)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '600');
            })
            ->get();

            foreach ($employees as $employee) {
                $employee->total = $employee->Alpha + $employee->Telat + $employee->Izin;
            //    dd($employee);
            }
        }

        elseif($user->is_head == 1 && $user->department_id == 6)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '311');
            })
            ->get();

            foreach ($employees as $employee) {
                $employee->total = $employee->Alpha + $employee->Telat + $employee->Izin;
            //    dd($employee);
            }
        }

        elseif($user->is_head == 1 && $user->department_id == 25)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '351');
            })
            ->get();

            foreach ($employees as $employee) {
                $employee->total = $employee->Alpha + $employee->Telat + $employee->Izin;
            //    dd($employee);
            }
        }


        return $dataTable->render("setting.disciplineindexstep1", compact("employees"));
    }

    public function step2()
    {
        return view("setting.disciplineindexstep2");
    }
}
