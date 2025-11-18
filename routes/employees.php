<?php

use App\Http\Controllers\Auth\EmployeeLoginController;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest:employee'])->group(function() {
    Route::get('login', [EmployeeLoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [EmployeeLoginController::class, 'login'])->name('login.submit');
});

Route::middleware('auth:employee')->group(function () {
    Route::get('home', fn() => view('employee.dashboard'));
    Route::post('logout', [EmployeeLoginController::class, 'logout'])->name('logout');
});