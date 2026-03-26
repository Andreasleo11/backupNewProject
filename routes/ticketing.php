<?php

use App\Livewire\Ticketing\Admin\KpiDashboard as AdminKpiDashboard;
use App\Livewire\Ticketing\TicketDashboard;
use App\Livewire\Ticketing\TicketDetail;
use App\Livewire\Ticketing\TicketList;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    // IT Core Routing
    Route::get('/ticketing', TicketList::class)->name('ticketing.list');
    Route::get('/ticketing/dashboard', TicketDashboard::class)->name('ticketing.dashboard');
    Route::get('/ticketing/{ticket}', TicketDetail::class)->name('ticketing.show');

    // Admin Level Routes
    Route::get('/admin/ticketing/kpi', AdminKpiDashboard::class)->name('ticketing.admin.kpi');
});
