<?php

use App\Domain\FileCompliance\Services\FileService;
use App\Models\File;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new FileService;
    Storage::fake('public');
});

test('it can upload multiple files with doc number', function () {
    $files = [
        UploadedFile::fake()->create('document1.pdf', 100),
        UploadedFile::fake()->create('document2.pdf', 150),
    ];

    $count = $this->service->uploadFiles($files, 'DOC-2026-001');

    expect($count)->toBe(2);
    $this->assertDatabaseCount('files', 2);
    $this->assertDatabaseHas('files', [
        'doc_id' => 'DOC-2026-001',
    ]);
    Storage::disk('public')->assertExists('files/' . $files[0]->hashName());
});

test('it can upload evaluation files with auto generated doc id', function () {
    $files = [
        UploadedFile::fake()->create('evaluation1.xlsx', 200),
    ];

    $count = $this->service->uploadEvaluationFiles($files, 1, 2026, 'IT');

    expect($count)->toBe(1);
    $this->assertDatabaseHas('files', [
        'doc_id' => '2026-01-IT-001',
    ]);
});

test('it generates incremental doc ids for evaluation uploads', function () {
    // Create existing file
    File::create([
        'doc_id' => '2026-01-IT-001',
        'name' => 'existing.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1000,
    ]);

    $files = [
        UploadedFile::fake()->create('new-doc.xlsx', 200),
    ];

    $count = $this->service->uploadEvaluationFiles($files, 1, 2026, 'IT');

    $this->assertDatabaseHas('files', [
        'doc_id' => '2026-01-IT-002',
    ]);
});

test('it can delete file from storage and database', function () {
    $fileName = 'test-file.pdf';
    Storage::disk('public')->put('files/' . $fileName, 'test content');

    $file = File::create([
        'doc_id' => 'DOC-001',
        'name' => $fileName,
        'mime_type' => 'application/pdf',
        'size' => 1000,
    ]);

    $result = $this->service->deleteFile($file->id);

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('files', ['id' => $file->id]);
    Storage::disk('public')->assertMissing('files/' . $fileName);
});

test('it returns false when deleting non existent file', function () {
    $result = $this->service->deleteFile(999);

    expect($result)->toBeFalse();
});

test('it can get files by filter criteria', function () {
    File::create([
        'doc_id' => '2026-01-IT-001',
        'name' => 'file1.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1000,
    ]);
    File::create([
        'doc_id' => '2026-01-IT-002',
        'name' => 'file2.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1500,
    ]);
    File::create([
        'doc_id' => '2026-02-HR-001',
        'name' => 'file3.pdf',
        'mime_type' => 'application/pdf',
        'size' => 2000,
    ]);

    $files = $this->service->getFilesByFilter(2026, 1, 'IT');

    expect($files)->toHaveCount(2);
});

test('it stores file metadata correctly', function () {
    $file = UploadedFile::fake()->create('test.pdf', 500, 'application/pdf');

    $this->service->uploadFiles([$file], 'DOC-TEST');

    $this->assertDatabaseHas('files', [
        'doc_id' => 'DOC-TEST',
        'mime_type' => 'application/pdf',
        'size' => 512000, // 500KB in bytes
    ]);
});
