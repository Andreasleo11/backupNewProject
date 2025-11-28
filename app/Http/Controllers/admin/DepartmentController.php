<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\DepartmentsDataTable;
use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(DepartmentsDataTable $dataTable)
    {
        $departments = Department::all();

        // return view('admin.departments.index', compact('departments'));
        return $dataTable->render('admin.departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255|string',
            'dept_no' => 'nullable|max:255|string',
            'is_office' => 'boolean',
        ]);

        Department::create([
            'name' => strtoupper($request->name),
            'dept_no' => $request->dept_no,
            'is_office' => (bool) $request->is_office,
        ]);

        return redirect()
            ->route('admin.departments')
            ->with(['success' => 'Department added successfully!']);
    }

    public function update(Request $request, $id)
    {
        $department = Department::find($id);
        $request->validate([
            'name' => 'required|max:255|string',
            'dept_no' => 'nullable|max:255|string',
            'is_office' => 'boolean',
        ]);

        $department->update($request->all());

        return redirect()
            ->route('admin.departments')
            ->with(['success' => 'Department updated successfully!']);
    }

    public function destroy($id)
    {
        Department::find($id)->delete();

        return redirect()
            ->route('admin.departments')
            ->with(['success' => 'Departments deleted successfully!']);
    }
}
