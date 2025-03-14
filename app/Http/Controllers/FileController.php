<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        
        $request->validate([
            'files.*' => 'required|file|max:32000',
            'doc_num' => 'string',
        ]);

        if ($request->hasFile('files')) {
            // dd($request->files);
            foreach ($request->file('files') as $file) {
                // Generate a unique filename
                $fileName = time() . '-' . $file->getClientOriginalName();

                // Get the file size in bytes
                $fileSize = $file->getSize();

                // Store the file in the filesystem
                $file->storeAs('public/files', $fileName);

                // Store file data in the database
                File::create([
                    'doc_id' => $request->doc_num,
                    'name' => $fileName,
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $fileSize,
                ]);
            }
        }

        return redirect()->back()->with(['success' => 'Files successfully uploaded!']);
    }

    public function uploadEvaluation(Request $request)
    {
            // Get filter month, year, and department from the request
        $month = $request->input('filter_month');
        $year = $request->input('filter_year');
        $dept = $request->input('department');
    
        // Generate the prefix for doc_id (e.g., 2025-02-IT-)
        $prefix = sprintf('%04d-%02d-%s-', $year, $month, strtoupper($dept));
        
        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                // Generate a unique filename (timestamp + original filename)
                $fileName = time() . '-' . $file->getClientOriginalName();
    
                // Get the file size
                $fileSize = $file->getSize();
    
                // Store the file in the filesystem (e.g., 'uploads' folder)
                $filePath = $file->storeAs('public/files', $fileName);
                
                // Find the last document with the same year, month, and department to determine the next incremental number
                $lastDoc = File::where('doc_id', 'like', $prefix . '%') // Filter by the prefix
                    ->orderByDesc('doc_id')
                    ->first();
                
                // Generate the incremental number
                $incrementNumber = 1;
                if ($lastDoc) {
                    // Extract the incremental number from the last doc_id (e.g., HR-001 -> 1)
                    preg_match('/(\d+)$/', $lastDoc->doc_id, $matches);
                    $incrementNumber = intval($matches[0]) + 1;
                }
    
                // Format the incremental number to be zero-padded (e.g., 001, 002, ...)
                $incrementalDocId = sprintf('%03d', $incrementNumber);
    
                // Combine the prefix with the incremental number to generate a unique doc_id
                $docId = $prefix . $incrementalDocId;
             
                // Store file data in the database
                File::create([
                    'doc_id' => $docId,
                    'name' => $fileName,
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $fileSize,
                ]);
            }
        }
    
        // Redirect back with a success message
        return redirect()->back()->with(['success' => 'Files successfully uploaded!']);
    }

    public function destroy($id)
    {
        $file = File::find($id);
        // Check if the file exists
        if ($file) {
            // Get the filename from the record
            $filename = $file->name;

            // Delete the file from the storage directory
            Storage::delete('public/files/' . $filename);

            // Delete the database record
            $file->delete();
        }
        return redirect()->back()->with(['success' => 'File successfully deleted']);
    }

    public function getFiles(Request $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');
        $dept = $request->input('dept');

        $pattern = "{$year}-{$month}-{$dept}-%";
        
        $files = File::where('doc_id', 'LIKE', $pattern)->get();

        return response()->json(['files' => $files]);
    }
}
