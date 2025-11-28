<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\SpecificationDataTable;
use App\Http\Controllers\Controller;
use App\Models\Specification;
use Illuminate\Http\Request;

class SpecificationController extends Controller
{
    public function index(SpecificationDataTable $dataTable)
    {
        return $dataTable->render('admin.specifications.index');
    }

    public function store(Request $request)
    {
        $request->validate(['name']);

        Specification::create([
            'name' => strtoupper($request->name),
        ]);

        return redirect()
            ->back()
            ->with(['success' => 'Specification added successfully!']);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name']);

        return redirect()
            ->back()
            ->with(['success' => 'Specification updated successfully!']);
    }

    public function destroy($id)
    {
        Specification::find($id)->delete();

        return redirect()
            ->back()
            ->with(['success' => 'Specification deleted successfully!']);
    }
}
