<?php

use App\Http\Controllers\accounting\AccountingHomeController;
use App\Http\Controllers\admin\DepartmentController;
use App\Http\Controllers\admin\UserController;
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
use App\Http\Controllers\maintenance\MaintenanceHomeController;
use App\Http\Controllers\pe\PEHomeController;
use App\Http\Controllers\PPICHomeController;
use App\Http\Controllers\SpecificationController;
use App\Http\Controllers\UpdateDailyController;
use App\Http\Controllers\EvaluationDataController;
use App\Http\Controllers\DisciplinePageController;

use App\Models\Department;
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
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/create/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/create/{id}', [UserController::class, 'destroy'])->name('users.delete');
    Route::get('/users/reset/{id}', [UserController::class, 'resetPassword'])->name('users.reset.password');

    Route::get('/change-email/page', [SuperAdminHomeController::class, 'updateEmailpage'])->name('changeemail.page');
    Route::post('/change-email',  [SuperAdminHomeController::class, 'updateEmail'])->name('email.update');
    Route::get('/get-email-settings/{feature}', [SuperAdminHomeController::class, 'getEmailSettings']);

    Route::get('/superadmin/home', [SuperAdminHomeController::class, 'index'])->name('superadmin.home');

    Route::prefix('superadmin')->group(function () {
        Route::name('superadmin.')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users');
            Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
            Route::put('/users/update/{id}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/users/delete/{id}', [UserController::class, 'destroy'])->name('users.delete');
            Route::post('/users/reset/{id}', [UserController::class, 'resetPassword'])->name('users.reset.password');
            Route::delete('/users/delete-selected', [UserController::class, 'deleteSelected'])->name('users.deleteSelected');

            Route::get('/departments', [DepartmentController::class, 'index'])->name('departments');
            Route::post('/departments/store', [DepartmentController::class, 'store'])->name('departments.store');
            Route::put('/departments/update/{id}', [DepartmentController::class, 'update'])->name('departments.update');
            Route::delete('/departments/delete/{id}', [DepartmentController::class, 'destroy'])->name('departments.delete');

            Route::get('/specifications', [SpecificationController::class, 'index'])->name('specifications');
            Route::post('/specifications/store', [SpecificationController::class, 'store'])->name('specifications.store');
            Route::put('/specifications/{id}/update', [SpecificationController::class, 'update'])->name('specifications.update');
            Route::delete('/specifications/{id}/delete', [SpecificationController::class, 'destroy'])->name('specifications.delete');

            Route::get('/permission', function () {
                return view('admin.permissions');
            })->name('permissions');

            Route::get('/settings', function () {
                return view('admin.settings');
            })->name('settings');

            Route::get('/business', function () {
                return view('business.business');
            })->name('business');

            Route::get('/production', function () {
                return view('production.production');
            })->name('production');
        });
    });
});


Route::middleware(['checkUserRole:2,1', 'checkSessionId'])->group(function () {

    Route::middleware(['checkDepartment:QA,QC', 'checkSessionId'])->group(function () {
        Route::get('/qaqc/home', [QaqcHomeController::class, 'index'])->name('qaqc.home');

        Route::post('/save-image-path/{reportId}/{section}', [QaqcReportController::class,'saveImagePath']);
        Route::post('/qaqc/{id}/upload-attachment', [QaqcReportController::class, 'uploadAttachment'])->name('uploadAttachment');
        Route::post('/qaqc/report/{reportId}/autograph/{section}', [QaqcReportController::class, 'storeSignature'])->name('qaqc.report.autograph.store');

        Route::get('/qaqc/reports', [QaqcReportController::class, 'index'])->name('qaqc.report.index');
        Route::get('/qaqc/report/{id}', [QaqcReportController::class, 'detail'])->name('qaqc.report.detail');
        Route::get('/qaqc/report/{id}/edit',[QaQcReportController::class, 'edit'])->name('qaqc.report.edit');
        Route::post('/qaqc/report/{id}/updateheader',[QaQcReportController::class, 'updateHeader'])->name('qaqc.report.updateHeader');
        Route::get('/qaqc/report/{id}/editdetail',[QaQcReportController::class, 'editDetail'])->name('qaqc.report.editDetail');
        Route::delete('/qaqc/report/{id}/deletedetail',[QaQcReportController::class, 'destroyDetail'])->name('qaqc.report.deleteDetail');
        Route::post('/qaqc/report/{id}/updatedetail',[QaQcReportController::class, 'updateDetail'])->name('qaqc.report.updateDetail');
        Route::get('/qaqc/report/{id}/editDefect',[QaQcReportController::class, 'editDefect'])->name('qaqc.report.editDefect');
        Route::put('/qaqc/report/{id}', [QaqcReportController::class, 'update' ])->name('qaqc.report.update');
        Route::get('/qaqc/reports/create', [QaqcReportController::class, 'create'])->name('qaqc.report.create');
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
        Route::delete('/qaqc/report/{id}', [QaqcReportController::class, 'destroy'])->name('qaqc.report.delete');

        // adding new defect category
        Route::get('/qaqc/defectcategory', [DefectCategoryController::class, 'index'])->name('qaqc.defectcategory');
        Route::post('/qaqc/defectcategory/store', [DefectCategoryController::class, 'store'])->name('qaqc.defectcategory.store');
        Route::put('/qaqc/defectcategory/{id}/update', [DefectCategoryController::class, 'update'])->name('qaqc.defectcategory.update');
        Route::delete('/qaqc/defectcategory/{id}/delete', [DefectCategoryController::class, 'destroy'])->name('qaqc.defectcategory.delete');
        // adding new defect category

        Route::get('/qaqc/reports/redirectToIndex', [QaqcReportController::class, 'redirectToIndex'])->name('qaqc.report.redirect.to.index');
        //REVISI
        Route::get('/items', [QaqcReportController::class, 'getItems'])->name('items');
        Route::get('/customers', [QaqcReportController::class, 'getCustomers'])->name('Customers');
        //REVISI

        Route::get('/item/price', [QaqcReportController::class, 'getItemPrice']);
        Route::get('/qaqc/reports/{id}/download', [QaqcReportController::class, 'exportToPdf'])->name('qaqc.report.download');
        Route::get('/qaqc/reports/{id}/preview', [QaqcReportController::class, 'previewPdf'])->name('qaqc.report.preview');
        Route::get('qaqc/report/{id}/lock', [QaqcReportController::class, 'lock'])->name('qaqc.report.lock');
        Route::get('/qaqc/export-reports', [QaqcReportController::class, 'exportToExcel'])->name('export.reports');
    });

    Route::middleware(['checkDepartment:HRD'])->group(function() {
        Route::get('/hrd/home', [HrdHomeController::class, 'index'])->name('hrd.home');

        Route::get('/hrd/importantdocs/', [ImportantDocController::class, 'index'])->name('hrd.importantDocs.index');
        Route::get('/hrd/importantdocs/create', [ImportantDocController::class, 'create'])->name('hrd.importantDocs.create');
        Route::post('/hrd/importantdocs/store', [ImportantDocController::class, 'store'])->name('hrd.importantDocs.store');
        Route::get('/hrd/importantdocs/{id}', [ImportantDocController::class, 'detail'])->name('hrd.importantDocs.detail');
        Route::get('/hrd/importantdocs/{id}/edit', [ImportantDocController::class, 'edit'])->name('hrd.importantDocs.edit');
        Route::put('/hrd/importantdocs/{id}', [ImportantDocController::class, 'update'])->name('hrd.importantDocs.update');
        Route::delete('/hrd/importantdocs/{id}', [ImportantDocController::class, 'destroy'])->name('hrd.importantDocs.delete');
    });

    Route::middleware(['checkDepartment:DIRECTOR'])->group(function() {

        Route::get('/director/home', [DirectorHomeController::class, 'index'])->name('director.home');
        Route::get('/director/qaqc/index', [ReportController::class, 'index'])->name('director.qaqc.index');
        Route::get('/director/qaqc/detail/{id}', [ReportController::class, 'detail'])->name('director.qaqc.detail');
        Route::put('/director/qaqc/approve/{id}', [ReportController::class, 'approve'])->name('director.qaqc.approve');
        Route::put('/director/qaqc/reject/{id}', [ReportController::class, 'reject'])->name('director.qaqc.reject');
        Route::put('/director/qaqc/approveSelected', [ReportController::class, 'approveSelected'])->name('director.qaqc.approveSelected');
        Route::put('/director/qaqc/rejectSelected', [ReportController::class, 'rejectSelected'])->name('director.qaqc.rejectSelected');

        Route::get('/director/pr/index', [DirectorPurchaseRequestController::class, 'index'])->name('director.pr.index');
        Route::put('/director/pr/approveSelected', [DirectorPurchaseRequestController::class, 'approveSelected'])->name('director.pr.approveSelected');
        Route::put('/director/pr/rejectSelected', [DirectorPurchaseRequestController::class, 'rejectSelected'])->name('director.pr.rejectSelected');
    });

    Route::middleware(['checkDepartment:PE'])->group(function(){
        Route::get('pe/home', [PEHomeController::class, 'index'])->name('pe.home');

        Route::get('/pe/trialinput', [PEController::class, 'trialinput'])->name('pe.trial');
        Route::post('/pe/trialfinish', [PEController::class, 'input'])->name('pe.input');
        Route::get('/pe/listformrequest', [PEController::class, 'view'])->name('pe.formlist');
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

    Route::middleware(['checkDepartment:BUSINESS,PPIC'])->group(function(){
        Route::get('/ppic/home', [PPICHomeController::class, 'index'])->name('ppic.home');
        Route::get('deliveryschedule/index', [DeliveryScheduleController::class, 'index'])->name('indexds');
        Route::get("deliveryschedule/raw",[DeliveryScheduleController::class, "indexraw"])->name("rawdelsched");
        Route::get('deliveryschedule/wip', [DeliveryScheduleController::class, 'indexfinal'])->name('indexfinalwip');

        Route::get("delsched/start1", [DeliveryScheduleController::class, "step1"])->name("deslsched.step1");
        Route::get("delsched/start2", [DeliveryScheduleController::class, "step2"])->name("deslsched.step2");
        Route::get("delsched/start3", [DeliveryScheduleController::class, "step3"])->name("deslsched.step3");
        Route::get("delsched/start4", [DeliveryScheduleController::class, "step4"])->name("deslsched.step4");

        Route::get("delsched/wip/step1", [DeliveryScheduleController::class, "step1wip"])->name("delschedwip.step1");
        Route::get("delsched/wip/step2", [DeliveryScheduleController::class, "step2wip"])->name("delschedwip.step2");
    });

    Route::middleware(['checkDepartment:ACCOUNTING'])->group(function(){
        Route::get('accounting/home', [AccountingHomeController::class, 'index'])->name('accounting.home');
    });

    Route::middleware(['checkDepartment:PRODUCTION'])->group(function(){
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


        Route::get("/pps/index", [PPSGeneralController::class, "index"])->name("indexpps");
        Route::get("/pps/menu", [PPSGeneralController::class, "menu"])->name("menupps");
        Route::post('/pps/portal', [PPSGeneralController::class, 'portal'])->name('portal');

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

    Route::middleware(['checkDepartment:MAINTENANCE'])->group(function(){
        Route::get('maintenance/home', [MaintenanceHomeController::class, 'index'])->name('maintenance.home');

        Route::get("maintenance/mould-repair", [MouldDownController::class, "index"])->name("moulddown.index");
        Route::post("/add/mould", [MouldDownController::class, "addmould"])->name('addmould');
        Route::get("maintenance/line-repair", [LineDownController::class, "index"])->name("linedown.index");
        Route::post("/add/line/down", [LineDownController::class, "addlinedown"])->name('addlinedown');
    });
});

Route::middleware(['checkUserRole:3'])->group(function () {
    Route::get('/user/home', [UserHomeController::class, 'index'])->name('user.home');
});

Route::middleware((['checkUserRole:1,2', 'checkSessionId']))->group(function(){

    Route::post('file/upload', [FileController::class, 'upload'])->name('file.upload');
    Route::delete('file/{id}/delete', [FileController::class, 'destroy'])->name('file.delete');

    // PR
    Route::get('/purchaseRequest', [PurchaseRequestController::class,'index'])->name('purchaserequest.home');
    Route::get('/purchaseRequest/create', [PurchaseRequestController::class,'create'])->name('purchaserequest.create');
    Route::post('/purchaseRequest/insert', [PurchaseRequestController::class,'insert'])->name('purchaserequest.insert');
    Route::get('/purchaserequest/detail/{id}', [PurchaseRequestController::class, 'detail'])->name('purchaserequest.detail');
    Route::get('/purchaserequest/reject/{id}', [PurchaseRequestController::class, 'reject'])->name('purchaserequest.reject');
    Route::get('/purchaserequest/{id}/edit', [PurchaseRequestController::class, 'edit'])->name('purchaserequest.edit');
    Route::put('/purchaserequest/{id}/update', [PurchaseRequestController::class, 'update'])->name('purchaserequest.update');
    Route::delete('/purchaserequest/{id}/delete', [PurchaseRequestController::class, 'destroy'])->name('purchaserequest.delete');

    // PR MONTHLY
    Route::get('/purchaserequest/monthly-list', [PurchaseRequestController::class, 'monthlyprlist'])->name('purchaserequest.monthlyprlist');
    Route::get('/purchaserequest/monthly-detail/{id}', [PurchaseRequestController::class, 'monthlydetail'])->name('purchaserequest.monthlydetail');
    Route::post('/save-signature-path-monthlydetail/{monthprId}/{section}', [PurchaseRequestController::class,'saveImagePathMonthly']);
    Route::get('/purchaserequest/monthlypr', [PurchaseRequestController::class, 'monthlyview'])->name('purchaserequest.monthly');
    Route::get('/purchaserequest/month-selected', [PurchaseRequestController::class, 'monthlyviewmonth'])->name('purchaserequest.monthlyselected');
    Route::post('/save-signature-path/{prId}/{section}', [PurchaseRequestController::class,'saveImagePath']);
    // Route::get('/purchase-request/chart-data/{year}/{month}', [PurchaseRequestController::class, 'getChartData']);

    Route::get('/purchaserequest/detail/{id}/approve', [DetailPurchaseRequestController::class, 'approve'])->name('purchaserequest.detail.approve');
    Route::get('/purchaserequest/detail/{id}/reject', [DetailPurchaseRequestController::class, 'reject'])->name('purchaserequest.detail.reject');
    // REVISI PR PENAMBAHAN DROPDOWN ITEM & PRICE
    Route::get('/get-item-names', [PurchaseRequestController::class, 'getItemNames']);
    // REVISI PR PENAMBAHAN DROPDOWN ITEM & PRICE

    // FORM CUTI
    Route::get('/form-cuti', [FormCutiController::class, 'index'])->name('formcuti.home');
    Route::get('/form-cuti/create', [FormCutiController::class, 'create'])->name('formcuti.create');
    Route::post('/form-cuti/insert', [FormCutiController::class, 'store'])->name('formcuti.insert');
    Route::get('/form-cuti/detail/{id}', [FormCutiController::class, 'detail'])->name('formcuti.detail');
    Route::post('/form-cuti/save-autograph-path/{formId}/{section}', [FormCutiController::class,'saveImagePath']);

    // FORM KELUAR
    Route::get('/form-keluar', [FormKeluarController::class, 'index'])->name('formkeluar.home');
    Route::get('/form-keluar/create', [FormKeluarController::class, 'create'])->name('formkeluar.create');
    Route::post('/form-keluar/insert', [FormKeluarController::class, 'store'])->name('formkeluar.insert');
    Route::get('/form-keluar/detail/{id}', [FormKeluarController::class, 'detail'])->name('formkeluar.detail');
    Route::post('/save-autosignature-path/{formId}/{section}', [FormKeluarController::class,'saveImagePath']);

    Route::get('/inventory/fg', [InventoryFgController::class, "index"])->name('inventoryfg');
    Route::get('/inventory/mtr',  [InventoryMtrController::class, "index"])->name('inventorymtr');

    Route::get('/inventory/line-list',  [InvLineListController::class, "index"])->name('invlinelist');
    Route::post("/add/line", [InvLineListController::class, "addline"])->name('addline');
    Route::put("/edit/line/{id}", [InvLineListController::class, "editline"])->name('editline');
    Route::delete("/delete/line/{linecode}", [InvLineListController::class, "deleteline"])->name('deleteline');

    Route::get('/inventory/fg', [InventoryFgController::class, "index"])->name('inventoryfg');
    Route::get('/inventory/mtr',  [InventoryMtrController::class, "index"])->name('inventorymtr');

    // Holiday list feature
    Route::get("setting/holiday-list", [HolidayListController::class, "index"])->name("indexholiday");
    Route::get("setting/holiday-list/create", [HolidayListController::class, "create"])->name("createholiday");
    Route::post('setting/input/holidays', [HolidayListController::class, "store"])->name('holidays.store');
    Route::get('/download-holiday-list-template', [HolidayListController::class, 'downloadTemplate'])->name('download.holiday.template');
    Route::post('/upload-holiday-list-template', [HolidayListController::class, 'uploadTemplate'])->name('upload.holiday.template');
    Route::delete('/holiday/{id}/delete', [HolidayListController::class, 'delete'])->name('holiday.delete');
    Route::put('/holiday/{id}/update', [HolidayListController::class, 'update'])->name('holiday.update');


    Route::get("projecttracker/index", [ProjectTrackerController::class, "index"])->name("pt.index");
    Route::get("projecttracker/create", [ProjectTrackerController::class, "create"])->name("pt.create");
    Route::post("projecttracker/post", [ProjectTrackerController::class, "store"])->name("pt.store");
    Route::get("projecttracker/detail/{id}", [ProjectTrackerController::class, "detail"])->name("pt.detail");
    Route::put('projecttracker/{id}/update-ongoing', [ProjectTrackerController::class, 'updateOngoing'])->name('pt.updateongoing');
    Route::put('projecttracker/{id}/update-test', [ProjectTrackerController::class, 'updateTest'])->name('pt.updatetest');
    Route::put('projecttracker/{id}/update-revision', [ProjectTrackerController::class, 'updateRevision'])->name('pt.updaterevision');
    Route::put('projecttracker/{id}/accept', [ProjectTrackerController::class, 'updateAccept'])->name('pt.updateaccept');



    Route::get("updatepage/index", [UpdateDailyController::class, "index"])->name("indexupdatepage");
    Route::post("/processdailydata", [UpdateDailyController::class, 'update'])->name("updatedata");

    Route::get("/employeemaster/index", [EmployeeMasterController::class, 'index'])->name("index.employeesmaster");
    Route::post("/employeemaster/add", [EmployeeMasterController::class, "addemployee"])->name('addemployee');

    Route::get("/evaluation/index", [EvaluationDataController::class, 'index'])->name("evaluation.index");
    Route::post("/processevaluationdata", [EvaluationDataController::class, 'update'])->name("UpdateEvaluation");

    Route::get("/discipline/index", [DisciplinePageController::class, 'index'])->name("discipline.index");


});
