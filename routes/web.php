<?php

use App\Http\Controllers\AccountingPurchaseRequestController;
use App\Http\Controllers\AdjustFormQcController;
use App\Http\Controllers\admin\DepartmentController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\CapacityByForecastController;
use App\Http\Controllers\DefectCategoryController;
use App\Http\Controllers\DeliveryScheduleController;
use App\Http\Controllers\DetailPurchaseRequestController;
use App\Http\Controllers\director\DirectorHomeController;
use App\Http\Controllers\director\ReportController;
use App\Http\Controllers\DirectorPurchaseRequestController;
use App\Http\Controllers\DisciplinePageController;
use App\Http\Controllers\DownloadUploadController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDailyReportController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\EmployeeMasterController;
use App\Http\Controllers\EmployeeTrainingController;
use App\Http\Controllers\EvaluationDataController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ForecastCustomerController;
use App\Http\Controllers\FormCutiController;
use App\Http\Controllers\FormKeluarController;
use App\Http\Controllers\FormKerusakanController;
use App\Http\Controllers\FormOvertimeController;
use App\Http\Controllers\HolidayListController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\hrd\HrdHomeController;
use App\Http\Controllers\hrd\ImportantDocController;
use App\Http\Controllers\ImportJobController;
use App\Http\Controllers\InventoryFgController;
use App\Http\Controllers\InventoryMtrController;
use App\Http\Controllers\InvLineListController;
use App\Http\Controllers\LineDownController;
use App\Http\Controllers\MaintenanceInventoryController;
use App\Http\Controllers\MasterInventoryController;
use App\Http\Controllers\MasterTintaController;
use App\Http\Controllers\materialPredictionController;
use App\Http\Controllers\MonthlyBudgetReportController;
use App\Http\Controllers\MonthlyBudgetReportDetailController;
use App\Http\Controllers\MonthlyBudgetReportSummaryDetailController;
use App\Http\Controllers\MonthlyBudgetSummaryReportController;
use App\Http\Controllers\MouldDownController;
use App\Http\Controllers\NotificationFeedController;
use App\Http\Controllers\PEController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\pps\PPSAssemblyController;
use App\Http\Controllers\pps\PPSGeneralController;
use App\Http\Controllers\pps\PPSInjectionController;
use App\Http\Controllers\pps\PPSKarawangController;
use App\Http\Controllers\pps\PPSSecondController;
use App\Http\Controllers\PreviewUploadController;
use App\Http\Controllers\ProjectTrackerController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\PurchasingDetailController;
use App\Http\Controllers\PurchasingMaterialController;
use App\Http\Controllers\PurchasingReminderController;
use App\Http\Controllers\PurchasingRequirementController;
use App\Http\Controllers\PurchasingSupplierEvaluationController;
use App\Http\Controllers\qaqc\QaqcHomeController;
use App\Http\Controllers\qaqc\QaqcReportController;
use App\Http\Controllers\RequirementUploadDownloadController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\SpecificationController;
use App\Http\Controllers\StockTintaController;
use App\Http\Controllers\SuperAdminHomeController;
use App\Http\Controllers\SuratPerintahKerjaController;
use App\Http\Controllers\SyncProgressController;
use App\Http\Controllers\UpdateDailyController;
use App\Http\Controllers\UserHomeController;
use App\Http\Controllers\UserPermissionController;
use App\Http\Controllers\WaitingPurchaseOrderController;
use App\Http\Controllers\PEHomeController;
use App\Livewire\Admin\RequirementUploads\Review as ReviewUploads;
use App\Livewire\Compliance\Dashboard as ComplianceDashboard;
use App\Livewire\DailyReportIndex;
use App\Livewire\DeliveryNote\DeliveryNoteForm;
use App\Livewire\DeliveryNote\DeliveryNoteIndex;
use App\Livewire\DeliveryNote\DeliveryNotePrint;
use App\Livewire\DeliveryNoteShow;
use App\Livewire\DepartmentExpenses;
use App\Livewire\Departments\Compliance as DeptCompliance;
use App\Livewire\Departments\Overview as DepartmentsOverview;
use App\Livewire\DestinationForm;
use App\Livewire\DestinationIndex;
use App\Livewire\FileLibrary;
use App\Livewire\InspectionForm;
use App\Livewire\InspectionIndex;
use App\Livewire\InspectionShow;
use App\Livewire\MasterDataPart\ImportParts;
use App\Livewire\MonthlyBudgetSummary\Index as MonthlyBudgetSummaryIndex;
use App\Livewire\Overtime\Create as FormOvertimeCreate;
use App\Livewire\Overtime\Index as FormOvertimeIndex;
use App\Livewire\ReportWizard;
use App\Livewire\Requirements\Assign as ReqAssign;
use App\Livewire\Requirements\Departments as RequirementDepartments;
use App\Livewire\Requirements\Form as RequirementForm;
use App\Livewire\Requirements\Index as ReqIndex;
use App\Livewire\Services\Form as ServiceForm;
use App\Livewire\Signature\CaptureSignature;
use App\Livewire\Signature\ManageSignatures;
use App\Livewire\Vehicles\Form as VehiclesForm;
use App\Livewire\Vehicles\Index as VehiclesIndex;
use App\Livewire\Vehicles\Show as VehiclesShow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', fn() => view('welcome'))->name('/');

Route::middleware('guest')->prefix('daily-reports')->group(function() {
    Route::get('login', [EmployeeDailyReportController::class, 'showLoginForm'])->name('employee-login');
    Route::post('/login', [EmployeeDailyReportController::class, 'login'])->name('employee.login');
    Route::get('/', [EmployeeDailyReportController::class, 'dashboardDailyReport'])->name('daily-reports.index');
    Route::post('/logout', [EmployeeDailyReportController::class, 'logout'])->name('employee.logout');
    
    Route::get('/depthead/report/{employee_id}', [EmployeeDailyReportController::class, 'showDepthead'])->name('reports.depthead.show');
    
    Route::get('/upload-daily-report', [EmployeeDailyReportController::class, 'showUploadForm'])->name('daily-report.form');
    Route::post('/upload-daily-report', [EmployeeDailyReportController::class, 'upload'])->name('daily-report.upload');
    Route::get('/employee-daily-reports', [EmployeeDailyReportController::class, 'index']);
    Route::post('/daily-report/confirm-upload', [EmployeeDailyReportController::class, 'confirmUpload'])->name('daily-report.confirm-upload');  
});

// Route::middleware(['auth'])->group(function () {
//     Route::get('/daily-reports', DailyReportIndex::class)->name('daily-reports.index');
// });

Route::get('/', fn () => Auth::check() ? redirect()->intended('/home') : redirect()->intended(route('login')))->name('/');
Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/change-password', [PasswordChangeController::class, 'showChangePasswordForm'])->name('change.password.show');
Route::post('/change-password', [PasswordChangeController::class, 'changePassword'])->name('change.password');

Route::middleware(['checkUserRole:1', 'checkSessionId'])->group(function () {
    Route::get('/change-email/page', [SuperAdminHomeController::class, 'updateEmailpage'])->name('changeemail.page');
    Route::post('/change-email', [SuperAdminHomeController::class, 'updateEmail'])->name('email.update');
    Route::get('/get-email-settings/{feature}', [SuperAdminHomeController::class, 'getEmailSettings']);

    Route::get('/superadmin/home', [SuperAdminHomeController::class, 'index'])->name('superadmin');

    Route::prefix('superadmin')->group(function () {
        Route::name('superadmin.')->group(function () {
            Route::get('/users', [UserController::class, 'index'])
                ->name('users')
                ->middleware('permission:get-users');
            Route::post('/users/store', [UserController::class, 'store'])
                ->name('users.store')
                ->middleware('permission:store-users');
            Route::put('/users/update/{id}', [UserController::class, 'update'])
                ->name('users.update')
                ->middleware('permission:update-users');
            Route::delete('/users/delete/{id}', [UserController::class, 'destroy'])
                ->name('users.delete')
                ->middleware('permission:delete-users');
            Route::get('/users/reset/{id}', [UserController::class, 'resetPassword'])
                ->name('users.reset.password')
                ->middleware('permission:reset-password-users');
            Route::delete('/users/delete-selected', [UserController::class, 'deleteSelected'])
                ->name('users.deleteSelected')
                ->middleware('permission:delete-selected-users');

            Route::get('/departments', [DepartmentController::class, 'index'])
                ->name('departments')
                ->middleware('permission:get-departments');
            Route::post('/departments/store', [DepartmentController::class, 'store'])
                ->name('departments.store')
                ->middleware('permission:store-departments');
            Route::put('/departments/update/{id}', [DepartmentController::class, 'update'])
                ->name('departments.update')
                ->middleware('permission:update-departments');
            Route::delete('/departments/delete/{id}', [DepartmentController::class, 'destroy'])
                ->name('departments.delete')
                ->middleware('permission:delete-departments');

            Route::get('/specifications', [SpecificationController::class, 'index'])
                ->name('specifications')
                ->middleware('permission:get-specifications');
            Route::post('/specifications/store', [SpecificationController::class, 'store'])
                ->name('specifications.store')
                ->middleware('permission:store-specifications');
            Route::put('/specifications/{id}/update', [SpecificationController::class, 'update'])
                ->name('specifications.update')
                ->middleware('permission:update-specifications');
            Route::delete('/specifications/{id}/delete', [SpecificationController::class, 'destroy'])
                ->name('specifications.delete')
                ->middleware('permission:delete-specifications');

            Route::get('/users-permissions', [UserPermissionController::class, 'index'])
                ->name('users.permissions.index')
                ->middleware('permission:get-users-permissions');
            Route::put('/users-permissions/{id}/update', [UserPermissionController::class, 'update'])
                ->name('users.permissions.update')
                ->middleware('permission:update-users-permissions');

            Route::get('/permissions', [PermissionController::class, 'index'])
                ->name('permissions.index')
                ->middleware('permission:get-permissions');
            Route::post('/permissions/store', [PermissionController::class, 'store'])
                ->name('permissions.store')
                ->middleware('permission:store-permissions');
            Route::put('/permissions/{permission}', [PermissionController::class, 'update'])
                ->name('permissions.update')
                ->middleware('permission:update-permissions');
            Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])
                ->name('permissions.destroy')
                ->middleware('permission:delete-permissions');
        });
    });
});

Route::middleware(['checkUserRole:2,1', 'checkSessionId'])->group(function () {
    Route::middleware(['checkDepartment:QA,QC,ACCOUNTING,PPIC,STORE,LOGISTIC,BUSINESS', 'checkSessionId'])->group(function () {
        Route::get('/qaqc/home', [QaqcHomeController::class, 'index'])->name('qaqc');

        Route::post('/save-image-path/{reportId}/{section}', [QaqcReportController::class, 'saveImagePath']);
        Route::post('/qaqc/{id}/upload-attachment', [QaqcReportController::class, 'uploadAttachment'])->name('uploadAttachment');
        Route::post('/qaqc/report/{reportId}/autograph/{section}', [QaqcReportController::class, 'storeSignature'])->name('qaqc.report.autograph.store');

        Route::get('/qaqc/reports', [QaqcReportController::class, 'index'])
            ->name('qaqc.report.index')
            ->middleware('permission:get-vqc-reports');
        Route::get('/qaqc/report/{id}', [QaqcReportController::class, 'detail'])
            ->name('qaqc.report.detail')
            ->middleware('permission:detail-vqc-reports');
        Route::get('/qaqc/report/{reportId}/edit', ReportWizard::class)->name('qaqc.report.edit')->middleware('permission:edit-vqc-report');
        Route::get('/qaqc/reports/create', ReportWizard::class)->name('qaqc.report.create')->middleware('permission:create-vqc-report');
        Route::get('qaqc/report/{id}/rejectAuto', [QaqcReportController::class, 'rejectAuto'])->name('qaqc.report.rejectAuto');
        Route::get('qaqc/report/{id}/savePdf', [QaqcReportController::class, 'savePdf'])->name('qaqc.report.savePdf');
        Route::post('qaqc/report/{id}/sendEmail', [QaqcReportController::class, 'sendEmail'])->name('qaqc.report.sendEmail');
        Route::delete('/qaqc/report/{id}', [QaqcReportController::class, 'destroy'])
            ->name('qaqc.report.delete')
            ->middleware('permission:delete-vqc-report');

        // adding new defect category
        Route::get('/qaqc/defectcategory', [DefectCategoryController::class, 'index'])
            ->name('qaqc.defectcategory')
            ->middleware('permission:get-defect-categories');
        Route::post('/qaqc/defectcategory/store', [DefectCategoryController::class, 'store'])
            ->name('qaqc.defectcategory.store')
            ->middleware('permission:store-defect-category');
        Route::put('/qaqc/defectcategory/{id}/update', [DefectCategoryController::class, 'update'])
            ->name('qaqc.defectcategory.update')
            ->middleware('permission:update-defect-category');
        Route::delete('/qaqc/defectcategory/{id}/delete', [DefectCategoryController::class, 'destroy'])
            ->name('qaqc.defectcategory.delete')
            ->middleware('permission:delete-defect-category');
        // adding new defect category

        Route::get('/admin/price-log/import', \App\Livewire\PartPriceLogImport::class)
            ->name('price-log.import')
            ->middleware(['auth']);

        Route::get('/qaqc/reports/redirectToIndex', [QaqcReportController::class, 'redirectToIndex'])->name('qaqc.report.redirect.to.index');

        Route::get('/items', [QaqcReportController::class, 'getItems'])->name('items');
        Route::get('/customers', [QaqcReportController::class, 'getCustomers'])->name('Customers');
        Route::get('/item/price', [QaqcReportController::class, 'getItemPrice']);

        Route::get('/qaqc/reports/{id}/download', [QaqcReportController::class, 'exportToPdf'])
            ->name('qaqc.report.download')
            ->middleware('permission:download-vqc-report');
        Route::get('/qaqc/reports/{id}/preview', [QaqcReportController::class, 'previewPdf'])->name('qaqc.report.preview');
        Route::get('qaqc/report/{id}/lock', [QaqcReportController::class, 'lock'])
            ->name('qaqc.report.lock')
            ->middleware('permission:lock-vqc-report');
        Route::get('/qaqc/export-reports', [QaqcReportController::class, 'exportToExcel'])
            ->name('export.reports')
            ->middleware('permission:export-to-excel-vqc-report');
        Route::get('/qaqc/FormAdjust', [QaqcReportController::class, 'exportFormAdjustToExcel'])
            ->name('export.formadjusts')
            ->middleware();

        Route::put('/qaqc/reports/{id}/updateDoNumber', [QaQcReportController::class, 'updateDoNumber'])->name('update.do.number');

        Route::get('/qaqc/monthlyreport', [QaqcReportController::class, 'monthlyreport'])->name('qaqc.summarymonth');
        Route::post('/monthlyreport', [QaqcReportController::class, 'showDetails'])->name('monthlyreport.details');
        Route::post('/monthlyreport/export', [QaqcReportController::class, 'export'])->name('monthlyreport.export');
    });

    Route::middleware(['checkDepartment:QA,QC,ACCOUNTING,PPIC,STORE,LOGISTIC,DIRECTOR,PLASTIC INJECTION', 'checkSessionId'])->group(function () {
        // FORM ADJUST SECITON
        Route::get('/qaqc/adjustform', [AdjustFormQcController::class, 'index'])->name('adjust.index');
        Route::post('/qaqc/save/formadjust', [AdjustFormQcController::class, 'save'])->name('save.rawmaterial');
        Route::post('/fgwarehouse/save/adjust', [AdjustFormQcController::class, 'savewarehouse'])->name('fgwarehousesave');
        Route::get('/view/adjustform', [AdjustFormQcController::class, 'adjustformview'])->name('adjustview');
        Route::post('/remark/detail/adjust', [AdjustFormQcController::class, 'addremarkadjust'])->name('addremarkadjust');
        Route::post('/save-autograph-path/{reportId}/{section}', [AdjustFormQcController::class, 'saveAutographPath']);

        Route::get('listformadjust/all', [AdjustFormQcController::class, 'listformadjust'])->name('listformadjust');
    });

    Route::middleware(['checkDepartment:PERSONALIA'])->group(function () {
        Route::get('/hrd/home', [HrdHomeController::class, 'index'])->name('hrd');

        Route::get('/hrd/importantdocs/', [ImportantDocController::class, 'index'])
            ->name('hrd.importantDocs.index')
            ->middleware('permission:get-important-docs');
        Route::get('/hrd/importantdocs/create', [ImportantDocController::class, 'create'])
            ->name('hrd.importantDocs.create')
            ->middleware('permission:create-important-doc');
        Route::post('/hrd/importantdocs/store', [ImportantDocController::class, 'store'])
            ->name('hrd.importantDocs.store')
            ->middleware('permission:store-important-doc');
        Route::get('/hrd/importantdocs/{id}', [ImportantDocController::class, 'detail'])
            ->name('hrd.importantDocs.detail')
            ->middleware('permission:detail-important-doc');
        Route::get('/hrd/importantdocs/{id}/edit', [ImportantDocController::class, 'edit'])
            ->name('hrd.importantDocs.edit')
            ->middleware('permission:edit-important-doc');
        Route::put('/hrd/importantdocs/{id}', [ImportantDocController::class, 'update'])
            ->name('hrd.importantDocs.update')
            ->middleware('permission:update-important-doc');
        Route::delete('/hrd/importantdocs/{id}', [ImportantDocController::class, 'destroy'])
            ->name('hrd.importantDocs.delete')
            ->middleware('permission:delete-important-doc');
    });

    Route::middleware(['checkDepartment:MANAGEMENT'])->group(function () {
        Route::get('/director/home', [DirectorHomeController::class, 'index'])->name('director');
        Route::get('/director/qaqc/index', [ReportController::class, 'index'])
            ->name('director.qaqc.index')
            ->middleware('permission:get-vqc-reports-director');
        Route::get('/director/qaqc/detail/{id}', [ReportController::class, 'detail'])
            ->name('director.qaqc.detail')
            ->middleware('permission:detail-vqc-report-director');
        Route::put('/director/qaqc/approve/{id}', [ReportController::class, 'approve'])
            ->name('director.qaqc.approve')
            ->middleware('permission:approve-vqc-report-director');
        Route::put('/director/qaqc/reject/{id}', [ReportController::class, 'reject'])
            ->name('director.qaqc.reject')
            ->middleware('permission:reject-vqc-report-director');
        Route::put('/director/qaqc/approveSelected', [ReportController::class, 'approveSelected'])
            ->name('director.qaqc.approveSelected')
            ->middleware('permission:approve-selected-vqc-report-director');
        Route::put('/director/qaqc/rejectSelected', [ReportController::class, 'rejectSelected'])
            ->name('director.qaqc.rejectSelected')
            ->middleware('permission:reject-selected-vqc-report-director');

        Route::get('/director/pr/index', [DirectorPurchaseRequestController::class, 'index'])
            ->name('director.pr.index')
            ->middleware('permission:get-pr-director');
        Route::put('/director/pr/approveSelected', [DirectorPurchaseRequestController::class, 'approveSelected'])
            ->name('director.pr.approveSelected')
            ->middleware('permission:approve-selected-director');
        Route::put('/director/pr/rejectSelected', [DirectorPurchaseRequestController::class, 'rejectSelected'])
            ->name('director.pr.rejectSelected')
            ->middleware('permission:reject-selected-director');
    });

    Route::middleware(['checkDepartment:PE,PPIC'])->group(function () {
        Route::get('pe/home', [PEHomeController::class, 'index'])->name('pe');

        Route::get('/pe/trialinput', [PEController::class, 'trialinput'])->name('pe.trial');
        Route::post('/pe/trialfinish', [PEController::class, 'input'])->name('pe.input');
        Route::get('/pe/listformrequest', [PEController::class, 'view'])
            ->name('pe.formlist')
            ->middleware('permission:get-pe-form-list');
        Route::get('/pe/listformrequest/detail/{id}', [PEController::class, 'detail'])->name('trial.detail');
        Route::post('/pe/listformrequest/detai/updateTonage/{id}', [PEController::class, 'updateTonage'])->name('update.tonage');
    });

    Route::middleware(['checkDepartment:PURCHASING'])->group(function () {
        Route::get('/purchasing', [PurchasingController::class, 'index'])->name('purchasing');

        Route::get('/store-data', [PurchasingMaterialController::class, 'storeDataInNewTable'])->name('construct_data');
        Route::get('/insert-material_prediction', [materialPredictionController::class, 'processForemindFinalData'])->name('material_prediction');
        Route::get('/foremind-detail', [PurchasingController::class, 'indexhome'])->name('purchasing_home');
        Route::get('/foremind-detail/print', [PurchasingDetailController::class, 'index']);
        Route::get('/foremind-detail/printCustomer', [PurchasingDetailController::class, 'indexcustomer']);

        Route::get('/foremind-detail/print/excel/{vendor_code}', [PurchasingDetailController::class, 'exportExcel']);
        Route::get('/foremind-detail/print/customer/excel/{vendor_code}', [PurchasingDetailController::class, 'exportExcelcustomer']);

        Route::get('purchasing/reminder', [PurchasingReminderController::class, 'index'])->name('reminderindex');
        Route::get('purchasing/reminder/detail', [PurchasingReminderController::class, 'detail'])->name('reminderdetail');

        Route::get('purchasing/requirement', [PurchasingRequirementController::class, 'index'])->name('purchasingrequirement.index');
        Route::get('purchasing/requirement/detail', [PurchasingRequirementController::class, 'detail'])->name('purchasingrequirement.detail');
    });

    Route::middleware(['checkDepartment:BUSINESS,PPIC,PURCHASING'])->group(function () {
        Route::get('deliveryschedule/index', [DeliveryScheduleController::class, 'index'])
            ->name('indexds')
            ->middleware('permission:get-delivery-schedule-index');

        Route::get('deliveryschedule/raw', [DeliveryScheduleController::class, 'indexraw'])->name('rawdelsched');
        Route::get('deliveryschedule/wip', [DeliveryScheduleController::class, 'indexfinal'])->name('indexfinalwip');

        Route::get('delsched/start1', [DeliveryScheduleController::class, 'step1'])->name('deslsched.step1');
        Route::get('delsched/start2', [DeliveryScheduleController::class, 'step2'])->name('deslsched.step2');
        Route::get('delsched/start3', [DeliveryScheduleController::class, 'step3'])->name('deslsched.step3');
        Route::get('delsched/start4', [DeliveryScheduleController::class, 'step4'])->name('deslsched.step4');

        Route::get('delsched/wip/step1', [DeliveryScheduleController::class, 'step1wip'])->name('delschedwip.step1');
        Route::get('delsched/wip/step2', [DeliveryScheduleController::class, 'step2wip'])->name('delschedwip.step2');
    });

    Route::middleware(['checkDepartment:ACCOUNTING'])->group(function () {
        Route::get('accounting/purchase-requests/', [AccountingPurchaseRequestController::class, 'index'])->name('accounting.purchase-request');
    });

    Route::middleware(['checkDepartment:PRODUCTION,PPIC'])->group(function () {

        Route::get('/production/capacity-forecast', [CapacityByForecastController::class, 'index'])->name('capacityforecastindex');
        Route::get('/production/capacity-line', [CapacityByForecastController::class, 'line'])->name('capacityforecastline');
        Route::get('/production/capacity-distribution', [CapacityByForecastController::class, 'distribution'])->name('capacityforecastdistribution');
        Route::get('/production/capacity-detail', [CapacityByForecastController::class, 'detail'])->name('capacityforecastdetail');

        Route::get('/production/capacity-forecast/view-step', [CapacityByForecastController::class, 'viewstep1'])->name('viewstep1');
        Route::get('/production/capacity-forecast/step1', [CapacityByForecastController::class, 'step1'])->name('step1');
        Route::get('/production/capacity-forecast/step1second', [CapacityByForecastController::class, 'step1_second'])->name('step1second');

        Route::get('/production/capacity-forecast/step2', [CapacityByForecastController::class, 'step2'])->name('step2');
        Route::get('/production/capacity-forecast/step2logic', [CapacityByForecastController::class, 'step2logic'])->name('step2logic');

        Route::get('/production/capacity-forecast/step3', [CapacityByForecastController::class, 'step3'])->name('step3');
        Route::get('/production/capacity-forecast/step3logic', [CapacityByForecastController::class, 'step3logic'])->name('step3logic');
        Route::get('/production/capacity-forecast/step3last', [CapacityByForecastController::class, 'step3logiclast'])->name('step3logiclast');

        Route::get('/pps/index', [PPSGeneralController::class, 'index'])
            ->name('indexpps')
            ->middleware('permission:get-pps-index');
        Route::get('/pps/menu', [PPSGeneralController::class, 'menu'])->name('menupps');
        Route::post('/pps/portal', [PPSGeneralController::class, 'portal'])->name('portal');

        // KarawangRoute
        Route::get('/pps/karawang', [PPSKarawangController::class, 'index'])->name('indexkarawang');
        Route::post('/pps/process-karawang-form', [PPSKarawangController::class, 'processKarawangForm'])->name('processKarawangForm');
        Route::get('pps/karawang/process1', [PPSKarawangController::class, 'process1'])->name('karawangprocess1');
        Route::get('pps/karawang/process2', [PPSKarawangController::class, 'process2'])->name('karawangprocess2');
        Route::get('pps/karawang/process3', [PPSKarawangController::class, 'process3'])->name('karawangprocess3');

        Route::get('/pps/karawang/delivery', [PPSKarawangController::class, 'karawanginjection'])->name('karawanginjection');
        Route::get('pps/karawang/process4', [PPSKarawangController::class, 'process4'])->name('karawangprocess4');
        Route::get('pps/karawang/process5', [PPSKarawangController::class, 'process5'])->name('karawangprocess5');
        Route::get('pps/karawang/process6', [PPSKarawangController::class, 'process6'])->name('karawangprocess6');

        Route::get('/pps/karawang/items', [PPSKarawangController::class, 'itemkarawang'])->name('itemkarawang');

        Route::get('/pps/karawang/line', [PPSKarawangController::class, 'linekarawang'])->name('linekarawang');

        Route::get('pps/karawanginjectionfinal', [PPSKarawangController::class, 'finalresultkarawanginjection'])->name('finalkarawanginjectionpps');

        Route::get('/pps/injection/start', [PPSInjectionController::class, 'indexscenario'])->name('indexinjection');
        Route::post('/pps/process-injection-form', [PPSInjectionController::class, 'processInjectionForm'])->name('processInjectionForm');
        Route::get('pps/injection/process1', [PPSInjectionController::class, 'process1'])->name('injectionprocess1');
        Route::get('pps/injection/process2', [PPSInjectionController::class, 'process2'])->name('injectionprocess2');
        Route::get('pps/injection/process3', [PPSInjectionController::class, 'process3'])->name('injectionprocess3');

        Route::get('/pps/injection/delivery', [PPSInjectionController::class, 'deliveryinjection'])->name('deliveryinjection');
        Route::get('pps/injection/process4', [PPSInjectionController::class, 'process4'])->name('injectionprocess4');
        Route::get('pps/injection/process5', [PPSInjectionController::class, 'process5'])->name('injectionprocess5');
        Route::get('pps/injection/process6', [PPSInjectionController::class, 'process6'])->name('injectionprocess6');
        // jika ada post untuk delivery

        Route::get('/pps/injection/items', [PPSInjectionController::class, 'iteminjection'])->name('iteminjection');
        // jika ada post untuk items

        Route::get('/pps/injection/line', [PPSInjectionController::class, 'lineinjection'])->name('lineinjection');
        // jika ada post untuk line

        Route::get('pps/injectionfinal', [PPSInjectionController::class, 'finalresultinjection'])->name('finalinjectionpps');

        Route::get('/pps/second/start', [PPSSecondController::class, 'indexscenario'])->name('indexsecond');
        Route::post('/pps/second-process-form', [PPSSecondController::class, 'processSecondForm'])->name('processSecondForm');
        Route::get('pps/second/process1', [PPSSecondController::class, 'process1'])->name('secondprocess1');
        Route::get('pps/second/process2', [PPSSecondController::class, 'process2'])->name('secondprocess2');
        Route::get('pps/second/process3', [PPSSecondController::class, 'process3'])->name('secondprocess3');
        // jika ada post untuk start

        Route::get('/pps/second/delivery', [PPSSecondController::class, 'deliverysecond'])->name('deliverysecond');
        Route::get('pps/second/process4', [PPSSecondController::class, 'process4'])->name('secondprocess4');
        Route::get('pps/second/process5', [PPSSecondController::class, 'process5'])->name('secondprocess5');
        Route::get('pps/second/process6', [PPSSecondController::class, 'process6'])->name('secondprocess6');
        // jika ada post untuk delivery

        Route::get('/pps/second/items', [PPSSecondController::class, 'itemsecond'])->name('itemsecond');
        // jika ada post untuk items

        Route::get('/pps/second/line', [PPSSecondController::class, 'linesecond'])->name('linesecond');
        // jika ada post untuk line

        Route::get('pps/secondfinal', [PPSSecondController::class, 'finalresultsecond'])->name('finalsecondpps');

        Route::get('/pps/assembly/start', [PPSAssemblyController::class, 'indexscenario'])->name('indexassembly');
        Route::post('/pps/assembly-process-form', [PPSAssemblyController::class, 'processAssemblyForm'])->name('processAssemblyForm');
        Route::get('pps/assembly/process1', [PPSAssemblyController::class, 'process1'])->name('assemblyprocess1');
        Route::get('pps/assembly/process2', [PPSAssemblyController::class, 'process2'])->name('assemblyprocess2');
        Route::get('pps/assembly/process3', [PPSAssemblyController::class, 'process3'])->name('assemblyprocess3');
        // jika ada post untuk start

        Route::get('/pps/assembly/delivery', [PPSAssemblyController::class, 'deliveryassembly'])->name('deliveryassembly');
        Route::get('pps/assembly/process4', [PPSAssemblyController::class, 'process4'])->name('assemblyprocess4');
        Route::get('pps/assembly/process5', [PPSAssemblyController::class, 'process5'])->name('assemblyprocess5');
        Route::get('pps/assembly/process6', [PPSAssemblyController::class, 'process6'])->name('assemblyprocess6');
        // jika ada post untuk delivery

        Route::get('/pps/assembly/items', [PPSAssemblyController::class, 'itemassembly'])->name('itemassembly');
        // jika ada post untuk items

        Route::get('/pps/assembly/line', [PPSAssemblyController::class, 'lineassembly'])->name('lineassembly');
        // jika ada post untuk line

        Route::get('pps/assembly', [PPSAssemblyController::class, 'finalresultassembly'])->name('finalresultassembly');
    });

    Route::middleware(['checkDepartment:MAINTENANCE,PPIC'])->group(function () {

        Route::get('maintenance/mould-repair', [MouldDownController::class, 'index'])
            ->name('moulddown.index')
            ->middleware('permission:get-mould-down-index');
        Route::post('/add/mould', [MouldDownController::class, 'addmould'])->name('addmould');
        Route::get('maintenance/line-repair', [LineDownController::class, 'index'])->name('linedown.index');
        Route::post('/add/line/down', [LineDownController::class, 'addlinedown'])->name('addlinedown');
    });
});

Route::middleware(['checkUserRole:3'])->group(function () {
    Route::get('/user/home', [UserHomeController::class, 'index'])->name('user');
});

Route::middleware(['checkUserRole:1,2', 'checkSessionId'])->group(function () {
    Route::post('file/upload', [FileController::class, 'upload'])->name('file.upload');
    Route::post('file/uploadEvaluation', [FileController::class, 'uploadEvaluation'])->name('file.upload.evaluation');

    Route::get('/get-files', [FileController::class, 'getFiles']);

    Route::delete('file/{id}/delete', [FileController::class, 'destroy'])->name('file.delete');

    // PR
    Route::get('/purchaseRequest', [PurchaseRequestController::class, 'index'])
        ->name('purchaserequest')
        ->middleware('permission:get-purchase-requests');
    Route::get('/purchaseRequest/create', [PurchaseRequestController::class, 'create'])
        ->name('purchaserequest.create')
        ->middleware('permission:create-purchase-request');
    Route::post('/purchaseRequest/insert', [PurchaseRequestController::class, 'insert'])
        ->name('purchaserequest.insert')
        ->middleware('permission:store-purchase-request');
    Route::get('/purchaserequest/detail/{id}', [PurchaseRequestController::class, 'detail'])
        ->name('purchaserequest.detail')
        ->middleware('permission:detail-purchase-request');
    Route::get('/purchaserequest/reject/{id}', [PurchaseRequestController::class, 'reject'])
        ->name('purchaserequest.reject')
        ->middleware('permission:reject-purchase-request');
    Route::put('/purchaserequest/{id}/update', [PurchaseRequestController::class, 'update'])
        ->name('purchaserequest.update')
        ->middleware('permission:update-purchase-request');
    Route::delete('/purchaserequest/{id}/delete', [PurchaseRequestController::class, 'destroy'])
        ->name('purchaserequest.delete')
        ->middleware('permission:delete-purchase-request');
    Route::put('purchaserequest/{id}/cancel', [PurchaseRequestController::class, 'cancel'])
        ->name('purchaserequest.cancel')
        ->middleware('permission:cancel-purchase-request');
    Route::put('purchaserequest/{id}/ponum', [PurchaseRequestController::class, 'updatePoNumber'])->name('purchaserequest.update.ponumber');

    // PR MONTHLY
    Route::get('/purchaserequest/monthly-list', [PurchaseRequestController::class, 'monthlyprlist'])->name('purchaserequest.monthlyprlist');
    Route::get('/purchaserequest/monthly-detail/{id}', [PurchaseRequestController::class, 'monthlydetail'])->name('purchaserequest.monthlydetail');
    Route::post('/save-signature-path-monthlydetail/{monthprId}/{section}', [PurchaseRequestController::class, 'saveImagePathMonthly']);
    Route::get('/purchaserequest/monthlypr', [PurchaseRequestController::class, 'monthlyview'])->name('purchaserequest.monthly');
    Route::get('/purchaserequest/month-selected', [PurchaseRequestController::class, 'monthlyviewmonth'])->name('purchaserequest.monthlyselected');
    Route::post('/save-signature-path/{prId}/{section}', [PurchaseRequestController::class, 'saveImagePath']);
    // Route::get('/purchase-request/chart-data/{year}/{month}', [PurchaseRequestController::class, 'getChartData']);
    Route::get('approveAllDetailItems/{prId}/{type}', [PurchaseRequestController::class, 'approveAllDetailItems']);

    Route::get('/purchaserequest/detail/{id}/approve', [DetailPurchaseRequestController::class, 'approve'])->name('purchaserequest.detail.approve');
    Route::get('/purchaserequest/detail/{id}/reject', [DetailPurchaseRequestController::class, 'reject'])->name('purchaserequest.detail.reject');
    Route::post('/purchaserequest/detail/update', [DetailPurchaseRequestController::class, 'update'])->name('purchaserequest.detail.update');
    // REVISI PR PENAMBAHAN DROPDOWN ITEM & PRICE
    Route::get('/get-item-names', [PurchaseRequestController::class, 'getItemNames']);

    Route::post('/purchaseRequest/detail/{id}/updateReceivedQuantity', [DetailPurchaseRequestController::class, 'updateReceivedQuantity'])->name('purchaserequest.update.receivedQuantity');
    Route::get('/purchaseRequest/detail/{id}/updateAllReceivedQuantity', [DetailPurchaseRequestController::class, 'updateAllReceivedQuantity'])->name('purchaserequest.update.allReceivedQuantity');

    Route::get('/purchaseRequest/{id}/exportToPdf', [PurchaseRequestController::class, 'exportToPdf'])->name('purchaserequest.exportToPdf');
    Route::get('/purchaseRequest/exportExcel', [PurchaseRequestController::class, 'exportExcel'])->name('purchaserequest.export.excel');

    // FORM CUTI
    Route::get('/form-cuti', [FormCutiController::class, 'index'])
        ->name('formcuti')
        ->middleware('permission:get-form-cuti');
    Route::get('/form-cuti/create', [FormCutiController::class, 'create'])
        ->name('formcuti.create')
        ->middleware('permission:create-form-cuti');
    Route::post('/form-cuti/insert', [FormCutiController::class, 'store'])
        ->name('formcuti.insert')
        ->middleware('permission:store-form-cuti');
    Route::get('/form-cuti/detail/{id}', [FormCutiController::class, 'detail'])
        ->name('formcuti.detail')
        ->middleware('permission:detail-form-cuti');
    Route::post('/form-cuti/save-autograph-path/{formId}/{section}', [FormCutiController::class, 'saveImagePath']);

    // FORM KELUAR
    Route::get('/form-keluar', [FormKeluarController::class, 'index'])
        ->name('formkeluar')
        ->middleware('permission:get-form-keluar');
    Route::get('/form-keluar/create', [FormKeluarController::class, 'create'])
        ->name('formkeluar.create')
        ->middleware('permission:create-form-keluar');
    Route::post('/form-keluar/insert', [FormKeluarController::class, 'store'])
        ->name('formkeluar.insert')
        ->middleware('permission:store-form-keluar');
    Route::get('/form-keluar/detail/{id}', [FormKeluarController::class, 'detail'])
        ->name('formkeluar.detail')
        ->middleware('permission:detail-form-keluar');
    Route::post('/save-autosignature-path/{formId}/{section}', [FormKeluarController::class, 'saveImagePath']);

    Route::get('/inventory/fg', [InventoryFgController::class, 'index'])
        ->name('inventoryfg')
        ->middleware('permission:get-inventory-fg');
    Route::get('/inventory/mtr', [InventoryMtrController::class, 'index'])
        ->name('inventorymtr')
        ->middleware('permission:get-inventory-mtr');

    Route::get('/inventory/line-list', [InvLineListController::class, 'index'])
        ->name('invlinelist')
        ->middleware('permission:get-inventory-line-list');
    Route::post('/add/line', [InvLineListController::class, 'addline'])->name('addline');
    Route::put('/edit/line/{id}', [InvLineListController::class, 'editline'])->name('editline');
    Route::delete('/delete/line/{linecode}', [InvLineListController::class, 'deleteline'])->name('deleteline');

    // Holiday list feature
    Route::get('setting/holiday-list', [HolidayListController::class, 'index'])
        ->name('indexholiday')
        ->middleware('permission:get-holiday-list-index');
    Route::get('setting/holiday-list/create', [HolidayListController::class, 'create'])
        ->name('createholiday')
        ->middleware('permission:create-holiday-list');
    Route::post('setting/input/holidays', [HolidayListController::class, 'store'])
        ->name('holidays.store')
        ->middleware('permission:store-holiday-list');
    Route::get('/download-holiday-list-template', [HolidayListController::class, 'downloadTemplate'])
        ->name('download.holiday.template')
        ->middleware('permission:download-holiday-list-template');
    Route::post('/upload-holiday-list-template', [HolidayListController::class, 'uploadTemplate'])
        ->name('upload.holiday.template')
        ->middleware('permission:upload-holiday-list-template');
    Route::delete('/holiday/{id}/delete', [HolidayListController::class, 'delete'])
        ->name('holiday.delete')
        ->middleware('permission:delete-holiday-list');
    Route::put('/holiday/{id}/update', [HolidayListController::class, 'update'])
        ->name('holiday.update')
        ->middleware('permission:update-holiday-list');

    Route::get('projecttracker/index', [ProjectTrackerController::class, 'index'])
        ->name('pt.index')
        ->middleware('permission:get-project-tracker-index');
    Route::get('projecttracker/create', [ProjectTrackerController::class, 'create'])->name('pt.create');
    Route::post('projecttracker/post', [ProjectTrackerController::class, 'store'])->name('pt.store');
    Route::get('projecttracker/detail/{id}', [ProjectTrackerController::class, 'detail'])->name('pt.detail');
    Route::put('projecttracker/{id}/update-ongoing', [ProjectTrackerController::class, 'updateOngoing'])->name('pt.updateongoing');
    Route::put('projecttracker/{id}/update-test', [ProjectTrackerController::class, 'updateTest'])->name('pt.updatetest');
    Route::put('projecttracker/{id}/update-revision', [ProjectTrackerController::class, 'updateRevision'])->name('pt.updaterevision');
    Route::put('projecttracker/{id}/accept', [ProjectTrackerController::class, 'updateAccept'])->name('pt.updateaccept');

    Route::get('updatepage/index', [UpdateDailyController::class, 'index'])->name('indexupdatepage');
    Route::post('/processdailydata', [UpdateDailyController::class, 'update'])->name('updatedata');

    Route::get('/employeemaster/index', [EmployeeMasterController::class, 'index'])
        ->name('index.employeesmaster')
        ->middleware('permission:get-employee-master-index');
    Route::post('/employeemaster/add', [EmployeeMasterController::class, 'addemployee'])->name('addemployee');
    Route::put('/edit/employee/{id}', [EmployeeMasterController::class, 'editemployee'])->name('editemployee');
    Route::delete('/delete/employee/{linecode}', [EmployeeMasterController::class, 'deleteemployee'])->name('deleteemployee');
    Route::get('/import-annual-leave-quota', [EmployeeMasterController::class, 'showImportForm'])->name('import.annual-leave-quota.form');
    Route::post('/import-annual-leave-quota', [EmployeeMasterController::class, 'importAnnualLeaveQuota'])->name('import.annual-leave-quota');

    Route::get('/evaluation/index', [EvaluationDataController::class, 'index'])
        ->name('evaluation.index')
        ->middleware('permission:get-evaluation-index');
    Route::post('/processevaluationdata', [EvaluationDataController::class, 'update'])->name('UpdateEvaluation');

    Route::get('/weekly-evaluation/index', [EvaluationDataController::class, 'weeklyIndex'])
        ->name('weekly.evaluation.index')
        ->middleware('permission:get-evaluation-index');
    Route::post('/weeklyprocessevaluationdata', [EvaluationDataController::class, 'updateWeekly'])->name('WeeklyUpdateEvaluation');

    Route::delete('/delete-evaluation', [EvaluationDataController::class, 'delete'])->name('DeleteEvaluation');

    Route::get('/format-evaluation-year-yayasan', [EvaluationDataController::class, 'evaluationformatrequestpageYayasan'])->name('format.evaluation.year.yayasan');
    Route::get('/format-evaluation-year-allin', [EvaluationDataController::class, 'evaluationformatrequestpageAllin'])->name('format.evaluation.year.allin');
    Route::get('/format-evaluation-year-magang', [EvaluationDataController::class, 'evaluationformatrequestpageMagang'])->name('format.evaluation.year.magang');
    Route::post('/getformatyayasan', [EvaluationDataController::class, 'getFormatYearyayasan'])->name('get.format');
    Route::post('/getformatallin', [EvaluationDataController::class, 'getFormatYearallin'])->name('get.format.allin');
    Route::post('/getformatmagang', [EvaluationDataController::class, 'getFormatYearmagang'])->name('get.format.magang');
    Route::get('/single/eval', [EvaluationDataController::class, 'allEmployees'])->name('single.employee');

    Route::get('/discipline/indexall', [DisciplinePageController::class, 'allindex'])->name('alldiscipline.index');
    Route::get('/discipline/indexallyayasan', [DisciplinePageController::class, 'yayasanallindex'])->name('allyayasandiscipline.index');
    Route::get('/discipline/index', [DisciplinePageController::class, 'index'])
        ->name('discipline.index')
        ->middleware('permission:get-discipline-index');
    Route::get('/firstimeexport/yayasan/discipline', [DisciplinePageController::class, 'exportYayasan'])->name('export.yayasan.first.time');
    Route::get('/export/yayasan-full/discipline', [DisciplinePageController::class, 'exportYayasanFull'])->name('export.yayasan.full');

    Route::post('/lock-data/discipline', [DisciplinePageController::class, 'lockdata'])->name('lock.data');

    Route::post('/approve-data-yayasan/depthead', [DisciplinePageController::class, 'approve_depthead'])->name('approve.data.depthead');

    Route::post('/reject-data-yayasan/depthead', [DisciplinePageController::class, 'reject_depthead_button'])->name('reject.depthead.yayasan');
    Route::post('/reject-data-yayasan/hrd', [DisciplinePageController::class, 'reject_hrd_button'])->name('reject.hrd.yayasan');

    Route::post('/approve-data-depthead/yayasan', [DisciplinePageController::class, 'approve_depthead_button'])->name('approve.depthead.yayasan');
    Route::post('/approve-data-hrd/yayasan', [DisciplinePageController::class, 'approve_hrd_button'])->name('approve.hrd.yayasan');

    Route::post('/approve-data-yayasan/gm', [DisciplinePageController::class, 'approve_gm'])->name('approve.data.gm');

    Route::post('/set-filter-value', [DisciplinePageController::class, 'setFilterValue']);
    Route::get('/get-filter-value', [DisciplinePageController::class, 'getFilterValue']);
    Route::put('/edit/discipline/{id}', [DisciplinePageController::class, 'update'])->name('editdiscipline');
    Route::post('/updatediscipline', [DisciplinePageController::class, 'import'])->name('discipline.import');
    Route::get('/disciplineupdate/step1', [DisciplinePageController::class, 'step1'])->name('update.point');
    Route::get('/disciplineupdate/step2', [DisciplinePageController::class, 'step2'])->name('update.excel');

    Route::get('/updatedept', [DisciplinePageController::class, 'updateDept'])->name('update.dept');

    Route::get('/exportyayasanexcel', [DisciplinePageController::class, 'exportYayasan'])->name('exportyayasan');

    Route::get('/fetch/filtered/employees', [DisciplinePageController::class, 'fetchFilteredEmployees'])->name('fetch.filtered.employees');
    Route::get('/fetch/filtered/yayasan-employees', [DisciplinePageController::class, 'fetchFilteredYayasanEmployees'])->name('fetch.filtered.yayasan.employees');

    Route::get('/fetch/filtered/yayasan-employees-GM', [DisciplinePageController::class, 'fetchFilteredEmployeesGM']);

    Route::get('/yayasan/disciplineindex', [DisciplinePageController::class, 'indexyayasan'])->name('yayasan.table');

    Route::post('/department-status-yayasan', [DisciplinePageController::class, 'getDepartmentStatusYayasan'])->name('department.status.yayasan');

    Route::put('/edit/magangdiscipline/{id}', [DisciplinePageController::class, 'updatemagang'])->name('updatemagang');
    Route::put('/edit/yayasandiscipline/{id}', [DisciplinePageController::class, 'updateyayasan'])->name('updateyayasan');
    Route::post('/updateyayasandata', [DisciplinePageController::class, 'importyayasan'])->name('yayasan.import');
    Route::post('/updatemagangdata', [DisciplinePageController::class, 'magangimport'])->name('magang.import');

    Route::get('/evaluationDatas/{id}', [DisciplinePageController::class, 'getEvaluationData']);

    Route::get('/unlock/data', [DisciplinePageController::class, 'unlockdata']);

    Route::get('/magang/disciplineindex', [DisciplinePageController::class, 'indexmagang'])->name('magang.table');

    Route::get('/exportyayasandateinput', [DisciplinePageController::class, 'dateExport'])->name('exportyayasan.dateinput');
    Route::get('/exportyayasansummary', [DisciplinePageController::class, 'exportYayasanJpayroll'])->name('exportyayasan.summary');
    Route::get('/export/yayasan/discipline', [DisciplinePageController::class, 'exportYayasanJpayrollFunction'])->name('export.yayasan.jpayroll');

    Route::get('/forecastcustomermaster', [ForecastCustomerController::class, 'index'])
        ->name('fc.index')
        ->middleware('permission:get-forecast-customer-index');
    Route::post('/add/forecastmaster', [ForecastCustomerController::class, 'addnewmaster'])->name('addnewforecastmaster');

    Route::get('/overtime-forms', FormOvertimeIndex::class)->name('overtime.index');
    Route::get('/overtime-forms/create', FormOvertimeCreate::class)->name('overtime.create');
    Route::post('/formovertime/insert', [FormOvertimeController::class, 'insert'])->name('formovertime.insert');
    Route::get('/formovertime/detail/{id}', [FormOvertimeController::class, 'detail'])->name('formovertime.detail');
    Route::delete('formovertime/{id}', [FormOvertimeController::class, 'destroy'])->name('formovertime.delete');
    Route::post('/save-autographot-path/{reportId}/{section}', [FormOvertimeController::class, 'saveAutographOtPath']);
    Route::get('/formovertime/edit', [FormOvertimeController::class, 'edit'])->name('formovertime.edit');
    Route::put('/formovertime/{id}/update', [FormOvertimeController::class, 'update'])->name('formovertime.update');
    Route::delete('/formovertime/{id}/delete', [FormOvertimeController::class, 'destroyDetail'])->name('formovertime.destroyDetail');
    Route::get('export-overtime/{headerId}', [FormOvertimeController::class, 'exportOvertime'])->name('export.overtime');
    Route::get('/formovertime/template/download', [FormOvertimeController::class, 'downloadTemplate'])->name('formovertime.template.download');
    Route::put('/overtime/reject/{id}', [FormOvertimeController::class, 'reject'])->name('overtime.reject');
    Route::post('/overtime/sign/{id}', [FormOvertimeController::class, 'sign'])->name('overtime.sign');

    Route::delete('/overtime-detail/{id}/reject-server-side', [FormOvertimeController::class, 'rejectDetailServerSide'])->name('overtime-detail.reject-server-side');

    Route::get('/overtime/summary', [FormOvertimeController::class, 'summaryView'])->name('overtime.summary');
    Route::get('/overtime/summary/export', [FormOvertimeController::class, 'exportSummaryExcel'])->name('overtime.summary.export');

    Route::get('/actual-overtime/import', [FormOvertimeController::class, 'showForm'])->name('actual.import.form');
    Route::post('/actual-overtime/import', [FormOvertimeController::class, 'import'])->name('actual.import');

    Route::get('/get-employees', [FormOvertimeController::class, 'getEmployees']);

    Route::get('/push-overtime-detail/{detailId}', [FormOvertimeController::class, 'pushSingleDetailToJPayroll']);
    Route::post('/overtime/push-all/{headerId}', [FormOvertimeController::class, 'pushAllDetailsToJPayroll']);

    Route::get('/stock-tinta-index', [StockTintaController::class, 'index'])->name('stocktinta');

    Route::get('/statusfinish', [DeliveryScheduleController::class, 'statusFinish']);

    Route::get('/update-dept', [DisciplinePageController::class, 'updateDeptColumn']);

    Route::prefix('monthly-budget-summaries')->group(function () {
        Route::get('/', MonthlyBudgetSummaryIndex::class)->name('monthly-budget-summary-report.index');
        Route::get('/{id}', [MonthlyBudgetSummaryReportController::class, 'show'])->name('monthly.budget.summary.report.show');
        Route::post('/', [MonthlyBudgetSummaryReportController::class, 'store'])->name('monthly.budget.summary.report.store');
        Route::delete('/id}', [MonthlyBudgetSummaryReportController::class, 'destroy'])->name('monthly.budget.summary.report.delete');
        Route::put('/save-autograph/{id}', [MonthlyBudgetSummaryReportController::class, 'saveAutograph'])->name('monthly.budget.summary.save.autograph');
        Route::put('/{id}/reject', [MonthlyBudgetSummaryReportController::class, 'reject'])->name('monthly.budget.summary.report.reject');
        Route::put('/{id}/cancel', [MonthlyBudgetSummaryReportController::class, 'cancel'])->name('monthly.budget.summary.report.cancel');
        Route::post('/{id}/refresh', [MonthlyBudgetSummaryReportController::class, 'refresh'])->name('monthly-budget-summary.refresh');
    });

    Route::put('monthlyBudgetReportSummaryDetail/{id}', [MonthlyBudgetReportSummaryDetailController::class, 'update'])->name('monthly.budget.report.summary.detail.update');
    Route::delete('monthlyBudgetReportSummaryDetail/{id}', [MonthlyBudgetReportSummaryDetailController::class, 'destroy'])->name('monthly.budget.report.summary.detail.destroy');

    Route::get('monthlyBudgetReports', [MonthlyBudgetReportController::class, 'index'])->name('monthly.budget.report.index');
    Route::get('monthlyBudgetReport/create', [MonthlyBudgetReportController::class, 'create'])->name('monthly.budget.report.create');
    Route::get('monthlyBudgetReport/{id}/edit', [MonthlyBudgetReportController::class, 'edit'])->name('monthly.budget.report.edit');
    Route::put('monthlyBudgetReport/{id}', [MonthlyBudgetReportController::class, 'update'])->name('monthly.budget.report.update');
    Route::post('monthlyBudgetReports', [MonthlyBudgetReportController::class, 'store'])->name('monthly.budget.report.store');
    Route::get('monthlyBudgetReport/{id}', [MonthlyBudgetReportController::class, 'show'])->name('monthly.budget.report.show');
    Route::delete('monthlyBudgetReport/{id}', [MonthlyBudgetReportController::class, 'destroy'])->name('monthly.budget.report.delete');
    Route::put('monthlyBudgetReport/{id}/reject', [MonthlyBudgetReportController::class, 'reject'])->name('monthly.budget.report.reject');
    Route::put('monthlyBudgetReport/{id}/cancel', [MonthlyBudgetReportController::class, 'cancel'])->name('monthly.budget.report.cancel');
    Route::put('monthlyBudgetReport/save-autograph/{id}', [MonthlyBudgetReportController::class, 'saveAutograph'])->name('monthly.budget.save.autograph');
    Route::post('/download-monthly-excel-template', [MonthlyBudgetReportController::class, 'downloadExcelTemplate'])->name('monthly.budget.download.excel.template');

    Route::post('monthlyBudgetReportDetail', [MonthlyBudgetReportDetailController::class, 'store'])->name('monthly.budget.report.detail.store');
    Route::put('monthlyBudgetReportDetail/{id}', [MonthlyBudgetReportDetailController::class, 'update'])->name('monthly.budget.report.detail.update');
    Route::delete('monthlyBudgetReportDetail/{id}', [MonthlyBudgetReportDetailController::class, 'destroy'])->name('monthly.budget.report.detail.delete');

    Route::get('barcode/index', [BarcodeController::class, 'index'])->name('barcode.base.index');
    Route::get('barcode/inandout/index', [BarcodeController::class, 'inandoutpage'])->name('inandout.index');
    Route::get('barcode/missing/index', [BarcodeController::class, 'missingbarcodeindex'])->name('missingbarcode.index');
    Route::post('barcode/missing/generate', [BarcodeController::class, 'missingbarcodegenerator'])->name('generateBarcodeMissing');

    Route::post('barcode/process/save', [BarcodeController::class, 'processInAndOut'])->name('process.in.and.out');

    Route::post('process/inandoutbarcode', [BarcodeController::class, 'storeInAndOut'])->name('processbarcodeinandout');
    Route::get('indexbarcode', [BarcodeController::class, 'indexBarcode'])->name('barcodeindex');
    Route::post('/generate-barcode', [BarcodeController::class, 'generateBarcode'])->name('generateBarcode');

    Route::get('barcode/list', [BarcodeController::class, 'barcodelist'])->name('list.barcode');

    Route::get('barcode/latest/item', [BarcodeController::class, 'latestitemdetails'])->name('updated.barcode.item.position');

    Route::get('barcode/historytable', [BarcodeController::class, 'historybarcodelist'])->name('barcode.historytable');

    Route::get('mastertinta/index', [MasterTintaController::class, 'index'])->name('mastertinta.index');

    Route::get('request/index', [MasterTintaController::class, 'requestpageindex'])->name('testing.request');

    Route::get('mastertinta/transaction/list', [MasterTintaController::class, 'listtransaction'])->name('transaction.list');

    Route::post('/mastertinta/request/process', [MasterTintaController::class, 'requeststore'])->name('stockrequest.store');

    Route::get('mastertinta/transaction/index', [MasterTintaController::class, 'transactiontintaview'])->name('mastertinta.transaction.index');
    Route::post('mastertinta/transaction/process', [MasterTintaController::class, 'storetransaction'])->name('mastertinta.process');
    Route::get('/masterstock/get-items/{masterStockId}', [MasterTintaController::class, 'getItems']);

    Route::get('/stock/get-available-quantity/{stock_id}/{department_id}', [MasterTintaController::class, 'getAvailableQuantity']);

    Route::get('/barcode/filter', [BarcodeController::class, 'filter'])->name('barcode.filter');
    Route::get('barcode/latest/item', [BarcodeController::class, 'latestitemdetails'])->name('updated.barcode.item.position');
    Route::get('barcode/stockall/{location?}', [BarcodeController::class, 'stockall'])->name('stockallbarcode');

    Route::get('/spk', [SuratPerintahKerjaController::class, 'index'])->name('spk.index');
    Route::get('/spk/create', [SuratPerintahKerjaController::class, 'createpage'])->name('spk.create');
    Route::post('/spk/input', [SuratPerintahKerjaController::class, 'inputprocess'])->name('spk.input');
    Route::get('/spk/{id}', [SuratPerintahKerjaController::class, 'detail'])->name('spk.detail');
    Route::put('/spk/{id}', [SuratPerintahKerjaController::class, 'update'])->name('spk.update');
    Route::delete('/spk/{id}', [SuratPerintahKerjaController::class, 'destroy'])->name('spk.delete');
    Route::get('/spk/report/monthly', [SuratPerintahKerjaController::class, 'monthlyreport'])->name('spk.monthlyreport');
    Route::put('/spk/save-autograph/{id}', [SuratPerintahKerjaController::class, 'saveAutograph'])->name('spk.save.autograph');
    Route::put('/spk/ask-a-revision/{id}', [SuratPerintahKerjaController::class, 'revision'])->name('spk.revision');
    Route::put('/spk/finish/{id}', [SuratPerintahKerjaController::class, 'finish'])->name('spk.finish');

    Route::get('deliveryschedule/averagemonth', [DeliveryScheduleController::class, 'averageschedule'])->name('delsched.averagemonth');
    Route::get('deliveryschedule/index', [DeliveryScheduleController::class, 'index'])
        ->name('indexds')
        ->middleware('permission:get-delivery-schedule-index');

    Route::get('masterinventory/index', [MasterInventoryController::class, 'index'])->name('masterinventory.index');
    Route::get('masterinventory/create', [MasterInventoryController::class, 'createpage'])->name('masterinventory.createpage');
    Route::post('masterinventory/store', [MasterInventoryController::class, 'store'])->name('masterinventory.store');
    Route::get('masterinventory/detail/{id}', [MasterInventoryController::class, 'detail'])->name('masterinventory.detail');
    Route::get('masterinventory/type', [MasterInventoryController::class, 'typeAdder'])->name('masterinventory.typeindex');
    Route::delete('/masterinventory/{id}', [MasterInventoryController::class, 'destroy'])->name('masterinventory.delete');

    Route::post('masterinventory/generate/qr/{id}', [MasterInventoryController::class, 'generateQr'])->name('generate.hardware.qrcode');

    // Route to handle adding new types
    Route::post('/add/hardware/type', [MasterInventoryController::class, 'addHardwareType'])->name('add.hardware.type');
    Route::post('/add/software/type', [MasterInventoryController::class, 'addSoftwareType'])->name('add.software.type');
    Route::delete('/delete/type', [MasterInventoryController::class, 'deleteType'])->name('delete.type');
    Route::get('/export-inventory', [MasterInventoryController::class, 'export'])->name('export.inventory');

    Route::get('masterinventory/{id}/edit', [MasterInventoryController::class, 'editpage'])->name('masterinventory.editpage');
    Route::put('masterinventory/{id}', [MasterInventoryController::class, 'update'])->name('masterinventory.update');
    Route::put('masterinventory/update/repairhistory/{id}', [MasterInventoryController::class, 'updateHistory'])->name('inventory.update');
    Route::post('masterinventory/repairs', [MasterInventoryController::class, 'CreateRepair'])->name('repair.store');
    Route::get('/items/types/{type}', [MasterInventoryController::class, 'getItems'])->name('items.get');
    Route::get('/items/available', [MasterInventoryController::class, 'getAvailableItems']);

    Route::get('maintenanceInventoryReports', [MaintenanceInventoryController::class, 'index'])->name('maintenance.inventory.index');
    Route::get('maintenanceInventoryReports/create/{id?}', [MaintenanceInventoryController::class, 'create'])->name('maintenance.inventory.create');
    Route::get('maintenanceInventoryReports/edit/{id}', [MaintenanceInventoryController::class, 'edit'])->name('maintenance.inventory.edit');
    Route::put('maintenanceInventoryReports/{id}', [MaintenanceInventoryController::class, 'update'])->name('maintenance.inventory.update');
    Route::post('maintenanceInventoryReports', [MaintenanceInventoryController::class, 'store'])->name('maintenance.inventory.store');
    Route::get('maintenanceInventoryReports/{id}', [MaintenanceInventoryController::class, 'show'])->name('maintenance.inventory.show');

    Route::get('formkerusakan/index', [FormKerusakanController::class, 'index'])->name('formkerusakan.index');
    Route::post('laporan-kerusakan/store', [FormKerusakanController::class, 'store'])->name('laporan-kerusakan.store');
    Route::get('laporan-kerusakan/report', [FormKerusakanController::class, 'report'])->name('laporan-kerusakan.report');
    Route::get('laporan-kerusakan/{id}', [FormKerusakanController::class, 'show'])->name('laporan-kerusakan.show');
    Route::delete('laporan-kerusakan-delete/{id}', [FormKerusakanController::class, 'destroy'])->name('laporan-kerusakan.destroy');

    Route::get('purc/evaluationsupplier/index', [PurchasingSupplierEvaluationController::class, 'index'])->name('purchasing.evaluationsupplier.index');
    Route::post('purc/evaluationsupplier/generate', [PurchasingSupplierEvaluationController::class, 'calculate'])->name('purchasing.evaluationsupplier.calculate');
    Route::get('purc/evaluationsupplier/details/{id}', [PurchasingSupplierEvaluationController::class, 'details'])->name('purchasing.evaluationsupplier.details');
    Route::get('purc/vendorclaim', [PurchasingSupplierEvaluationController::class, 'kriteria1'])->name('kriteria1');
    Route::get('purc/vendoraccuracygood', [PurchasingSupplierEvaluationController::class, 'kriteria2'])->name('kriteria2');
    Route::get('purc/vendorontimedelivery', [PurchasingSupplierEvaluationController::class, 'kriteria3'])->name('kriteria3');
    Route::get('purc/vendorurgentrequest', [PurchasingSupplierEvaluationController::class, 'kriteria4'])->name('kriteria4');
    Route::get('purc/vendorclaimresponse', [PurchasingSupplierEvaluationController::class, 'kriteria5'])->name('kriteria5');
    Route::get('purc/vendorlistcertificate', [PurchasingSupplierEvaluationController::class, 'kriteria6'])->name('kriteria6');

    Route::get('purchaseOrders', [PurchaseOrderController::class, 'index'])->name('po.index');
    Route::post('purchaseOrder/create', [PurchaseOrderController::class, 'create'])->name('po.create');
    Route::post('/purchaseOrder/store', [PurchaseOrderController::class, 'store'])->name('po.store');
    Route::get('/purchaseOrder/{id}', [PurchaseOrderController::class, 'view'])->name('po.view');
    Route::post('/purchaseOrder/sign', [PurchaseOrderController::class, 'sign'])->name('po.sign');
    Route::post('/purchaseOrder/reject-pdf', [PurchaseOrderController::class, 'rejectPDF'])->name('po.reject');
    Route::get('/download-pdf/{filename}', [PurchaseOrderController::class, 'downloadPDF'])->name('po.download');
    Route::delete('/purchaseOrder/{id}', [PurchaseOrderController::class, 'destroy'])->name('po.destroy');
    Route::post('/purchaseOrder/rejectAll', [PurchaseOrderController::class, 'rejectAll'])->name('po.rejectAll');
    Route::get('/purchase-orders/export', [PurchaseOrderController::class, 'exportExcel'])->name('po.export');
    Route::get('/purchaseOrder/{id}/edit', [PurchaseOrderController::class, 'edit'])->name('po.edit');
    Route::put('/purchaseOrder/{po}', [PurchaseOrderController::class, 'update'])->name('po.update');
    Route::post('purchase-orders/approve-selected', [PurchaseOrderController::class, 'approveSelected'])->name('purchase_orders.approve_selected');
    Route::post('purchase-orders/reject-selected', [PurchaseOrderController::class, 'rejectSelected'])->name('purchase_orders.reject_selected');
    Route::get('purchaseOrders/dashboard', [PurchaseOrderController::class, 'dashboard'])->name('po.dashboard');
    Route::get('/purchase-orders/filter', [PurchaseOrderController::class, 'filter']);
    Route::get('/purchase-orders/vendor-monthly-totals', [PurchaseOrderController::class, 'vendorMonthlyTotals'])->name('po.vendor-monthly-totals');
    Route::get('/purchase-orders/vendor-details', [PurchaseOrderController::class, 'getVendorDetails']);
    Route::put('/purchase-orders/cancel/{id}', [PurchaseOrderController::class, 'cancel'])->name('po.cancel');

    Route::resource('waiting_purchase_orders', WaitingPurchaseOrderController::class);
    Route::resource('employee_trainings', EmployeeTrainingController::class);
    Route::patch('employee_trainings/{employee_training}/evaluate', [EmployeeTrainingController::class, 'evaluate'])->name('employee_trainings.evaluate');
});

Route::middleware(['auth', 'is.head.or.management'])->group(function () {
    Route::get('/employee-dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
    Route::post('/employee-dashboard/update-employee-data', [EmployeeDashboardController::class, 'updateEmployeeData'])->name('employee.dashboard.updateEmployeeData');
    Route::get('/sync-progress/{companyArea}', [SyncProgressController::class, 'show']);
    Route::post('/director/warning-log', [DirectorHomeController::class, 'storeWarningLog'])->name('director.warning-log.store');
    Route::post('/filter-employees', [EmployeeDashboardController::class, 'filterEmployees'])->name('filter.employees');
    Route::post('/get-employees-by-category', [EmployeeDashboardController::class, 'getEmployeesByCategory'])->name('getEmployeesByCategory');
    Route::post('/get-employees-by-department', [EmployeeDashboardController::class, 'getEmployeesByDepartment'])->name('getEmployeesByDepartment');
    Route::post('/get-employees-by-chart-category', [EmployeeDashboardController::class, 'getEmployeesByChartCategory'])->name('getEmployeesByChartCategory');
    Route::get('/employees/{id}/warnings', function ($id) {
        $warnings = \App\Models\EmployeeWarningLog::where('nik', $id)->get();

        return response()->json($warnings);
    });
    Route::get('/get-employee-count-by-month/{year?}', [EmployeeDashboardController::class, 'getEmployeeCountByMonth'])->name('getEmployeeCountByMonth');
    Route::get('employee-with-evaluation', [EmployeeDashboardController::class, 'getEmployeeWithEvaluationData'])->name('employee-dashboard.getEmployeeWithEvaluationData');
    Route::get('employees', [EmployeeDashboardController::class, 'getEmployeesData'])->name('employee-dashboard.getEmployeesData');
    Route::get('/get-weekly-evaluation-data/{year}/{week}', [EmployeeDashboardController::class, 'getWeeklyEvaluationData'])->name('getWeeklyEvaluationData');
    Route::get('/get-employees-by-category-week/{department}/{category}/{year}/{week}', [EmployeeDashboardController::class, 'getEmployeesByCategoryAndWeek'])->name('getEmployeesByCategoryAndWeek');
});

Route::get('/autologin', function (\Illuminate\Http\Request $request) {
    // dd($request->all());
    if (! $request->hasValidSignature()) {
        abort(403, 'Invalid or expired link.');
    }

    $user = \App\Models\User::where('name', $request->name)->firstOrFail();

    Auth::login($user);

    return redirect()->route('employee.dashboard'); // or wherever you want to redirect after login
})->name('autologin');

Route::get('/dashboard-employee-login', function () {
    $user = \App\Models\User::where('name', 'dashboardemployee')->first();

    $link = \Illuminate\Support\Facades\URL::temporarySignedRoute('autologin', now()->addMinutes(30), ['name' => $user->name]);

    return redirect($link);
});

Route::get('/inspection-reports', InspectionIndex::class)->name('inspection-reports.index');
Route::get('/inspection-report/create', InspectionForm::class)->name('inspection-reports.create');
Route::get('/inspection-reports/{inspection_report}', InspectionShow::class)->name('inspection-reports.show');

Route::middleware('auth')->group(function () {
    Route::get('/destinations', DestinationIndex::class)->name('destination.index');
    Route::get('/destinations/create', DestinationForm::class)->name('destination.create');
    Route::get('/destinations/{id}/edit', DestinationForm::class)->name('destination.edit');
});

Route::prefix('delivery-notes')
    ->name('delivery-notes.')
    ->group(function () {
        Route::get('/', DeliveryNoteIndex::class)->name('index');
        Route::get('/create', DeliveryNoteForm::class)->name('create');
        Route::get('/{deliveryNote}/edit', DeliveryNoteForm::class)->name('edit');
        Route::get('/{id}', DeliveryNoteShow::class)->name('show');
        Route::get('/{deliveryNote}/print', DeliveryNotePrint::class)->name('print');
    });

Route::middleware('auth')->group(function () {
    Route::get('/master-data/parts/import', fn () => view('master-data-part.import-dashboard'))->name('md.parts.import');
    Route::get('/parts/import', ImportParts::class)->name('parts.import');
    Route::get('/import-jobs/{job}/log', [ImportJobController::class, 'downloadLog'])->name('import-jobs.log');
});

Route::get('/import-jabatan', [EmployeeController::class, 'showImportForm']);
Route::post('/import-jabatan', [EmployeeController::class, 'importJabatan']);

Route::middleware('auth')->group(function () {
    Route::get('/files', FileLibrary::class)->name('files.index');
    Route::get('/files/{upload}/download', DownloadUploadController::class)->name('files.download');
    Route::get('/files/{upload}/preview', PreviewUploadController::class)->name('files.preview');
});

Route::get('/reports/department-expenses', DepartmentExpenses::class)
    ->middleware(['auth'])
    ->name('department-expenses.index');

Route::middleware(['auth'])->group(function () {
    Route::get('/notifications/unread-count', [NotificationFeedController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/feed', [NotificationFeedController::class, 'feed'])->name('notifications.feed');
    Route::post('/notifications/mark-read/{id?}', [NotificationFeedController::class, 'markAsRead'])->name('notifications.mark-read');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/vehicles', VehiclesIndex::class)->name('vehicles.index');
    Route::get('/vehicles/{vehicle}', VehiclesShow::class)->name('vehicles.show');
    Route::get('/vehicle/create', VehiclesForm::class)->name('vehicles.create');
    Route::get('/vehicles/{vehicle}/edit', VehiclesForm::class)->name('vehicles.edit');
    Route::get('/services/create/{vehicle}', ServiceForm::class)->name('services.create');
    Route::get('/services/{record}/edit', ServiceForm::class)->name('services.edit');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/requirements', ReqIndex::class)->name('requirements.index');
    Route::get('/requirements/create', RequirementForm::class)->name('requirements.create');
    Route::get('/requirements/{requirement}/edit', RequirementForm::class)->name('requirements.edit');
    Route::get('/requirements/assign', ReqAssign::class)->name('requirements.assign');
    Route::get('/departments/{department}/compliance', DeptCompliance::class)->name('departments.compliance');
    Route::get('/admin/requirement-uploads', ReviewUploads::class)->name('admin.requirement-uploads');

    Route::get('/uploads/{upload}/download', function (Illuminate\Http\Request $request, App\Models\RequirementUpload $upload) {
        // optional: add Gate to restrict who can download
        // if (Illuminate\Support\Facades\Gate::denies('approve-requirements') && auth()->id() !== $upload->uploaded_by) {
        //     abort(403);
        // }

        return Illuminate\Support\Facades\Storage::disk('public')->download($upload->path, $upload->original_name);
    })
        ->middleware(['signed', 'auth'])
        ->name('uploads.download');

    Route::get('/requirements/{requirement}/departments', RequirementDepartments::class)->name('requirements.departments');

    Route::get('/departments/overview', DepartmentsOverview::class)->name('departments.overview');
    Route::get('/compliance/dashboard', ComplianceDashboard::class)->name('compliance.dashboard');

    Route::get('/uploads/{upload}/download', [RequirementUploadDownloadController::class, 'show'])
        ->name('uploads.download')
        ->middleware('signed');
});
Route::middleware(['web', 'auth'])->group(function () {
    // Secure stream of a signature image (PNG or SVG) from private disk
    Route::get('/signatures/{id}', [SignatureController::class, 'show'])->name('signatures.show');

    // Livewire pages
    Route::get('/settings/signatures', ManageSignatures::class)->name('signatures.manage');
    Route::get('/settings/signatures/capture', CaptureSignature::class)->name('signatures.capture');
});

// require __DIR__.'/admin.php';
