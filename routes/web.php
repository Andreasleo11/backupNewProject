<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NavPinController;
use App\Livewire\Auth\ChangePasswordPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

// Root redirect - authenticated users go to home, guests go to login
Route::get('/', fn () => Auth::check() ? redirect()->intended('/home') : redirect()->intended(route('login')))->name('/');

// Laravel Authentication Routes
Auth::routes();

// Core Application Routes
Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/account/security', ChangePasswordPage::class)->name('account.security');

    // Quick Access Pin/Unpin API
    Route::prefix('nav')->name('nav.')->group(function () {
        Route::post('/pin', [NavPinController::class, 'pin'])->name('pin');
        Route::delete('/pin', [NavPinController::class, 'unpin'])->name('unpin');
    });
});

Route::post('file/upload', [FileController::class, 'upload'])->name('file.upload');
Route::delete('files/{id}', [FileController::class, 'destroy'])->name('file.destroy');

/*
|--------------------------------------------------------------------------
| Feature-Based Modular Routes
|--------------------------------------------------------------------------
|
| Routes are organized by business feature/domain in separate files.
| Each file contains routes related to a specific functional area.
|
*/

// Administration & Management
require __DIR__ . '/administration.php';

// Inventory & Assets
require __DIR__ . '/inventory.php';

// Quality Control
require __DIR__ . '/quality.php';

// Production
require __DIR__ . '/production.php';

// Procurement
require __DIR__ . '/procurement.php';

// Finance & Accounting
require __DIR__ . '/finance.php';

// Operations
require __DIR__ . '/operations.php';

// HR & Employee Management
require __DIR__ . '/hr.php';

// Performance & Evaluation
require __DIR__ . '/performance.php';

// Compliance & Documentation
require __DIR__ . '/compliance.php';

// Department-Specific Home Pages
require __DIR__ . '/departments.php';

// Master Data Management
require __DIR__ . '/master-data.php';

// Legacy & Utility Routes
require __DIR__ . '/legacy.php';

require __DIR__ . '/ticketing.php';