<?php

use App\Domain\Overtime\Services\OvertimeImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new OvertimeImportService;
});

describe('OvertimeImportService', function () {
    it('imports overtime data from valid Excel file', function () {
        // Create a test Excel file
        $filePath = storage_path('app/test_overtime_import.xlsx');

        // Create a simple Excel with PhpSpreadsheet for testing
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Add headers (rows 0-3)
        $sheet->setCellValue('A1', 'Overtime Import');
        $sheet->setCellValue('A2', 'Date: 2026-01-23');
        $sheet->setCellValue('A3', 'Total Records');
        $sheet->setCellValue('A4', 'Key');

        // Add data row
        $sheet->setCellValue('A5', 'LINE 1 ID');
        $sheet->setCellValue('B5', 'OT-2026-001');
        $sheet->setCellValue('C5', \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(new DateTime('2026-01-15')));
        $sheet->setCellValue('D5', \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(new DateTime('18:00:00')));
        $sheet->setCellValue('E5', \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(new DateTime('2026-01-15')));
        $sheet->setCellValue('F5', \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(new DateTime('22:00:00')));
        $sheet->setCellValue('G5', 4.0);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filePath);

        $result = $this->service->importFromExcel($filePath);

        expect($result['success'])->toBeTrue();
        expect($result['imported'])->toBe(1);

        // Verify data was saved
        $this->assertDatabaseHas('actual_overtime_details', [
            'key' => 1,
            'voucher' => 'OT-2026-001',
        ]);

        // Cleanup
        unlink($filePath);
    });

    it('skips header rows and empty rows', function () {
        $filePath = storage_path('app/test_overtime_import_empty.xlsx');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Only headers, no data
        $sheet->setCellValue('A1', 'Header 1');
        $sheet->setCellValue('A2', 'Header 2');
        $sheet->setCellValue('A3', 'Header 3');
        $sheet->setCellValue('A4', 'Header 4');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filePath);

        $result = $this->service->importFromExcel($filePath);

        expect($result['success'])->toBeTrue();
        expect($result['imported'])->toBe(0);
        expect($result['skipped'])->toBeGreaterThan(0);

        unlink($filePath);
    });

    it('handles import errors gracefully', function () {
        $result = $this->service->importFromExcel('/nonexistent/file.xlsx');

        expect($result['success'])->toBeFalse();
        expect($result['message'])->toContain('Gagal impor');
        expect($result)->toHaveKey('error');
    });

    it('parses date formats correctly', function () {
        // Use reflection to test private method
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('parseDate');
        $method->setAccessible(true);

        // Test numeric Excel date
        $excelDate = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(new DateTime('2026-01-15'));
        $result = $method->invoke($this->service, $excelDate);
        expect($result)->toBe('2026-01-15');

        // Test string date
        $result = $method->invoke($this->service, '15/01/2026');
        expect($result)->toBe('2026-01-15');

        // Test empty value
        $result = $method->invoke($this->service, null);
        expect($result)->toBeNull();
    });
});
