<?php

use App\Domain\Verification\Services\VerificationExportService;
use App\Domain\Verification\Services\VerificationEmailService;
use App\Exports\VerificationReportsExport;
use App\Exports\MonthlyVerificationExport;
use App\Http\Controllers\AdjustFormQcController;
use App\Http\Controllers\DefectCategoryController;
use App\Livewire\Admin\Verification\Defects\CatalogEdit;
use App\Livewire\Admin\Verification\Defects\CatalogIndex;
use App\Livewire\InspectionForm;
use App\Livewire\InspectionIndex;
use App\Livewire\InspectionShow;
use App\Livewire\PartPriceLogImport;
use App\Livewire\Verification\Dashboard as VerificationDashboard;
use App\Livewire\Verification\Index as VerificationIndex;
use App\Livewire\Verification\Show as VerificationShow;
use App\Livewire\Verification\Wizard;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

/*
|--------------------------------------------------------------------------
| Quality Control Routes
|--------------------------------------------------------------------------
|
| Routes for managing QA/QC reports, form adjustments, defect categories,
| verification reports, and inspection reports.
|
| RECOMMENDED PERMISSIONS:
| - quality.view-reports
| - quality.create-reports
| - quality.approve-reports
| - quality.manage-defects
| - quality.manage-adjustments
|
| RECOMMENDED ROLES: admin, super-admin, quality, operations, manager
|
*/

Route::middleware('auth')->group(function () {
    // QA/QC Reports
    Route::get('listformadjust/all', [AdjustFormQcController::class, 'listformadjust'])->name('listformadjust');

    Route::get('/qaqc/reports', function () {
        return redirect()->route('verification.index');
    })->name('qaqc.report.index');

    Route::get('/qaqc/report/{id}', function ($id) {
        $newReport = \App\Infrastructure\Persistence\Eloquent\Models\VerificationReport::whereJsonContains('meta->legacy_id', (int) $id)->first();
        if ($newReport) {
            return redirect()->route('verification.show', $newReport->id);
        }
        return redirect()->route('verification.index');
    })->name('qaqc.report.detail');

    // Legacy report index — redirect to new system
    Route::get('/qaqc/reports', fn() => redirect()->route('verification.index'))->name('qaqc.report.index');

    // Legacy savePdf / sendEmail / rejectAuto / lock / destroy — redirect to new system
    Route::get('qaqc/report/{id}/rejectAuto', fn($id) => redirect()->route('verification.index'))->name('qaqc.report.rejectAuto');
    Route::get('qaqc/report/{id}/savePdf', fn($id) => redirect()->route('verification.download', $id))->name('qaqc.report.savePdf');
    Route::post('qaqc/report/{id}/sendEmail', fn($id) => redirect()->route('verification.index'))->name('qaqc.report.sendEmail');
    Route::delete('/qaqc/report/{id}', fn($id) => redirect()->route('verification.index'))->name('qaqc.report.delete');
    Route::get('qaqc/report/{id}/lock', fn($id) => redirect()->route('verification.show', $id))->name('qaqc.report.lock');

    // Legacy autograph/image paths — stubs (AdjustForm still needs storeSignature via its own JS)
    Route::post('/save-autograph-path/{reportId}/{section}', fn() => response()->json(['message' => 'ok']));
    Route::post('/save-image-path/{reportId}/{section}', fn() => response()->json(['message' => 'ok']));
    Route::post('/qaqc/{id}/upload-attachment', fn() => redirect()->back())->name('uploadAttachment');
    Route::post('/qaqc/report/{reportId}/autograph/{section}', fn() => response()->json(['message' => 'ok']))->name('qaqc.report.autograph.store');

    // Autocomplete endpoints — ponytail: keep alive as these are used by Wizard/form modals
    Route::get('/items', fn() => response()->json([]))->name('items');
    Route::get('/customers', fn() => response()->json([]))->name('Customers');
    Route::get('/item/price', fn() => response()->json([]));

    // Legacy export/download — redirect to new routes
    Route::get('/qaqc/reports/{id}/download', fn($id) => redirect()->route('verification.download', $id))->name('qaqc.report.download');
    Route::get('/qaqc/reports/{id}/preview', fn($id) => redirect()->route('verification.preview', $id))->name('qaqc.report.preview');
    Route::get('/qaqc/export-reports', fn() => redirect()->route('verification.export'))->name('export.reports');
    Route::get('/qaqc/FormAdjust', fn() => redirect()->route('verification.export'))->name('export.formadjusts');

    Route::put('/qaqc/reports/{id}/updateDoNumber', fn() => redirect()->back())->name('update.do.number');

    // Legacy monthly report — redirect to new monthly export
    Route::get('/qaqc/monthlyreport', fn() => redirect()->route('verification.index'))->name('qaqc.summarymonth');
    Route::post('/monthlyreport', fn() => redirect()->back())->name('monthlyreport.details');
    Route::post('/monthlyreport/export', fn() => redirect()->back())->name('monthlyreport.export');

    Route::get('/qaqc/defectcategory', \App\Livewire\Qaqc\DefectCategoryManager::class)->name('qaqc.defectcategory');

    // Legacy defect category routes (restored for partial modals and report wizard)
    Route::post('/qaqc/defectcategory/store', [DefectCategoryController::class, 'store'])->name('qaqc.defectcategory.store');
    Route::put('/qaqc/defectcategory/{id}/update', [DefectCategoryController::class, 'update'])->name('qaqc.defectcategory.update');
    Route::delete('/qaqc/defectcategory/{id}/delete', [DefectCategoryController::class, 'destroy'])->name('qaqc.defectcategory.delete');

    // Form Adjust Section
    Route::get('/qaqc/adjustform', [AdjustFormQcController::class, 'index'])->name('adjust.index');
    Route::post('/qaqc/save/formadjust', [AdjustFormQcController::class, 'save'])->name('save.rawmaterial');
    Route::post('/fgwarehouse/save/adjust', [AdjustFormQcController::class, 'savewarehouse'])->name('fgwarehousesave');
    Route::get('/view/adjustform', [AdjustFormQcController::class, 'adjustformview'])->name('adjustview');
    Route::post('/remark/detail/adjust', [AdjustFormQcController::class, 'addremarkadjust'])->name('addremarkadjust');
    Route::post('/save-autograph-path/{reportId}/{section}', [AdjustFormQcController::class, 'saveAutographPath']);

    // Verification Reports
    Route::prefix('verification-reports')
        ->name('verification.')
        ->group(function () {
            Route::get('/', VerificationIndex::class)->name('index');
            Route::get('/dashboard', VerificationDashboard::class)->name('dashboard');
            Route::get('/create', Wizard::class)->name('create');
            Route::get('/{report}/edit', Wizard::class)->name('edit');
            Route::get('/{report}', VerificationShow::class)->name('show');

            // PDF & email — served via controller closure to return responses from Livewire-inaccessible methods
            Route::get('/{report}/download', function (int $report, VerificationExportService $service) {
                return $service->exportToPdf($report);
            })->name('download');

            Route::get('/{report}/preview', function (int $report, VerificationExportService $service) {
                return $service->previewPdf($report);
            })->name('preview');

            Route::post('/{report}/send-email', function (int $report, VerificationEmailService $emailService, VerificationExportService $exportService) {
                $emailService->sendEmail($report, request()->only('to', 'cc', 'subject', 'body'), $exportService);
                return back()->with('success', 'Email sent.');
            })->name('sendEmail');

            // Excel exports
            Route::get('/export/all', function () {
                return Excel::download(new VerificationReportsExport, 'verification-reports.xlsx');
            })->name('export');

            Route::get('/export/monthly', function () {
                $month = request('month', now()->month);
                $year  = request('year', now()->year);
                return Excel::download(new MonthlyVerificationExport((int)$month, (int)$year), "monthly-verification-{$year}-{$month}.xlsx");
            })->name('export.monthly');
        });

    // Defect Catalog (Admin)
    Route::middleware(['can:manage-defects'])
        ->prefix('admin/verification/defects')
        ->name('admin.verification.defects.')
        ->group(function () {
            Route::get('/', CatalogIndex::class)->name('index');
            Route::get('/create', CatalogEdit::class)->name('create');
            Route::get('/{id}/edit', CatalogEdit::class)->name('edit');
        });
});

// Inspection Reports
Route::get('/inspection-reports', InspectionIndex::class)->name('inspection-reports.index');
Route::get('/inspection-report/create', InspectionForm::class)->name('inspection-reports.create');
Route::get('/inspection-reports/dashboard', \App\Livewire\InspectionDashboard::class);
Route::get('/inspection-reports/{inspection_report}', InspectionShow::class)->name('inspection-reports.show');

// Department-specific QA/QC routes
Route::middleware(['checkDepartment:QA,QC,ACCOUNTING,PPIC,STORE,LOGISTIC,DIRECTOR,PLASTIC INJECTION', 'checkSessionId'])->group(function () {
    // FORM ADJUST SECTION (duplicate routes with department restrictions)
    Route::get('/qaqc/adjustform', [AdjustFormQcController::class, 'index'])->name('adjust.index');
    Route::post('/qaqc/save/formadjust', [AdjustFormQcController::class, 'save'])->name('save.rawmaterial');
    Route::post('/fgwarehouse/save/adjust', [AdjustFormQcController::class, 'savewarehouse'])->name('fgwarehousesave');
    Route::get('/view/adjustform', [AdjustFormQcController::class, 'adjustformview'])->name('adjustview');
    Route::post('/remark/detail/adjust', [AdjustFormQcController::class, 'addremarkadjust'])->name('addremarkadjust');
    Route::post('/save-autograph-path/{reportId}/{section}', [AdjustFormQcController::class, 'saveAutographPath']);
});
