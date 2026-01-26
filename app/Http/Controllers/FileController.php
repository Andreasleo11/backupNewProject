<?php

namespace App\Http\Controllers;

use App\Domain\FileCompliance\Services\FileService;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function __construct(
        private readonly FileService $fileService
    ) {}

    public function upload(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:32000',
            'doc_num' => 'string',
        ]);

        if ($request->hasFile('files')) {
            $this->fileService->uploadFiles($request->file('files'), $request->doc_num);
        }

        return redirect()->back()->with(['success' => 'Files successfully uploaded!']);
    }

    public function uploadEvaluation(Request $request)
    {
        $month = $request->input('filter_month');
        $year = $request->input('filter_year');
        $dept = $request->input('department');

        if ($request->hasFile('files')) {
            $this->fileService->uploadEvaluationFiles($request->file('files'), $month, $year, $dept);
        }

        return redirect()->back()->with(['success' => 'Files successfully uploaded!']);
    }

    public function destroy($id)
    {
        $this->fileService->deleteFile($id);

        return redirect()->back()->with(['success' => 'File successfully deleted']);
    }

    public function getFiles(Request $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');
        $dept = $request->input('dept');

        $files = $this->fileService->getFilesByFilter($year, $month, $dept);

        return response()->json(['files' => $files]);
    }
}
