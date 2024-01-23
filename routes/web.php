<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\UserHomeController;
use App\Http\Controllers\SuperAdminHomeController;
use App\Http\Controllers\StaffHomeController;

use App\Http\Controllers\ReportHeaderController;
use App\Http\Controllers\ReportDetailController;
use App\Http\Controllers\ReportViewController;

use App\Http\Controllers\hrd\ImportantDocController;

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

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/assign-role-manually', [UserRoleController::class, 'assignRoleToME'])->name('assignRoleManually');

Route::get('/home', function () {
    $user = auth()->user();

    if ($user->role_id == 1) {
        return redirect()->route('superadmin.home');
    }else if ($user->role_id == 2){
        return redirect()->route('staff.home');
    }
    else {
        return redirect()->route('user.home');
    }
})->name('home');


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
    Route::post('/upload-autograph/{reportId}/{section}', [ReportViewController::class, 'uploadAutograph']); // SINGLEROUTES susah diduplikat sementara ini
    Route::post('/save-image-path/{reportId}/{section}', [ReportViewController::class,'saveImagePath']);

    Route::get('/qaqc/reports/create', [ReportHeaderController::class, 'create'])->name('qaqc.header.create');
    Route::post('/qaqc/reports/store', [ReportHeaderController::class, 'store'])->name('qaqc.header.store');
    Route::post('/qaqc/report/{reportId}/autograph/{section}', [ReportViewController::class, 'storeSignature'])->name('qaqc.report.autograph.store');

    Route::get('/hrd/importantdocs/', [ImportantDocController::class, 'index'])->name('hrd.importantDocs');
    Route::get('/hrd/importantdocs/create', [ImportantDocController::class, 'create'])->name('hrd.importantDocs.create');
    Route::post('/hrd/importantdocs/store', [ImportantDocController::class, 'store'])->name('hrd.importantDocs.store');
    Route::get('/hrd/importantdocs/{id}', [ImportantDocController::class, 'detail'])->name('hrd.importantDocs.detail');
    Route::get('/hrd/importantdocs/{id}/edit', [ImportantDocController::class, 'edit'])->name('hrd.importantDocs.edit');
    Route::put('/hrd/importantdocs/{id}', [ImportantDocController::class, 'update'])->name('hrd.importantDocs.update');
    Route::delete('/hrd/importantdocs/{id}', [ImportantDocController::class, 'destroy'])->name('hrd.importantDocs.delete');

});

Route::middleware(['checkUserRole:3'])->group(function () {
    Route::get('/user/home', [UserHomeController::class, 'index'])->name('user.home');
});

Route::get('/qaqc/reports/view', [ReportViewController::class, 'index'])->name('qaqc.report.view');
Route::get('/qaqc/report/view/detail/{id}', [ReportViewController::class, 'detail'])->name('qaqc.report.detail');



// Route::get('/reports/create/header', [ReportHeaderController::class, 'create'])->name('header.create');
// Route::post('/report/store', [ReportHeaderController::class, 'store'])->name('header.store');

// Route::get('/reports/view', [ReportViewController::class, 'index'])->name('report.view');
// Route::get('/reports/view/details/{id}', [ReportViewController::class, 'detail'])->name('report.detail');

