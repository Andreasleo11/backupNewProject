<?php

use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\Auth\PasswordChangeController;
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

use App\Http\Controllers\PEController;

use App\Http\Controllers\PurchasingController;

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
    if(Auth::check()){
        return redirect('/home');
    }
    return view('auth.login');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/assign-role-manually', [UserRoleController::class, 'assignRoleToME'])->name('assignRoleManually');

// routes/web.php

Route::get('/change-password', [PasswordChangeController::class,'showChangePasswordForm'])->name('change.password.show');
Route::post('/change-password', [PasswordChangeController::class, 'changePassword'])->name('change.password');

Route::middleware(['checkUserRole:1', 'checkSession'])->group(function () {
    Route::get('/superadmin/home', [SuperAdminHomeController::class, 'index'])->name('superadmin.home');
    Route::get('/userSA/home', [UserHomeController::class, 'index']);
    Route::prefix('superadmin')->group(function () {

        Route::name('superadmin.')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
            Route::put('/users/create/{id}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/users/create/{id}', [UserController::class, 'destroy'])->name('users.delete');

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

Route::middleware(['checkUserRole:2', 'checkSession',])->group(function () {

    Route::get('/director/home', [DirectorHomeController::class, 'index'])->name('director.home');
    Route::get('/hrd/home', [HrdHomeController::class, 'index'])->name('hrd.home');

    Route::middleware(['checkDepartment:QA,QC'])->group(function () {
        Route::get('/qaqc/home', [QaqcHomeController::class, 'index'])->name('qaqc.home');

        Route::post('/save-image-path/{reportId}/{section}', [QaqcReportController::class,'saveImagePath']);
        Route::post('/upload-attachment', [QaqcReportController::class, 'uploadAttachment'])->name('uploadAttachment');
        Route::post('/qaqc/report/{reportId}/autograph/{section}', [QaqcReportController::class, 'storeSignature'])->name('qaqc.report.autograph.store');

        Route::get('/qaqc/reports/', [QaqcReportController::class, 'index'])->name('qaqc.report.index');
        Route::get('/qaqc/report/{id}', [QaqcReportController::class, 'detail'])->name('qaqc.report.detail');
        Route::get('/qaqc/report/{id}/edit',[QaQcReportController::class, 'edit'])->name('qaqc.report.edit');
        Route::put('/qaqc/report/{id}', [QaqcReportController::class, 'update' ])->name('qaqc.report.update');
        Route::get('/qaqc/reports/create', [QaqcReportController::class, 'create'])->name('qaqc.report.create');
        Route::post('/qaqc/reports/', [QaqcReportController::class, 'store'])->name('qaqc.report.store');
        Route::delete('/qaqc/reports/{id}', [QaqcReportController::class, 'destroy'])->name('qaqc.report.delete');
        Route::get('/qaqc/reports/{id}/download', [QaqcReportController::class, 'exportToPdf'])->name('qaqc.report.download');
        Route::get('qaqc/{id}/previewPdf', [QaqcReportController::class, 'previewPdf'])->name('qaqc.report.previewPdf');
    });

    Route::middleware(['checkDepartment:HRD'])->group(function() {
        Route::get('/hrd/importantdocs/', [ImportantDocController::class, 'index'])->name('hrd.importantDocs.index');
        Route::get('/hrd/importantdocs/create', [ImportantDocController::class, 'create'])->name('hrd.importantDocs.create');
        Route::post('/hrd/importantdocs/store', [ImportantDocController::class, 'store'])->name('hrd.importantDocs.store');
        Route::get('/hrd/importantdocs/{id}', [ImportantDocController::class, 'detail'])->name('hrd.importantDocs.detail');
        Route::get('/hrd/importantdocs/{id}/edit', [ImportantDocController::class, 'edit'])->name('hrd.importantDocs.edit');
        Route::put('/hrd/importantdocs/{id}', [ImportantDocController::class, 'update'])->name('hrd.importantDocs.update');
        Route::delete('/hrd/importantdocs/{id}', [ImportantDocController::class, 'destroy'])->name('hrd.importantDocs.delete');
        Route::get('/download/{file}', [ImportantDocController::class, 'downloadFile'])->name('downloadFile');
        // Route::get('/hrd/importantdocs/{file}', [ImportantDocController::class, 'previewPdf'])->name('hrd.importantDocs.previewPdf');
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

// Route::post('/upload-autograph/{reportId}/{section}', [ReportViewController::class, 'uploadAutograph']);

Route::get('/purchasing', [PurchasingController::class, 'index'])->name('purchasing.landing');

Route::get('/pe', [PEController::class, 'index'])->name('pe.landing');
Route::get('/pe/trialinput', [PEController::class, 'trialinput'])->name('pe.trial');
Route::post('/pe/trialfinish', [PEController::class, 'input'])->name('pe.input');

Route::get('/pe/listformrequest', [PEController::class, 'view'])->name('pe.formlist');

Route::get('/pe/listformrequest/detail/{id}', [PEController::class, 'detail'])->name('trial.detail');

Route::post('/pe/listformrequest/detai/updateTonage/{id}', [PEController::class, 'updateTonage'])->name('update.tonage');

