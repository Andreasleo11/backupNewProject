<?php

namespace App\Http\Controllers;

use App\DataTables\EmployeeTrainingDataTable;
use App\Models\Employee;
use App\Models\EmployeeTraining;
use Illuminate\Http\Request;

class EmployeeTrainingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(EmployeeTrainingDataTable $dataTable)
    {
        // $targetDate = \Carbon\Carbon::today()->subDays(75);

        // $trainings = EmployeeTraining::whereDate('last_training_at', '=', $targetDate)
        //     ->with('employee')
        //     ->get();

        //     dd($trainings);

        return $dataTable->render("employee_trainings.index");

        $trainings = EmployeeTraining::with("employee")->get();

        return view("employee_trainings.index", compact("trainings"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::all(); // Fetch employees for dropdown
        return view("employee_trainings.create", compact("employees"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "employee_id" => "required|exists:employees,id",
            "description" => "required|string|max:255",
            "last_training_at" => "required|date",
            "evaluated" => "nullable|boolean",
        ]);

        EmployeeTraining::create($request->all());

        return redirect()
            ->route("employee_trainings.index")
            ->with("success", "Training record added successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Fetch the specific training record and its associated employee
        $training = EmployeeTraining::with("employee")->findOrFail($id);

        return view("employee_trainings.show", compact("training"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $training = EmployeeTraining::findOrFail($id);
        $employees = Employee::all(); // Fetch employees for dropdown
        return view("employee_trainings.edit", compact("training", "employees"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            "employee_id" => "required|exists:employees,id",
            "description" => "required|string|max:255",
            "last_training_at" => "required|date",
            "evaluated" => "nullable|boolean",
        ]);

        $training = EmployeeTraining::findOrFail($id);
        $training->update($request->all());

        return redirect()
            ->route("employee_trainings.index")
            ->with("success", "Training record updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $training = EmployeeTraining::findOrFail($id);
        $training->delete();

        return redirect()
            ->route("employee_trainings.index")
            ->with("success", "Training record deleted successfully.");
    }

    /**
     * Update the evaluation status of the specified resource.
     */
    public function evaluate(string $id)
    {
        $training = EmployeeTraining::findOrFail($id);
        $training->update(["evaluated" => true]);

        return redirect()
            ->route("employee_trainings.index")
            ->with("success", "Training record evaluated successfully.");
    }
}
