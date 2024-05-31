<?php

use App\Http\Controllers\accounting\AccountingHomeController;
use App\Http\Controllers\admin\DepartmentController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\AssemblyHomeController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\BusinessHomeController;
use App\Http\Controllers\DefectCategoryController;
use App\Http\Controllers\director\DirectorHomeController;
use App\Http\Controllers\director\ReportController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\hrd\HrdHomeController;
use App\Http\Controllers\qaqc\QaqcHomeController;
use App\Http\Controllers\qaqc\QaqcReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\UserHomeController;
use App\Http\Controllers\SuperAdminHomeController;
use App\Http\Controllers\hrd\ImportantDocController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\FormCutiController;
use App\Http\Controllers\FormKeluarController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PEController;

//ROUTE SPECIAL PURCHASING
use App\Http\Controllers\PurchasingMaterialController;
use App\Http\Controllers\materialPredictionController;
use App\Http\Controllers\PurchasingDetailController;
//ROUTE SPECIAL PURCHASING

use App\Http\Controllers\MailController;
use App\Http\Controllers\ComputerHomeController;
use App\Http\Controllers\DirectorPurchaseRequestController;
use App\Http\Controllers\InventoryFgController;
use App\Http\Controllers\InventoryMtrController;
use App\Http\Controllers\InvLineListController;
use App\Http\Controllers\CapacityByForecastController;
use App\Http\Controllers\pps\PPSGeneralController;
use App\Http\Controllers\pps\PPSSecondController;
use App\Http\Controllers\pps\PPSAssemblyController;
use App\Http\Controllers\pps\PPSInjectionController;
use App\Http\Controllers\pps\PPSKarawangController;

use App\Http\Controllers\DeliveryScheduleController;
use App\Http\Controllers\DetailPurchaseRequestController;
use App\Http\Controllers\DSNewController;
use App\Http\Controllers\EmployeeMasterController;


use App\Http\Controllers\HolidayListController;
use App\Http\Controllers\ProductionHomeController;
use App\Http\Controllers\PurchasingReminderController;
use App\Http\Controllers\PurchasingRequirementController;
use App\Http\Controllers\ProjectTrackerController;
use App\Http\Controllers\MouldDownController;
use App\Http\Controllers\LineDownController;
use App\Http\Controllers\LogisticHomeController;
use App\Http\Controllers\maintenance\MaintenanceHomeController;
use App\Http\Controllers\ManagementHomeController;
use App\Http\Controllers\MMHomeController;
use App\Http\Controllers\MouldingHomeController;
use App\Http\Controllers\pe\PEHomeController;
use App\Http\Controllers\PersonaliaHomeController;
use App\Http\Controllers\PIHomeController;
use App\Http\Controllers\PPICHomeController;
use App\Http\Controllers\SpecificationController;
use App\Http\Controllers\SPHomeController;
use App\Http\Controllers\StoreHomeController;
use App\Http\Controllers\UpdateDailyController;
use App\Http\Controllers\EvaluationDataController;
use App\Http\Controllers\DisciplinePageController;
use App\Http\Controllers\ForecastCustomerController;
use App\Http\Controllers\FormOvertimeController;
use App\Http\Controllers\StockTintaController;


use App\Http\Controllers\AdjustFormQcController;
use App\Http\Controllers\MUHomeController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserPermissionController;
use App\Models\Department;
use App\Models\DetailPurchaseRequest;
use App\Models\Role;

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

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/home'); // Redirect to the home route for authenticated users
    }
    return view('auth.login');
})->name('/');

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/assign-role-manually', [UserRoleController::class, 'assignRoleToME'])->name('assignRoleManually');

Route::get('/change-password', [PasswordChangeController::class,'showChangePasswordForm'])->name('change.password.show');
Route::post('/change-password', [PasswordChangeController::class, 'changePassword'])->name('change.password');


Route::middleware(['checkUserRole:1', 'checkSessionId'])->group(function () {

    Route::get('/change-email/page', [SuperAdminHomeController::class, 'updateEmailpage'])->name('changeemail.page');
    Route::post('/change-email',  [SuperAdminHomeController::class, 'updateEmail'])->name('email.update');
    Route::get('/get-email-settings/{feature}', [SuperAdminHomeController::class, 'getEmailSettings']);

    Route::get('/superadmin/home', [SuperAdminHomeController::class, 'index'])->name('superadmin.home');

    Route::prefix('superadmin')->group(function () {
        Route::name('superadmin.')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users')->middleware('permission:get-users');
            Route::post('/users/store', [UserController::class, 'store'])->name('users.store')->middleware('permission:store-users');
            Route::put('/users/update/{id}', [UserController::class, 'update'])->name('users.update')->middleware('permission:update-users');
            Route::delete('/users/delete/{id}', [UserController::class, 'destroy'])->name('users.delete')->middleware('permission:delete-users');
            Route::get('/users/reset/{id}', [UserController::class, 'resetPassword'])->name('users.reset.password')->middleware('permission:reset-password-users');
            Route::delete('/users/delete-selected', [UserController::class, 'deleteSelected'])->name('users.deleteSelected')->middleware('permission:delete-selected-users');

            Route::get('/departments', [DepartmentController::class, 'index'])->name('departments')->middleware('permission:get-departments');
            Route::post('/departments/store', [DepartmentController::class, 'store'])->name('departments.store')->middleware('permission:store-departments');
            Route::put('/departments/update/{id}', [DepartmentController::class, 'update'])->name('departments.update')->middleware('permission:update-departments');
            Route::delete('/departments/delete/{id}', [DepartmentController::class, 'destroy'])->name('departments.delete')->middleware('permission:delete-departments');

            Route::get('/specifications', [SpecificationController::class, 'index'])->name('specifications')->middleware('permission:get-specifications');
            Route::post('/specifications/store', [SpecificationController::class, 'store'])->name('specifications.store')->middleware('permission:store-specifications');
            Route::put('/specifications/{id}/update', [SpecificationController::class, 'update'])->name('specifications.update')->middleware('permission:update-specifications');
            Route::delete('/specifications/{id}/delete', [SpecificationController::class, 'destroy'])->name('specifications.delete')->middleware('permission:delete-specifications');

            Route::get('/users-permissions', [UserPermissionController::class, 'index'])->name('users.permissions.index')->middleware('permission:get-users-permissions');
            Route::put('/users-permissions/{id}/update', [UserPermissionController::class, 'update'])->name('users.permissions.update')->middleware('permission:update-users-permissions');

            Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index')->middleware('permission:get-permissions');
            Route::post('/permissions/store', [PermissionController::class, 'store'])->name('permissions.store')->middleware('permission:store-permissions');
            Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update')->middleware('permission:update-permissions');
            Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy')->middleware('permission:delete-permissions');

        });
    });
});

Route::middleware(['checkUserRole:2,1', 'checkSessionId'])->group(function () {

    Route::middleware(['checkDepartment:QA,QC,ACCOUNTING,PPIC,STORE,LOGISTIC', 'checkSessionId'])->group(function () {
        Route::get('/qaqc/home', [QaqcHomeController::class, 'index'])->name('qaqc.home');

        Route::post('/save-image-path/{reportId}/{section}', [QaqcReportController::class,'saveImagePath']);
        Route::post('/qaqc/{id}/upload-attachment', [QaqcReportController::class, 'uploadAttachment'])->name('uploadAttachment');
        Route::post('/qaqc/report/{reportId}/autograph/{section}', [QaqcReportController::class, 'storeSignature'])->name('qaqc.report.autograph.store');

        Route::get('/qaqc/reports', [QaqcReportController::class, 'index'])->name('qaqc.report.index')->middleware('permission:get-vqc-reports');
        Route::get('/qaqc/report/{id}', [QaqcReportController::class, 'detail'])->name('qaqc.report.detail')->middleware('permission:detail-vqc-reports');
        Route::get('/qaqc/report/{id}/edit',[QaQcReportController::class, 'edit'])->name('qaqc.report.edit')->middleware('permission:edit-vqc-report');
        Route::post('/qaqc/report/{id}/updateheader',[QaQcReportController::class, 'updateHeader'])->name('qaqc.report.updateHeader');
        Route::get('/qaqc/report/{id}/editdetail',[QaQcReportController::class, 'editDetail'])->name('qaqc.report.editDetail');
        Route::delete('/qaqc/report/{id}/deletedetail',[QaQcReportController::class, 'destroyDetail'])->name('qaqc.report.deleteDetail');
        Route::post('/qaqc/report/{id}/updatedetail',[QaQcReportController::class, 'updateDetail'])->name('qaqc.report.updateDetail');
        Route::get('/qaqc/report/{id}/editDefect',[QaQcReportController::class, 'editDefect'])->name('qaqc.report.editDefect');
        Route::put('/qaqc/report/{id}', [QaqcReportController::class, 'update' ])->name('qaqc.report.update')->middleware('permission:update-vqc-report');
        Route::get('/qaqc/reports/create', [QaqcReportController::class, 'create'])->name('qaqc.report.create')->middleware('permission:create-vqc-report');
        Route::post('/qaqc/reports/createHeader', [QaqcReportController::class, 'postCreateHeader'])->name('qaqc.report.createheader');
        Route::get('/qaqc/reports/createdetail', [QaqcReportController::class, 'createDetail'])->name('qaqc.report.createdetail');
        Route::post('/qaqc/reports/postdetail', [QaqcReportController::class, 'postDetail'])->name('qaqc.report.postdetail');
        Route::get('/qaqc/reports/createdefect', [QaqcReportController::class, 'createDefect'])->name('qaqc.report.createdefect');
        Route::post('/qaqc/reports/postdefect', [QaqcReportController::class, 'postDefect'])->name('qaqc.report.postdefect');
        Route::delete('/qaqc/report/{id}/deletedefect', [QaqcReportController::class, 'deleteDefect'])->name('qaqc.report.deletedefect');
        Route::post('/update-active-tab', [QaqcReportController::class, 'updateActiveTab'])->name('update-active-tab');
        Route::get('qaqc/report/{id}/rejectAuto', [QaqcReportController::class, 'rejectAuto'])->name('qaqc.report.rejectAuto');
        Route::get('qaqc/report/{id}/savePdf', [QaqcReportController::class, 'savePdf'])->name('qaqc.report.savePdf');
        Route::post('qaqc/report/{id}/sendEmail', [QaqcReportController::class, 'sendEmail'])->name('qaqc.report.sendEmail');
        Route::post('/qaqc/reports/', [QaqcReportController::class, 'store'])->name('qaqc.report.store');
        Route::delete('/qaqc/report/{id}', [QaqcReportController::class, 'destroy'])->name('qaqc.report.delete')->middleware('permission:delete-vqc-report');

        // adding new defect category
        Route::get('/qaqc/defectcategory', [DefectCategoryController::class, 'index'])->name('qaqc.defectcategory')->middleware('permission:get-defect-categories');
        Route::post('/qaqc/defectcategory/store', [DefectCategoryController::class, 'store'])->name('qaqc.defectcategory.store')->middleware('permission:store-defect-category');
        Route::put('/qaqc/defectcategory/{id}/update', [DefectCategoryController::class, 'update'])->name('qaqc.defectcategory.update')->middleware('permission:update-defect-category');
        Route::delete('/qaqc/defectcategory/{id}/delete', [DefectCategoryController::class, 'destroy'])->name('qaqc.defectcategory.delete')->middleware('permission:delete-defect-category');
        // adding new defect category

        Route::get('/qaqc/reports/redirectToIndex', [QaqcReportController::class, 'redirectToIndex'])->name('qaqc.report.redirect.to.index');

        Route::get('/items', [QaqcReportController::class, 'getItems'])->name('items');
        Route::get('/customers', [QaqcReportController::class, 'getCustomers'])->name('Customers');
        Route::get('/item/price', [QaqcReportController::class, 'getItemPrice']);

        Route::get('/qaqc/reports/{id}/download', [QaqcReportController::class, 'exportToPdf'])->name('qaqc.report.download')->middleware('permission:download-vqc-report');
        Route::get('/qaqc/reports/{id}/preview', [QaqcReportController::class, 'previewPdf'])->name('qaqc.report.preview');
        Route::get('qaqc/report/{id}/lock', [QaqcReportController::class, 'lock'])->name('qaqc.report.lock')->middleware('permission:lock-vqc-report');
        Route::get('/qaqc/export-reports', [QaqcReportController::class, 'exportToExcel'])->name('export.reports')->middleware('permission:export-to-excel-vqc-report');
        Route::get('/qaqc/FormAdjust', [QaqcReportController::class, 'exportFormAdjustToExcel'])->name('export.formadjusts')->middleware();

        Route::put('/qaqc/reports/{id}/updateDoNumber', [QaQcReportController::class, 'updateDoNumber'])->name('update.do.number');

        Route::get('/qaqc/monthlyreport', [QaqcReportController::class, 'monthlyreport'])->name('qaqc.summarymonth');
        Route::post('/monthlyreport', [QaqcReportController::class, 'showDetails'])->name('monthlyreport.details');
        Route::post('/monthlyreport/export', [QaqcReportController::class, 'export'])->name('monthlyreport.export');

    });


    Route::middleware(['checkDepartment:QA,QC,ACCOUNTING,PPIC,STORE,LOGISTIC,DIRECTOR,PLASTIC INJECTION', 'checkSessionId'])->group(function () {

        //FORM ADJUST SECITON
          Route::get('/qaqc/adjustform', [AdjustFormQcController::class, 'index'])->name('adjust.index');
          Route::post('/qaqc/save/formadjust', [AdjustFormQcController::class, 'save'])->name('save.rawmaterial');
          Route::post('/fgwarehouse/save/adjust', [AdjustFormQcController::class, 'savewarehouse'])->name('fgwarehousesave');
          Route::get('/view/adjustform', [AdjustFormQcController::class, 'adjustformview'])->name('adjustview');
          Route::post('/remark/detail/adjust', [AdjustFormQcController::class, 'addremarkadjust'])->name('addremarkadjust');
          Route::post('/save-autograph-path/{reportId}/{section}', [AdjustFormQcController::class,'saveAutographPath']);

          Route::get('listformadjust/all',[AdjustFormQcController::class,'listformadjust'])->name('listformadjust');
    });

    Route::middleware(['checkDepartment:HRD'])->group(function() {
        Route::get('/hrd/home', [HrdHomeController::class, 'index'])->name('hrd.home');

        Route::get('/hrd/importantdocs/', [ImportantDocController::class, 'index'])->name('hrd.importantDocs.index')->middleware('permission:get-important-docs');
        Route::get('/hrd/importantdocs/create', [ImportantDocController::class, 'create'])->name('hrd.importantDocs.create')->middleware('permission:create-important-doc');
        Route::post('/hrd/importantdocs/store', [ImportantDocController::class, 'store'])->name('hrd.importantDocs.store')->middleware('permission:store-important-doc');
        Route::get('/hrd/importantdocs/{id}', [ImportantDocController::class, 'detail'])->name('hrd.importantDocs.detail')->middleware('permission:detail-important-doc');
        Route::get('/hrd/importantdocs/{id}/edit', [ImportantDocController::class, 'edit'])->name('hrd.importantDocs.edit')->middleware('permission:edit-important-doc');
        Route::put('/hrd/importantdocs/{id}', [ImportantDocController::class, 'update'])->name('hrd.importantDocs.update')->middleware('permission:update-important-doc');
        Route::delete('/hrd/importantdocs/{id}', [ImportantDocController::class, 'destroy'])->name('hrd.importantDocs.delete')->middleware('permission:delete-important-doc');
    });

    Route::middleware(['checkDepartment:DIRECTOR'])->group(function() {

        Route::get('/director/home', [DirectorHomeController::class, 'index'])->name('director.home');
        Route::get('/director/qaqc/index', [ReportController::class, 'index'])->name('director.qaqc.index')->middleware('permission:get-vqc-reports-director');
        Route::get('/director/qaqc/detail/{id}', [ReportController::class, 'detail'])->name('director.qaqc.detail')->middleware('permission:detail-vqc-report-director');
        Route::put('/director/qaqc/approve/{id}', [ReportController::class, 'approve'])->name('director.qaqc.approve')->middleware('permission:approve-vqc-report-director');
        Route::put('/director/qaqc/reject/{id}', [ReportController::class, 'reject'])->name('director.qaqc.reject')->middleware('permission:reject-vqc-report-director');
        Route::put('/director/qaqc/approveSelected', [ReportController::class, 'approveSelected'])->name('director.qaqc.approveSelected')->middleware('permission:approve-selected-vqc-report-director');;
        Route::put('/director/qaqc/rejectSelected', [ReportController::class, 'rejectSelected'])->name('director.qaqc.rejectSelected')->middleware('permission:reject-selected-vqc-report-director');;

        Route::get('/director/pr/index', [DirectorPurchaseRequestController::class, 'index'])->name('director.pr.index')->middleware('permission:get-pr-director');
        Route::put('/director/pr/approveSelected', [DirectorPurchaseRequestController::class, 'approveSelected'])->name('director.pr.approveSelected')->middleware('permission:approve-selected-director');
        Route::put('/director/pr/rejectSelected', [DirectorPurchaseRequestController::class, 'rejectSelected'])->name('director.pr.rejectSelected')->middleware('permission:reject-selected-director');
    });

    Route::middleware(['checkDepartment:PE,PPIC'])->group(function(){
        Route::get('pe/home', [PEHomeController::class, 'index'])->name('pe.home');

        Route::get('/pe/trialinput', [PEController::class, 'trialinput'])->name('pe.trial');
        Route::post('/pe/trialfinish', [PEController::class, 'input'])->name('pe.input');
        Route::get('/pe/listformrequest', [PEController::class, 'view'])->name('pe.formlist')->middleware('permission:get-pe-form-list');
        Route::get('/pe/listformrequest/detail/{id}', [PEController::class, 'detail'])->name('trial.detail');
        Route::post('/pe/listformrequest/detai/updateTonage/{id}', [PEController::class, 'updateTonage'])->name('update.tonage');
    });

    Route::middleware(['checkDepartment:PURCHASING'])->group(function(){

        Route::get('/purchasing', [PurchasingController::class, 'index'])->name('purchasing.home');

        Route::get('/store-data', [PurchasingMaterialController::class, 'storeDataInNewTable'])->name('construct_data');
        Route::get('/insert-material_prediction', [materialPredictionController::class,'processForemindFinalData'])->name('material_prediction');
        Route::get('/foremind-detail', [PurchasingController::class, 'indexhome'])->name('purchasing_home');
        Route::get('/foremind-detail/print', [PurchasingDetailController::class, 'index']);
        Route::get('/foremind-detail/printCustomer', [PurchasingDetailController::class,'indexcustomer']);

        Route::get('/foremind-detail/print/excel/{vendor_code}', [PurchasingDetailController::class, 'exportExcel']);
        Route::get('/foremind-detail/print/customer/excel/{vendor_code}', [PurchasingDetailController::class, 'exportExcelcustomer']);

        Route::get("purchasing/reminder", [PurchasingReminderController::class, "index"])->name('reminderindex');
        Route::get("purchasing/reminder/detail", [PurchasingReminderController::class, "detail"])->name('reminderdetail');

        Route::get("purchasing/requirement", [PurchasingRequirementController::class, "index"])->name("purchasingrequirement.index");
        Route::get("purchasing/requirement/detail", [PurchasingRequirementController::class, "detail"])->name("purchasingrequirement.detail");

    });

    Route::middleware(['checkDepartment:COMPUTER', 'checkSessionId'])->group(function () {
        Route::get('/computer/home', [ComputerHomeController::class, 'index'])->name('computer.home');
    });

    Route::middleware(['checkDepartment:BUSINESS,PPIC,PURCHASING'])->group(function(){
        Route::get('/ppic/home', [PPICHomeController::class, 'index'])->name('ppic.home');
        Route::get('deliveryschedule/index', [DeliveryScheduleController::class, 'index'])->name('indexds')->middleware('permission:get-delivery-schedule-index');
        Route::get("deliveryschedule/raw",[DeliveryScheduleController::class, "indexraw"])->name("rawdelsched");
        Route::get('deliveryschedule/wip', [DeliveryScheduleController::class, 'indexfinal'])->name('indexfinalwip');
        Route::get('deliveryschedule/averagemonth', [DeliveryScheduleController::class, 'averageschedule'])->name('averagemonth');


        Route::get("delsched/start1", [DeliveryScheduleController::class, "step1"])->name("deslsched.step1");
        Route::get("delsched/start2", [DeliveryScheduleController::class, "step2"])->name("deslsched.step2");
        Route::get("delsched/start3", [DeliveryScheduleController::class, "step3"])->name("deslsched.step3");
        Route::get("delsched/start4", [DeliveryScheduleController::class, "step4"])->name("deslsched.step4");

        Route::get("delsched/wip/step1", [DeliveryScheduleController::class, "step1wip"])->name("delschedwip.step1");
        Route::get("delsched/wip/step2", [DeliveryScheduleController::class, "step2wip"])->name("delschedwip.step2");
    });

    Route::middleware(['checkDepartment:BUSINESS'])->group(function(){
        Route::get('business/home', [BusinessHomeController::class, 'index'])->name('business.home');
    });

    Route::middleware(['checkDepartment:ACCOUNTING'])->group(function(){
        Route::get('accounting/home', [AccountingHomeController::class, 'index'])->name('accounting.home');
    });

    Route::middleware(['checkDepartment:PRODUCTION,PPIC'])->group(function(){
        Route::get('production/home', [ProductionHomeController::class, 'index'])->name('production.home');

        Route::get("/production/capacity-forecast", [CapacityByForecastController::class, "index"])->name('capacityforecastindex');
        Route::get("/production/capacity-line", [CapacityByForecastController::class, "line"])->name('capacityforecastline');
        Route::get("/production/capacity-distribution", [CapacityByForecastController::class, "distribution"])->name('capacityforecastdistribution');
        Route::get("/production/capacity-detail", [CapacityByForecastController::class, "detail"])->name('capacityforecastdetail');

        Route::get("/production/capacity-forecast/view-step", [CapacityByForecastController::class, "viewstep1"])->name('viewstep1');
        Route::get("/production/capacity-forecast/step1", [CapacityByForecastController::class, "step1"])->name('step1');
        Route::get("/production/capacity-forecast/step1second", [CapacityByForecastController::class, "step1_second"])->name('step1second');

        Route::get("/production/capacity-forecast/step2", [CapacityByForecastController::class, "step2"])->name('step2');
        Route::get("/production/capacity-forecast/step2logic", [CapacityByForecastController::class, "step2logic"])->name('step2logic');

        Route::get("/production/capacity-forecast/step3", [CapacityByForecastController::class, "step3"])->name('step3');
        Route::get("/production/capacity-forecast/step3logic", [CapacityByForecastController::class, "step3logic"])->name('step3logic');
        Route::get("/production/capacity-forecast/step3last", [CapacityByForecastController::class, "step3logiclast"])->name('step3logiclast');


        Route::get("/pps/index", [PPSGeneralController::class, "index"])->name("indexpps")->middleware('permission:get-pps-index');
        Route::get("/pps/menu", [PPSGeneralController::class, "menu"])->name("menupps");
        Route::post('/pps/portal', [PPSGeneralController::class, 'portal'])->name('portal');

        //KarawangRoute
        Route::get("/pps/karawang", [PPSKarawangController::class, "index"])->name('indexkarawang');
        Route::post('/pps/process-karawang-form', [PPSKarawangController::class, 'processKarawangForm'])->name('processKarawangForm');

        Route::get("/pps/injection/start", [PPSInjectionController::class, "indexscenario"])->name("indexinjection");
        Route::post('/pps/process-injection-form', [PPSInjectionController::class, 'processInjectionForm'])->name('processInjectionForm');
        Route::get("pps/injection/process1", [PPSInjectionController::class, 'process1'])->name('injectionprocess1');
        Route::get("pps/injection/process2", [PPSInjectionController::class, 'process2'])->name('injectionprocess2');
        Route::get("pps/injection/process3", [PPSInjectionController::class, 'process3'])->name('injectionprocess3');


        Route::get("/pps/injection/delivery", [PPSInjectionController::class, "deliveryinjection"])->name("deliveryinjection");
        Route::get("pps/injection/process4", [PPSInjectionController::class, 'process4'])->name("injectionprocess4");
        Route::get("pps/injection/process5", [PPSInjectionController::class, 'process5'])->name("injectionprocess5");
        Route::get("pps/injection/process6", [PPSInjectionController::class, 'process6'])->name("injectionprocess6");
        //jika ada post untuk delivery

        Route::get("/pps/injection/items", [PPSInjectionController::class, "iteminjection"])->name("iteminjection");
        // jika ada post untuk items

        Route::get("/pps/injection/line", [PPSInjectionController::class, "lineinjection"])->name("lineinjection");
        //jika ada post untuk line

        Route::get("pps/injectionfinal",  [PPSInjectionController::class, "finalresultinjection"])->name("finalinjectionpps");


        Route::get("/pps/second/start", [PPSSecondController::class, "indexscenario"])->name("indexsecond");
        Route::post("/pps/second-process-form", [PPSSecondController::class, "processSecondForm"])->name("processSecondForm");
        Route::get("pps/second/process1", [PPSSecondController::class, 'process1'])->name('secondprocess1');
        Route::get("pps/second/process2", [PPSSecondController::class, 'process2'])->name('secondprocess2');
        Route::get("pps/second/process3", [PPSSecondController::class, 'process3'])->name('secondprocess3');
        //jika ada post untuk start

        Route::get("/pps/second/delivery", [PPSSecondController::class, "deliverysecond"])->name("deliverysecond");
        Route::get("pps/second/process4", [PPSSecondController::class, 'process4'])->name('secondprocess4');
        Route::get("pps/second/process5", [PPSSecondController::class, 'process5'])->name('secondprocess5');
        Route::get("pps/second/process6", [PPSSecondController::class, 'process6'])->name('secondprocess6');
        //jika ada post untuk delivery

        Route::get("/pps/second/items", [PPSSecondController::class, "itemsecond"])->name("itemsecond");
        // jika ada post untuk items

        Route::get("/pps/second/line", [PPSSecondController::class, "linesecond"])->name("linesecond");
        //jika ada post untuk line

        Route::get("pps/secondfinal",  [PPSSecondController::class, "finalresultsecond"])->name("finalsecondpps");

        Route::get("/pps/assembly/start", [PPSAssemblyController::class, "indexscenario"])->name("indexassembly");
        Route::post("/pps/assembly-process-form", [PPSAssemblyController::class, "processAssemblyForm"])->name("processAssemblyForm");
        Route::get("pps/assembly/process1", [PPSAssemblyController::class, 'process1'])->name('assemblyprocess1');
        Route::get("pps/assembly/process2", [PPSAssemblyController::class, 'process2'])->name('assemblyprocess2');
        Route::get("pps/assembly/process3", [PPSAssemblyController::class, 'process3'])->name('assemblyprocess3');
        //jika ada post untuk start

        Route::get("/pps/assembly/delivery", [PPSAssemblyController::class, "deliveryassembly"])->name("deliveryassembly");
        Route::get("pps/assembly/process4", [PPSAssemblyController::class, 'process4'])->name('assemblyprocess4');
        Route::get("pps/assembly/process5", [PPSAssemblyController::class, 'process5'])->name('assemblyprocess5');
        Route::get("pps/assembly/process6", [PPSAssemblyController::class, 'process6'])->name('assemblyprocess6');
        //jika ada post untuk delivery

        Route::get("/pps/assembly/items", [PPSAssemblyController::class, "itemassembly"])->name("itemassembly");
        // jika ada post untuk items

        Route::get("/pps/assembly/line", [PPSAssemblyController::class, "lineassembly"])->name("lineassembly");
        //jika ada post untuk line

        Route::get("pps/assembly",  [PPSAssemblyController::class, "finalresultassembly"])->name("finalresultassembly");
    });

    Route::middleware(['checkDepartment:MAINTENANCE,PPIC'])->group(function(){
        Route::get('maintenance/home', [MaintenanceHomeController::class, 'index'])->name('maintenance.home');

        Route::get("maintenance/mould-repair", [MouldDownController::class, "index"])->name("moulddown.index")->middleware('permission:get-mould-down-index');
        Route::post("/add/mould", [MouldDownController::class, "addmould"])->name('addmould');
        Route::get("maintenance/line-repair", [LineDownController::class, "index"])->name("linedown.index");
        Route::post("/add/line/down", [LineDownController::class, "addlinedown"])->name('addlinedown');
    });

    Route::middleware(['checkDepartment:PLASTIC INJECTION'])->group(function(){
        Route::get('pi/home', [PIHomeController::class, 'index'])->name('pi.home');
    });

    Route::middleware(['checkDepartment:MOULDING'])->group(function(){
        Route::get('moulding/home', [MouldingHomeController::class, 'index'])->name('moulding.home');
    });

    Route::middleware(['checkDepartment:STORE'])->group(function(){
        Route::get('store/home', [StoreHomeController::class, 'index'])->name('store.home');
    });

    Route::middleware(['checkDepartment:SECOND PROCESS'])->group(function(){
        Route::get('sp/home', [SPHomeController::class, 'index'])->name('sp.home');
    });

    Route::middleware(['checkDepartment:ASSEMBLY'])->group(function(){
        Route::get('assembly/home', [AssemblyHomeController::class, 'index'])->name('assembly.home');
    });

    Route::middleware(['checkDepartment:PERSONALIA'])->group(function(){
        Route::get('personalia/home', [PersonaliaHomeController::class, 'index'])->name('personalia.home');
    });

    Route::middleware(['checkDepartment:MANAGEMENT'])->group(function(){
        Route::get('management/home', [ManagementHomeController::class, 'index'])->name('management.home');
    });

    Route::middleware(['checkDepartment:LOGISTIC'])->group(function(){
        Route::get('logistic/home', [LogisticHomeController::class, 'index'])->name('logistic.home');
    });

    Route::middleware(['checkDepartment:MAINTENANCE MOULDING'])->group(function(){
        Route::get('mm/home', [MMHomeController::class, 'index'])->name('mm.home');
    });

    Route::middleware(['checkDepartment:MAINTENANCE UTILITY'])->group(function(){
        Route::get('mu/home', [MUHomeController::class, 'index'])->name('mu.home');
    });

});

Route::middleware(['checkUserRole:3'])->group(function () {
    Route::get('/user/home', [UserHomeController::class, 'index'])->name('user.home');
});

Route::middleware((['checkUserRole:1,2', 'checkSessionId']))->group(function(){

    Route::post('file/upload', [FileController::class, 'upload'])->name('file.upload');
    Route::delete('file/{id}/delete', [FileController::class, 'destroy'])->name('file.delete');

    // PR
    Route::get('/purchaseRequest', [PurchaseRequestController::class,'index'])->name('purchaserequest.home')->middleware('permission:get-purchase-requests');
    Route::get('/purchaseRequest/create', [PurchaseRequestController::class,'create'])->name('purchaserequest.create')->middleware('permission:create-purchase-request');
    Route::post('/purchaseRequest/insert', [PurchaseRequestController::class,'insert'])->name('purchaserequest.insert')->middleware('permission:store-purchase-request');
    Route::get('/purchaserequest/detail/{id}', [PurchaseRequestController::class, 'detail'])->name('purchaserequest.detail')->middleware('permission:detail-purchase-request');
    Route::get('/purchaserequest/reject/{id}', [PurchaseRequestController::class, 'reject'])->name('purchaserequest.reject')->middleware('permission:reject-purchase-request');
    Route::put('/purchaserequest/{id}/update', [PurchaseRequestController::class, 'update'])->name('purchaserequest.update')->middleware('permission:update-purchase-request');
    Route::delete('/purchaserequest/{id}/delete', [PurchaseRequestController::class, 'destroy'])->name('purchaserequest.delete')->middleware('permission:delete-purchase-request');

    // PR MONTHLY
    Route::get('/purchaserequest/monthly-list', [PurchaseRequestController::class, 'monthlyprlist'])->name('purchaserequest.monthlyprlist');
    Route::get('/purchaserequest/monthly-detail/{id}', [PurchaseRequestController::class, 'monthlydetail'])->name('purchaserequest.monthlydetail');
    Route::post('/save-signature-path-monthlydetail/{monthprId}/{section}', [PurchaseRequestController::class,'saveImagePathMonthly']);
    Route::get('/purchaserequest/monthlypr', [PurchaseRequestController::class, 'monthlyview'])->name('purchaserequest.monthly');
    Route::get('/purchaserequest/month-selected', [PurchaseRequestController::class, 'monthlyviewmonth'])->name('purchaserequest.monthlyselected');
    Route::post('/save-signature-path/{prId}/{section}', [PurchaseRequestController::class,'saveImagePath']);
    // Route::get('/purchase-request/chart-data/{year}/{month}', [PurchaseRequestController::class, 'getChartData']);
    Route::get('approveAllDetailItems/{prId}/{type}', [PurchaseRequestController::class, 'approveAllDetailItems']);

    Route::get('/purchaserequest/detail/{id}/approve', [DetailPurchaseRequestController::class, 'approve'])->name('purchaserequest.detail.approve');
    Route::get('/purchaserequest/detail/{id}/reject', [DetailPurchaseRequestController::class, 'reject'])->name('purchaserequest.detail.reject');
    Route::post('/purchaserequest/detail/update', [DetailPurchaseRequestController::class, 'update'])->name('purchaserequest.detail.update');
    // REVISI PR PENAMBAHAN DROPDOWN ITEM & PRICE
    Route::get('/get-item-names', [PurchaseRequestController::class, 'getItemNames']);

    Route::post('/purchaseRequest/detail/{id}/updateReceivedQuantity', [DetailPurchaseRequestController::class, 'updateReceivedQuantity'])->name('purchaserequest.update.receivedQuantity');
    Route::get('/purchaseRequest/detail/{id}/updateAllReceivedQuantity', [DetailPurchaseRequestController::class, 'updateAllReceivedQuantity'])->name('purchaserequest.update.allReceivedQuantity');

    // FORM CUTI
    Route::get('/form-cuti', [FormCutiController::class, 'index'])->name('formcuti.home')->middleware('permission:get-form-cuti');
    Route::get('/form-cuti/create', [FormCutiController::class, 'create'])->name('formcuti.create')->middleware('permission:create-form-cuti');
    Route::post('/form-cuti/insert', [FormCutiController::class, 'store'])->name('formcuti.insert')->middleware('permission:store-form-cuti');
    Route::get('/form-cuti/detail/{id}', [FormCutiController::class, 'detail'])->name('formcuti.detail')->middleware('permission:detail-form-cuti');
    Route::post('/form-cuti/save-autograph-path/{formId}/{section}', [FormCutiController::class,'saveImagePath']);

    // FORM KELUAR
    Route::get('/form-keluar', [FormKeluarController::class, 'index'])->name('formkeluar.home')->middleware('permission:get-form-keluar');
    Route::get('/form-keluar/create', [FormKeluarController::class, 'create'])->name('formkeluar.create')->middleware('permission:create-form-keluar');
    Route::post('/form-keluar/insert', [FormKeluarController::class, 'store'])->name('formkeluar.insert')->middleware('permission:store-form-keluar');
    Route::get('/form-keluar/detail/{id}', [FormKeluarController::class, 'detail'])->name('formkeluar.detail')->middleware('permission:detail-form-keluar');
    Route::post('/save-autosignature-path/{formId}/{section}', [FormKeluarController::class,'saveImagePath']);

    Route::get('/inventory/fg', [InventoryFgController::class, "index"])->name('inventoryfg')->middleware('permission:get-inventory-fg');
    Route::get('/inventory/mtr',  [InventoryMtrController::class, "index"])->name('inventorymtr')->middleware('permission:get-inventory-mtr');

    Route::get('/inventory/line-list',  [InvLineListController::class, "index"])->name('invlinelist')->middleware('permission:get-inventory-line-list');
    Route::post("/add/line", [InvLineListController::class, "addline"])->name('addline');
    Route::put("/edit/line/{id}", [InvLineListController::class, "editline"])->name('editline');
    Route::delete("/delete/line/{linecode}", [InvLineListController::class, "deleteline"])->name('deleteline');

    // Holiday list feature
    Route::get("setting/holiday-list", [HolidayListController::class, "index"])->name("indexholiday")->middleware('permission:get-holiday-list-index');
    Route::get("setting/holiday-list/create", [HolidayListController::class, "create"])->name("createholiday")->middleware('permission:create-holiday-list');
    Route::post('setting/input/holidays', [HolidayListController::class, "store"])->name('holidays.store')->middleware('permission:store-holiday-list');
    Route::get('/download-holiday-list-template', [HolidayListController::class, 'downloadTemplate'])->name('download.holiday.template')->middleware('permission:download-holiday-list-template');
    Route::post('/upload-holiday-list-template', [HolidayListController::class, 'uploadTemplate'])->name('upload.holiday.template')->middleware('permission:upload-holiday-list-template');
    Route::delete('/holiday/{id}/delete', [HolidayListController::class, 'delete'])->name('holiday.delete')->middleware('permission:delete-holiday-list');
    Route::put('/holiday/{id}/update', [HolidayListController::class, 'update'])->name('holiday.update')->middleware('permission:update-holiday-list');


    Route::get("projecttracker/index", [ProjectTrackerController::class, "index"])->name("pt.index")->middleware('permission:get-project-tracker-index');
    Route::get("projecttracker/create", [ProjectTrackerController::class, "create"])->name("pt.create");
    Route::post("projecttracker/post", [ProjectTrackerController::class, "store"])->name("pt.store");
    Route::get("projecttracker/detail/{id}", [ProjectTrackerController::class, "detail"])->name("pt.detail");
    Route::put('projecttracker/{id}/update-ongoing', [ProjectTrackerController::class, 'updateOngoing'])->name('pt.updateongoing');
    Route::put('projecttracker/{id}/update-test', [ProjectTrackerController::class, 'updateTest'])->name('pt.updatetest');
    Route::put('projecttracker/{id}/update-revision', [ProjectTrackerController::class, 'updateRevision'])->name('pt.updaterevision');
    Route::put('projecttracker/{id}/accept', [ProjectTrackerController::class, 'updateAccept'])->name('pt.updateaccept');

    Route::get("updatepage/index", [UpdateDailyController::class, "index"])->name("indexupdatepage");
    Route::post("/processdailydata", [UpdateDailyController::class, 'update'])->name("updatedata");

    Route::get("/employeemaster/index", [EmployeeMasterController::class, 'index'])->name("index.employeesmaster")->middleware('permission:get-employee-master-index');
    Route::post("/employeemaster/add", [EmployeeMasterController::class, "addemployee"])->name('addemployee');
    Route::put("/edit/employee/{id}", [EmployeeMasterController::class, "editemployee"])->name('editemployee');
    Route::delete("/delete/employee/{linecode}", [EmployeeMasterController::class, "deleteemployee"])->name('deleteemployee');



    Route::get("/evaluation/index", [EvaluationDataController::class, 'index'])->name("evaluation.index")->middleware('permission:get-evaluation-index');
    Route::post("/processevaluationdata", [EvaluationDataController::class, 'update'])->name("UpdateEvaluation");
    Route::delete('/delete-evaluation', [EvaluationDataController::class, 'delete'])->name('DeleteEvaluation');


    Route::get("/discipline/indexall", [DisciplinePageController::class, 'allindex'])->name("alldiscipline.index");
    Route::get("/discipline/index", [DisciplinePageController::class, 'index'])->name("discipline.index")->middleware('permission:get-discipline-index');

    Route::post('/set-filter-value', [DisciplinePageController::class, 'setFilterValue']);
    Route::get('/get-filter-value', [DisciplinePageController::class, 'getFilterValue']);
    Route::put("/edit/discipline/{id}", [DisciplinePageController::class, "update"])->name('editdiscipline');
    Route::post('/updatediscipline', [DisciplinePageController::class, 'import'])->name('discipline.import');
    Route::get("/disciplineupdate/step1",  [DisciplinePageController::class, 'step1'])->name('update.point');
    Route::get("/disciplineupdate/step2",  [DisciplinePageController::class, 'step2'])->name('update.excel');

    Route::get("/forecastcustomermaster", [ForecastCustomerController::class, 'index'])->name("fc.index")->middleware('permission:get-forecast-customer-index');
    Route::post("/add/forecastmaster", [ForecastCustomerController::class, "addnewmaster"])->name('addnewforecastmaster');


    Route::get("/formovertime/index", [FormOvertimeController::class, 'index'])->name("formovertime.index");
    Route::get("/formovertime/create", [FormOvertimeController::class, 'create'])->name("formovertime.create");
    Route::post("/formovertime/insert", [FormOvertimeController::class, 'insert'])->name("formovertime.insert");
    Route::get("/formovertime/detail/{id}", [FormOvertimeController::class, 'detail'])->name("formovertime.detail");
    Route::post('/save-autographot-path/{reportId}/{section}', [FormOvertimeController::class,'saveAutographOtPath']);
    Route::put('/overtime/reject/{id}', [FormOvertimeController::class, 'reject'])->name('overtime.reject');

    Route::get('export-overtime/{headerId}', [FormOvertimeController::class, 'exportOvertime'])->name('export.overtime');

    Route::get('/get-nik-names', [FormOvertimeController::class, 'getEmployeeNik']);

    Route::get('/stock-tinta-index', [StockTintaController::class, 'index'])->name('stocktinta');


});
