<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;

use App\DataTables\DisciplineTableDataTable;

use App\Models\EvaluationData;
use App\Models\Employee;
use Illuminate\Http\Request;

class DisciplinePageController extends Controller
{
    public function index(DisciplineTableDataTable $dataTable)
    {
        $user = Auth::user();
        if($user->is_head == 0)
        {
            $employees = EvaluationData::with('karyawan')->whereHas('karyawan', function ($query) {
                $query->where('Dept', '340');
            })
            ->get();
            // dd($employees);
        }
        return $dataTable->render("setting.disciplineindex", compact("employees"));
        // return view("setting.disciplineindex", compact("employees"));
    }
}
