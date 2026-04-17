<?php

use App\Http\Controllers\AdjustFormQcController;
use App\Http\Controllers\DefectCategoryController;
use App\Http\Controllers\qaqc\QaqcReportController;
use App\Livewire\Admin\Verification\Defects\CatalogEdit;
use App\Livewire\Admin\Verification\Defects\CatalogIndex;
use App\Livewire\InspectionForm;
use App\Livewire\InspectionIndex;
use App\Livewire\InspectionShow;
use App\Livewire\PartPriceLogImport;
use App\Livewire\ReportWizard;
use App\Livewire\Verification\Index as VerificationIndex;
use App\Livewire\Verification\Show as VerificationShow;
use App\Livewire\Verification\Wizard;
use Illuminate\Support\Facades\Route;

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

    Route::get('/qaqc/reports', [QaqcReportController::class, 'index'])->name('qaqc.report.index');
    Route::get('/qaqc/report/{id}', [QaqcReportController::class, 'detail'])->name('qaqc.report.detail');
    Route::get('/qaqc/report/{reportId}/edit', ReportWizard::class)->name('qaqc.report.edit');
    Route::get('/qaqc/reports/create', ReportWizard::class)->name('qaqc.report.create');
    Route::get('qaqc/report/{id}/rejectAuto', [QaqcReportController::class, 'rejectAuto'])->name('qaqc.report.rejectAuto');
    Route::get('qaqc/report/{id}/savePdf', [QaqcReportController::class, 'savePdf'])->name('qaqc.report.savePdf');
    Route::post('qaqc/report/{id}/sendEmail', [QaqcReportController::class, 'sendEmail'])->name('qaqc.report.sendEmail');
    Route::delete('/qaqc/report/{id}', [QaqcReportController::class, 'destroy'])->name('qaqc.report.delete');

    Route::post('/save-image-path/{reportId}/{section}', [QaqcReportController::class, 'saveImagePath']);
    Route::post('/qaqc/{id}/upload-attachment', [QaqcReportController::class, 'uploadAttachment'])->name('uploadAttachment');
    Route::post('/qaqc/report/{reportId}/autograph/{section}', [QaqcReportController::class, 'storeSignature'])->name('qaqc.report.autograph.store');

    Route::get('/admin/price-log/import', PartPriceLogImport::class)->name('price-log.import');

    Route::get('/qaqc/reports/redirectToIndex', [QaqcReportController::class, 'redirectToIndex'])->name('qaqc.report.redirect.to.index');

    Route::get('/items', [QaqcReportController::class, 'getItems'])->name('items');
    Route::get('/customers', [QaqcReportController::class, 'getCustomers'])->name('Customers');
    Route::get('/item/price', [QaqcReportController::class, 'getItemPrice']);

    Route::get('/qaqc/reports/{id}/download', [QaqcReportController::class, 'exportToPdf'])->name('qaqc.report.download');
    Route::get('/qaqc/reports/{id}/preview', [QaqcReportController::class, 'previewPdf'])->name('qaqc.report.preview');
    Route::get('qaqc/report/{id}/lock', [QaqcReportController::class, 'lock'])->name('qaqc.report.lock');
    Route::get('/qaqc/export-reports', [QaqcReportController::class, 'exportToExcel'])->name('export.reports');
    Route::get('/qaqc/FormAdjust', [QaqcReportController::class, 'exportFormAdjustToExcel'])->name('export.formadjusts');

    Route::put('/qaqc/reports/{id}/updateDoNumber', [QaqcReportController::class, 'updateDoNumber'])->name('update.do.number');

    Route::get('/qaqc/monthlyreport', [QaqcReportController::class, 'monthlyreport'])->name('qaqc.summarymonth');
    Route::post('/monthlyreport', [QaqcReportController::class, 'showDetails'])->name('monthlyreport.details');
    Route::post('/monthlyreport/export', [QaqcReportController::class, 'export'])->name('monthlyreport.export');

    // Defect Categories
    Route::get('/qaqc/defectcategory', [DefectCategoryController::class, 'index'])->name('qaqc.defectcategory');
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
            Route::get('/create', Wizard::class)->name('create');
            Route::get('/{report}/edit', Wizard::class)->name('edit');
            Route::get('/{report}', VerificationShow::class)->name('show');
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
