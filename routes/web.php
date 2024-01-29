<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\DirectorHomeController;
use App\Http\Controllers\director\ReportController;
use App\Http\Controllers\HomeControllerQaqc;
use App\Http\Controllers\QaQcReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\UserHomeController;
use App\Http\Controllers\SuperAdminHomeController;
use App\Http\Controllers\StaffHomeController;

use App\Http\Controllers\ReportHeaderController;
use App\Http\Controllers\ReportDetailController;

use App\Http\Controllers\hrd\ImportantDocController;
use Illuminate\Support\Facades\Auth;

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
    return view('auth.login');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/assign-role-manually', [UserRoleController::class, 'assignRoleToME'])->name('assignRoleManually');

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

Route::middleware(['checkUserRole:2'])->group(function () {
    Route::get('/staff/home', [StaffHomeController::class, 'index'])->name('staff.home');
    Route::get('/userStaff/home', [UserHomeController::class, 'index']);

    Route::get('/staff/home', [HomeControllerQaqc::class, 'index'])->name('qaqc.home');
    Route::get('/staff/home', [DirectorHomeController::class, 'index'])->name('director.home');

    Route::post('/save-image-path/{reportId}/{section}', [QaQcReportController::class,'saveImagePath']);
    Route::post('/upload-attachment', [QaQcReportController::class, 'uploadAttachment'])->name('uploadAttachment');
    Route::post('/qaqc/report/{reportId}/autograph/{section}', [QaqcReportController::class, 'storeSignature'])->name('qaqc.report.autograph.store');

    Route::get('/qaqc/reports/', [QaqcReportController::class, 'index'])->name('qaqc.report.index');
    Route::get('/qaqc/report/{id}', [QaqcReportController::class, 'detail'])->name('qaqc.report.detail');
    Route::get('/qaqc/report/{id}/edit',[QaQcReportController::class, 'edit'])->name('qaqc.report.edit');
    Route::put('/qaqc/report/{id}', [QaQcReportController::class, 'update' ])->name('qaqc.report.update');
    Route::get('/qaqc/reports/create', [QaQcReportController::class, 'create'])->name('qaqc.report.create');
    Route::post('/qaqc/reports/', [QaQcReportController::class, 'store'])->name('qaqc.report.store');
    Route::delete('/qaqc/reports/{id}', [QaQcReportController::class, 'destroy'])->name('qaqc.report.delete');

    Route::get('/hrd/importantdocs/', [ImportantDocController::class, 'index'])->name('hrd.importantDocs');
    Route::get('/hrd/importantdocs/create', [ImportantDocController::class, 'create'])->name('hrd.importantDocs.create');
    Route::post('/hrd/importantdocs/store', [ImportantDocController::class, 'store'])->name('hrd.importantDocs.store');
    Route::get('/hrd/importantdocs/{id}', [ImportantDocController::class, 'detail'])->name('hrd.importantDocs.detail');
    Route::get('/hrd/importantdocs/{id}/edit', [ImportantDocController::class, 'edit'])->name('hrd.importantDocs.edit');
    Route::put('/hrd/importantdocs/{id}', [ImportantDocController::class, 'update'])->name('hrd.importantDocs.update');
    Route::delete('/hrd/importantdocs/{id}', [ImportantDocController::class, 'destroy'])->name('hrd.importantDocs.delete');

    Route::get('/direktur/qaqc/index', [ReportController::class, 'index'])->name('direktur.qaqc.index');
    Route::get('/direktur/qaqc/detail/{id}', [ReportController::class, 'detail'])->name('direktur.qaqc.detail');
    Route::put('/direktur/qaqc/approve/{id}', [ReportController::class, 'approve'])->name('direktur.qaqc.approve');
    Route::put('/direktur/qaqc/reject/{id}', [ReportController::class, 'reject'])->name('direktur.qaqc.reject');

});

Route::middleware(['checkUserRole:3'])->group(function () {
    Route::get('/user/home', [UserHomeController::class, 'index'])->name('user.home');
});




// Route::get('/reports/create/header', [ReportHeaderController::class, 'create'])->name('header.create');
// Route::post('/report/store', [ReportHeaderController::class, 'store'])->name('header.store');

// Route::get('/reports/view', [ReportViewController::class, 'index'])->name('report.view');
// Route::get('/reports/view/pakjoni', [ReportViewController::class, 'indexjoni'])->name('report.viewjoni');
// Route::get('/reports/view/details/{id}', [ReportViewController::class, 'detail'])->name('report.detail');
// Route::get('/reports/view/detailspakjoni/{id}', [ReportViewController::class, 'detailjoni'])->name('report.detailjoni');
// Route::post('/approvalpakdjoni/{id}', [ReportViewController::class, 'approvaljoni'])->name('approval.joni');
// Route::post('/upload-attachment', [ReportViewController::class, 'uploadAtt'])->name('uploadAttachment');

// Route::get('/report/edit/{id}',[ReportViewController::class, 'editview'])->name('report.edit');
// Route::put('/report/update/{id}', [ReportViewController::class, 'updateedit' ])->name('report.update');

