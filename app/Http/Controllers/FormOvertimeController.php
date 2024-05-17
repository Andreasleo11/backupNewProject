<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Employee;
use App\Models\Department;
use App\Models\DetailFormOvertime;
use App\Models\HeaderFormOvertime;
use Illuminate\Support\Facades\Auth;

class FormOvertimeController extends Controller
{
    public function index()
    {
        $dataheader = HeaderFormOvertime::with('Relationuser','Relationdepartement')->get();
        // dd($dataheader);
        return view("formovertime.index", compact("dataheader"));
    }

    public function create()
    {
        $employees = Employee::get();
        $departements = Department::get();
        
        return view("formovertime.create", compact("employees", "departements"));
    }


    public function getEmployeeNik(Request $request)
    {
        $nik = $request->query('nik');
       
        info('AJAX request received for item name: ' . $nik);

        // Fetch item names and prices from the database based on user input
        $pegawais = Employee::where('NIK', 'like', "%" . $nik . "%")
        ->select('NIK', 'nama')
        ->get();


        
        return response()->json($pegawais);
    }


    public function insert(Request $request)
    {

        $userIdCreate = Auth::id();

        
        $headerData = [
            'user_id' => $userIdCreate,
            'dept_id' => $request->input('from_department'),
            'create_date' => $request->input('date_form_overtime'),
            'autograph_1' => strtoupper(Auth::user()->name) . '.png',
            'status' => 1
        ];
        
        $headerovertime = HeaderFormOvertime::create($headerData);

        $this->detailOvertimeInsert($request, $headerovertime->id);
        
        return redirect()->route('formovertime.index');
    }

    public function detailOvertimeInsert($request, $id)
    {
        if ($request->has('items') && is_array($request->input('items'))) {
            foreach ($request->input('items') as $employeedata) {
                $nik = $employeedata['NIK'];
                $nama = $employeedata['nama'];
                $jobdesc = $employeedata['jobdesc'];
                $makan = $employeedata['makan'];
                $startdate = $employeedata['startdate'];
                $starttime = $employeedata['starttime'];
                $enddate = $employeedata['enddate'];
                $endtime = $employeedata['endtime'];
                $break = $employeedata['break'];
                $remark = $employeedata['remark'];

                $detailData = [
                    'header_id' => $id,
                    'NIK' => $nik,
                    'nama' => $nama,
                    'is_makan' => $makan,
                    'job_desc' => $jobdesc,
                    'start_date' => $startdate,
                    'start_time' => $starttime,
                    'end_date' => $enddate,
                    'end_time' => $endtime,
                    'break' => $break,
                    'remarks' => $remark
                ];

                DetailFormOvertime::create($detailData);
            }
        }
    }
}
