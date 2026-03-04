<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EvaluationDataWeekly;
use Illuminate\Support\Facades\DB;

class EvaluationDataWeeklyManagementController extends Controller
{
    /**
     * Display a listing of the Weekly Evaluation Data.
     */
    public function index(\App\DataTables\Admin\EvaluationDataWeeklyManagementDataTable $dataTable)
    {
        return $dataTable->render('administration.evaluation-data-weekly.index');
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
        $evaluation = EvaluationDataWeekly::findOrFail($id);
        $evaluation->delete();

        return response()->json(['success' => true, 'message' => 'Data evaluation weekly berhasil dihapus.']);
    }

    /**
     * Truncate or bulk delete evaluation data.
     */
    public function truncate()
    {
        EvaluationDataWeekly::truncate();
        return response()->json(['success' => true, 'message' => 'Seluruh data berhasil dihapus.']);
    }
}
