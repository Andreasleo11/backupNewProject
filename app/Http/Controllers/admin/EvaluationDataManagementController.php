<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EvaluationData;
use Illuminate\Support\Facades\DB;

class EvaluationDataManagementController extends Controller
{
    /**
     * Display a listing of the Evaluation Data.
     */
    public function index(\App\DataTables\Admin\EvaluationDataManagementDataTable $dataTable)
    {
        return $dataTable->render('administration.evaluation-data.index');
    }

    /**
     * Store and process the uploaded Excel/CSV file.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        return response()->json(['success' => true, 'message' => 'Upload logic stubbed']);
    }

    /**
     * Remove the specified row from storage.
     */
    public function destroy($id)
    {
        $evaluation = EvaluationData::findOrFail($id);
        $evaluation->delete();

        return response()->json(['success' => true, 'message' => 'Data evaluation berhasil dihapus.']);
    }

    /**
     * Truncate or bulk delete evaluation data.
     */
    public function truncate()
    {
        EvaluationData::truncate();
        return response()->json(['success' => true, 'message' => 'Seluruh data berhasil dihapus.']);
    }
}
