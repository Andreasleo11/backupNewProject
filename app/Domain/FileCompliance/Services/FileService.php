<?php

declare(strict_types=1);

namespace App\Domain\FileCompliance\Services;

use App\Models\File;
use Illuminate\Support\Facades\Storage;

final class FileService
{
    /**
     * Upload files with document number.
     */
    public function uploadFiles(array $files, string $docNum): int
    {
        $uploadedCount = 0;

        foreach ($files as $file) {
            $fileName = time() . '-' . $file->getClientOriginalName();
            $fileSize = $file->getSize();

            $file->storeAs('public/files', $fileName);

            File::create([
                'doc_id' => $docNum,
                'name' => $fileName,
                'mime_type' => $file->getClientMimeType(),
                'size' => $fileSize,
            ]);

            $uploadedCount++;
        }

        return $uploadedCount;
    }

    /**
     * Upload evaluation files with auto-generated doc_id.
     */
    public function uploadEvaluationFiles(array $files, int $month, int $year, string $dept): int
    {
        $prefix = sprintf('%04d-%02d-%s-', $year, $month, strtoupper($dept));
        $uploadedCount = 0;

        foreach ($files as $file) {
            $fileName = time() . '-' . $file->getClientOriginalName();
            $fileSize = $file->getSize();

            $file->storeAs('public/files', $fileName);

            $docId = $this->generateDocId($prefix);

            File::create([
                'doc_id' => $docId,
                'name' => $fileName,
                'mime_type' => $file->getClientMimeType(),
                'size' => $fileSize,
            ]);

            $uploadedCount++;
        }

        return $uploadedCount;
    }

    /**
     * Delete file from storage and database.
     */
    public function deleteFile(int $fileId): bool
    {
        $file = File::find($fileId);

        if ($file) {
            Storage::delete('public/files/' . $file->name);
            $file->delete();

            return true;
        }

        return false;
    }

    /**
     * Get files by year, month, and department.
     */
    public function getFilesByFilter(int $year, int $month, string $dept): \Illuminate\Database\Eloquent\Collection
    {
        $pattern = "{$year}-{$month}-{$dept}-%";

        return File::where('doc_id', 'LIKE', $pattern)->get();
    }

    /**
     * Generate incremental doc_id for evaluation uploads.
     */
    private function generateDocId(string $prefix): string
    {
        $lastDoc = File::where('doc_id', 'like', $prefix . '%')
            ->orderByDesc('doc_id')
            ->first();

        $incrementNumber = 1;
        if ($lastDoc) {
            preg_match('/(\d+)$/', $lastDoc->doc_id, $matches);
            $incrementNumber = intval($matches[0]) + 1;
        }

        $incrementalDocId = sprintf('%03d', $incrementNumber);

        return $prefix . $incrementalDocId;
    }
}
