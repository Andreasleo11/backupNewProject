<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function upload(Request $request){
        $request->validate([
            'files.*' => 'required|file|max:16000',
            'doc_num' => 'string',
        ]);

        if($request->hasFile('files')){
            // dd($request->files);
            foreach ($request->file('files') as $file) {
                // dd($file);
                // Generate a unique filename
                $fileName = time() . '-' . $file->getClientOriginalName();

                // Get the file size in bytes
                $fileSize = $file->getSize();

                // Read file content
                $fileData = file_get_contents($file->getRealPath());

                // Store the file in the filesystem
                $file->storeAs('public/files', $fileName);

                // Store file data in the database
                File::create([
                    'doc_id' => $request->doc_num,
                    'name' => $fileName,
                    'mime_type' => $file->getClientMimeType(),
                    'data' => $fileData,
                    'size' => $fileSize,
                ]);
            }
        }

        return redirect()->back()->with(['success' => 'Files successfully uploaded!']);
    }

    public function destroy($id){
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
}
