<?php

use App\Livewire\Locker\LockerAuditLog;
use App\Livewire\Locker\LockerDashboard;
use App\Livewire\Locker\LockerManager;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'can:employee.view-any'])->group(function () {
    Route::get('/lockers', LockerDashboard::class)->name('lockers.dashboard');
    Route::get('/lockers/manage', LockerManager::class)->name('lockers.manage');
    Route::get('/lockers/audit', LockerAuditLog::class)->name('lockers.audit');
});
