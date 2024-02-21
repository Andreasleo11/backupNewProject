<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::all();
        return view('admin.departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255|string',
        ]);

        Department::create([
            'name' => strtoupper($request->name)
        ]);

        return redirect()->route('superadmin.departments')->with(['success' => 'Department added successfully!']);
    }

    public function update(Request $request, $id)
    {
        $department = Department::find($id);
        $request->validate([
            'name' => 'required|max:255|string',
        ]);

        $department->update($request->all());

        return redirect()->route('superadmin.departments')->with(['success' => 'Department updated successfully!']);
    }

    public function destroy($id)
    {
        Department::find($id)->delete();
        return redirect()->route('superadmin.departments')->with(['success' => 'Departments deleted successfully!']);
    }
}
