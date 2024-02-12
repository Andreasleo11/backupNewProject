<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\director\DirectorHomeController;
use App\Http\Controllers\director\ReportController;
use App\Http\Controllers\hrd\HrdHomeController;
use App\Http\Controllers\qaqc\QaqcHomeController;
use App\Http\Controllers\qaqc\QaqcReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\UserHomeController;
use App\Http\Controllers\SuperAdminHomeController;

use App\Http\Controllers\hrd\ImportantDocController;
use Illuminate\Support\Facades\Auth;


use App\Http\Controllers\PurchaseRequestController;

use App\Http\Controllers\FormCutiController;

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

 //PR SECTION
 Route::get('/purchaseRequest', [PurchaseRequestController::class,'index'])->name('purchaserequest.home');
 Route::get('/purchaseRequest/create', [PurchaseRequestController::class,'create'])->name('purchaserequest.create');
 Route::post('/purchaseRequest/insert', [PurchaseRequestController::class,'insert'])->name('purchaserequest.insert');
 Route::get('/purchaserequest/list', [PurchaseRequestController::class, 'viewAll'])->name('purchaserequest.view');
 Route::get('/purchaserequest/detail/{id}', [PurchaseRequestController::class, 'detail'])->name('purchaserequest.detail');
 Route::get('/purchaserequest/monthlypr', [PurchaseRequestController::class, 'monthlyview'])->name('purchaserequest.monthly');
 Route::get('/purchaserequest/month-selected', [PurchaseRequestController::class, 'monthlyviewmonth'])->name('purchaserequest.monthlyselected');
 Route::post('/save-signature-path/{prId}/{section}', [PurchaseRequestController::class,'saveImagePath']);
 Route::get('/purchaserequest/monthly-list', [PurchaseRequestController::class, 'monthlyprlist'])->name('purchaserequest.monthlyprlist');
 Route::get('/purchaserequest/monthly-detail/{id}', [PurchaseRequestController::class, 'monthlydetail'])->name('purchaserequest.monthlydetail');
 Route::post('/save-signature-path-monthlydetail/{monthprId}/{section}', [PurchaseRequestController::class,'saveImagePathMonthly']);


 Route::get('/purchase-request/chart-data/{year}/{month}', 'PurchaseRequestController@getChartData');
 //PR SECTION 




 // FORM CUTI SESSION
Route::get('/Form-cuti', [FormCutiController::class, 'index'])->name('formcuti.home');
Route::get('/Form-cuti/create', [FormCutiController::class, 'create'])->name('formcuti.create');
Route::get('/Form-cuti/view', [FormCutiController::class, 'view'])->name('formcuti.view');
Route::post('/Form-cuti/insert', [FormCutiController::class, 'store'])->name('formcuti.insert');
Route::get('/Form-cuti/detail/{id}', [FormCutiController::class, 'detail'])->name('formcuti.detail');
Route::post('/save-aurographed-path/{formId}/{section}', [FormCutiController::class,'saveImagePath']);

 // FORM CUTI SESSION
Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/home'); // Redirect to the home route for authenticated users
    }
    return view('auth.login');
});


Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/assign-role-manually', [UserRoleController::class, 'assignRoleToME'])->name('assignRoleManually');

Route::middleware(['checkSessionId'])->group(function () {
Route::middleware(['checkUserRole:1'])->group(function () {
    Route::get('/superadmin/home', [SuperAdminHomeController::class, 'index'])->name('superadmin.home');

    Route::get('/userSA/home', [UserHomeController::class, 'index']);
    Route::prefix('superadmin')->group(function () {
        Route::name('superadmin.')->group(function () {
            Route::get('/users', function () {
                return view('admin.users');
            })->name('users');

            Route::get('/permission', function () {
                return view('admin.permission');
            })->name('permission');

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

Route::middleware(['checkUserRole:2', 'checkSessionId'])->group(function () {

    Route::get('/director/home', [DirectorHomeController::class, 'index'])->name('director.home');
    Route::get('/hrd/home', [HrdHomeController::class, 'index'])->name('hrd.home');

    Route::middleware(['checkDepartment:QA,QC', 'checkSessionId'])->group(function () {
        Route::get('/qaqc/home', [QaqcHomeController::class, 'index'])->name('qaqc.home');

        Route::post('/save-image-path/{reportId}/{section}', [QaqcReportController::class,'saveImagePath']);
        Route::post('/upload-attachment', [QaqcReportController::class, 'uploadAttachment'])->name('uploadAttachment');
        Route::post('/qaqc/report/{reportId}/autograph/{section}', [QaqcReportController::class, 'storeSignature'])->name('qaqc.report.autograph.store');

        Route::get('/qaqc/reports/', [QaqcReportController::class, 'index'])->name('qaqc.report.index');
        Route::get('/qaqc/report/{id}', [QaqcReportController::class, 'detail'])->name('qaqc.report.detail');
        Route::get('/qaqc/report/{id}/edit',[qaQcReportController::class, 'edit'])->name('qaqc.report.edit');
        Route::put('/qaqc/report/{id}', [QaqcReportController::class, 'update' ])->name('qaqc.report.update');
        Route::get('/qaqc/reports/create', [QaqcReportController::class, 'create'])->name('qaqc.report.create');
        Route::post('/qaqc/reports/', [QaqcReportController::class, 'store'])->name('qaqc.report.store');
        Route::delete('/qaqc/reports/{id}', [QaqcReportController::class, 'destroy'])->name('qaqc.report.delete');
    });

    Route::middleware(['checkDepartment:HRD'])->group(function() {
        Route::get('/hrd/importantdocs/', [ImportantDocController::class, 'index'])->name('hrd.importantDocs');
        Route::get('/hrd/importantdocs/create', [ImportantDocController::class, 'create'])->name('hrd.importantDocs.create');
        Route::post('/hrd/importantdocs/store', [ImportantDocController::class, 'store'])->name('hrd.importantDocs.store');
        Route::get('/hrd/importantdocs/{id}', [ImportantDocController::class, 'detail'])->name('hrd.importantDocs.detail');
        Route::get('/hrd/importantdocs/{id}/edit', [ImportantDocController::class, 'edit'])->name('hrd.importantDocs.edit');
        Route::put('/hrd/importantdocs/{id}', [ImportantDocController::class, 'update'])->name('hrd.importantDocs.update');
        Route::delete('/hrd/importantdocs/{id}', [ImportantDocController::class, 'destroy'])->name('hrd.importantDocs.delete');
    });

    Route::middleware(['checkDepartment:DIREKTUR'])->group(function() {
        Route::get('/director/qaqc/index', [ReportController::class, 'index'])->name('director.qaqc.index');
        Route::get('/director/qaqc/detail/{id}', [ReportController::class, 'detail'])->name('director.qaqc.detail');
        Route::put('/director/qaqc/approve/{id}', [ReportController::class, 'approve'])->name('director.qaqc.approve');
        Route::put('/director/qaqc/reject/{id}', [ReportController::class, 'reject'])->name('director.qaqc.reject');
    });

});

Route::middleware(['checkUserRole:3'])->group(function () {
    Route::get('/user/home', [UserHomeController::class, 'index'])->name('user.home');
});

});

