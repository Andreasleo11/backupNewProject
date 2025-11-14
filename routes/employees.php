<?php

use App\Http\Controllers\Auth\EmployeeLoginController;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest:employee'])->group(function() {
    Route::get('/employee/login', [EmployeeLoginController::class, 'showLoginForm'])->name('employee.login');
    Route::post('/employee/login', [EmployeeLoginController::class, 'login'])->name('employee.login.submit');
});

Route::middleware('auth:employee')->group(function () {
    Route::get('/employee/home', fn() => view('employee.dashboard'));
    Route::post('/employee/logout', [EmployeeLoginController::class, 'logout'])->name('employee.logout');
});