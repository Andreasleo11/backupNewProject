<?php

namespace App\Http\Controllers;

use App\Models\DefectCategory;
use Illuminate\Http\Request;

class DefectCategoryController extends Controller
{
    public function index()
    {
        $defectCategories = DefectCategory::get();

        return view("qaqc.reports.defect-categories", compact("defectCategories"));
    }

    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|string|max:255",
        ]);

        $newdefect = new DefectCategory();
        $newdefect->name = ucfirst($request->input("name"));
        $newdefect->save();

        return redirect()->back()->with("success", "Category added successfully!");
    }

    public function update($id, Request $request)
    {
        $request->validate([
            "name" => "required|string|max:255",
        ]);

        DefectCategory::find($id)->update($request->all());

        return redirect()
            ->back()
            ->with(["success" => "Category updated successfully!"]);
    }

    public function destroy($id)
    {
        DefectCategory::find($id)->delete();
        return redirect()
            ->back()
            ->with(["success" => "Category deleted successfully!"]);
    }
}
