<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\UserHomeController;
use App\Http\Controllers\SuperAdminHomeController;
use App\Http\Controllers\StaffHomeController;

use App\Http\Controllers\ReportHeaderController;
use App\Http\Controllers\ReportDetailController;
use App\Http\Controllers\ReportViewController;

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
    return view('welcome');
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
    
});

Route::middleware(['auth','checkUserRole:2'])->group(function () {
    Route::get('/staff/home', [StaffHomeController::class, 'index'])->name('staff.home');
    Route::get('/userStaff/home', [UserHomeController::class, 'index']);
    Route::post('/upload-autograph/{reportId}/{section}', [ReportViewController::class, 'uploadAutograph']); // SINGLEROUTES susah diduplikat sementara ini
    Route::post('/save-image-path/{reportId}/{section}', [ReportViewController::class,'saveImagePath']);
});

Route::middleware(['checkUserRole:3'])->group(function () {
    Route::get('/user/home', [UserHomeController::class, 'index'])->name('user.home');
});


Route::get('/reports/create/header', [ReportHeaderController::class, 'create'])->name('header.create');
Route::post('/report/store', [ReportHeaderController::class, 'store'])->name('header.store');

Route::get('/reports/view', [ReportViewController::class, 'index'])->name('report.view');
Route::get('/reports/view/details/{id}', [ReportViewController::class, 'detail'])->name('report.detail');
Route::post('/upload-attachment', [ReportViewController::class, 'uploadAtt'])->name('uploadAttachment');

// Route::post('/upload-autograph/{reportId}/{section}', [ReportViewController::class, 'uploadAutograph']);
