<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

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

        elseif($user->is_head == 1 && $user->department_id == 22)
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

        if($employees == null)
        {
            return redirect()->back();
        }else{
        return $dataTable->render("setting.disciplineindex", compact("employees"));
        }
        // return view("setting.disciplineindex", compact("employees"));
    }

    public function update(Request $request, $id)
    {
        $dis = EvaluationData::where('id', $id)->get();

        foreach($dis as $di)
        $di->where('id', $request->id)->update(
            [
                'kerajinan_kerja' =>$request->kerajinan_kerja,
                'kerapian_pakaian' =>$request->kerapian_pakaian,
                'kerapian_rambut' =>$request->kerapian_rambut,
                'kerapian_sepatu' =>$request->kerapian_sepatu,
                'prestasi' =>$request->prestasi,
                'loyalitas' =>$request->loyalitas,
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

        // Fetch existing records based on NIK
        $existingRecords = EvaluationData::whereIn('NIK', $uniqueNIKs)->get();
        
        $i = 0;
        $j = 0;
        foreach ($data as $row) {
            foreach ($existingRecords as $record) {
                $j +=1;
                if ($record->NIK === $row[1] && $record->Month === $row[2]) { // Check if NIK matches
                    // Update the attributes with new values
                   EvaluationData::where('id', $record->id)->update([
                    'kerajinan_kerja'=> $row[7], 
                    'kerapian_pakaian' => $row[8],
                    'kerapian_rambut' => $row[9],
                    'kerapian_sepatu'=> $row[10],
                    'prestasi' => $row[11],
                    'loyalitas' =>  $row[12],
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

        elseif($user->is_head == 1 && $user->department_id == 22)
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
