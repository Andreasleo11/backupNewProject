<?php

namespace Tests\Unit\Domain\FileCompliance;

use App\Domain\FileCompliance\Services\FileService;
use App\Models\File;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileServiceTest extends TestCase
{
    use RefreshDatabase;

    private FileService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FileService();
        Storage::fake('public');
    }

    /** @test */
    public function it_can_upload_multiple_files_with_doc_number()
    {
        $files = [
            UploadedFile::fake()->create('document1.pdf', 100),
            UploadedFile::fake()->create('document2.pdf', 150),
        ];

        $count = $this->service->uploadFiles($files, 'DOC-2026-001');

        $this->assertEquals(2, $count);
        $this->assertDatabaseCount('files', 2);
        $this->assertDatabaseHas('files', [
            'doc_id' => 'DOC-2026-001',
        ]);
        Storage::disk('public')->assertExists('files/' . $files[0]->hashName());
    }

    /** @test */
    public function it_can_upload_evaluation_files_with_auto_generated_doc_id()
    {
        $files = [
            UploadedFile::fake()->create('evaluation1.xlsx', 200),
        ];

        $count = $this->service->uploadEvaluationFiles($files, 1, 2026, 'IT');

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('files', [
            'doc_id' => '2026-01-IT-001',
        ]);
    }

    /** @test */
    public function it_generates_incremental_doc_ids_for_evaluation_uploads()
    {
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
    }

    /** @test */
    public function it_can_delete_file_from_storage_and_database()
    {
        $fileName = 'test-file.pdf';
        Storage::disk('public')->put('files/' . $fileName, 'test content');

        $file = File::create([
            'doc_id' => 'DOC-001',
            'name' => $fileName,
            'mime_type' => 'application/pdf',
            'size' => 1000,
        ]);

        $result = $this->service->deleteFile($file->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('files', ['id' => $file->id]);
        Storage::disk('public')->assertMissing('files/' . $fileName);
    }

    /** @test */
    public function it_returns_false_when_deleting_non_existent_file()
    {
        $result = $this->service->deleteFile(999);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_get_files_by_filter_criteria()
    {
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

        $this->assertCount(2, $files);
    }

    /** @test */
    public function it_stores_file_metadata_correctly()
    {
        $file = UploadedFile::fake()->create('test.pdf', 500, 'application/pdf');

        $this->service->uploadFiles([$file], 'DOC-TEST');

        $this->assertDatabaseHas('files', [
            'doc_id' => 'DOC-TEST',
            'mime_type' => 'application/pdf',
            'size' => 512000, // 500KB in bytes
        ]);
    }
}
