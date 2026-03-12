<?php

use App\Http\Controllers\RequirementUploadDownloadController;
use App\Livewire\Admin\RequirementUploads\Review as ReviewUploads;
use App\Livewire\Compliance\Dashboard as ComplianceDashboard;
use App\Livewire\Departments\Compliance as DeptCompliance;
use App\Livewire\Departments\Overview as DepartmentsOverview;
use App\Livewire\Requirements\Assign as ReqAssign;
use App\Livewire\Requirements\Departments as RequirementDepartments;
use App\Livewire\Requirements\Form as RequirementForm;
use App\Livewire\Requirements\Index as ReqIndex;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Compliance & Documentation Routes
|--------------------------------------------------------------------------
|
| Routes for managing compliance requirements, department compliance tracking,
| requirement uploads, and documentation management.
|
| RECOMMENDED PERMISSIONS:
| - compliance.view-dashboard
| - compliance.manage-requirements
| - compliance.review-uploads
| - compliance.view-departments
|
| RECOMMENDED ROLES: admin, super-admin, compliance, quality, manager
|
*/

Route::middleware('auth')->group(function () {
    // Compliance Dashboard
    Route::get('/compliance/dashboard', ComplianceDashboard::class)->name('compliance.dashboard');

    // Department Compliance
    Route::get('/departments', DepartmentsOverview::class)->name('departments.index');
    Route::get('/departments/{department}/compliance', DeptCompliance::class)->name('departments.compliance');

    // Requirements
    Route::get('/requirements', ReqIndex::class)->name('requirements.index');
    Route::get('/requirements/create', RequirementForm::class)->name('requirements.create');
    Route::get('/requirements/{requirement}/edit', RequirementForm::class)->name('requirements.edit');
    Route::get('/requirements/assign', ReqAssign::class)->name('requirements.assign');
    Route::get('/requirements/{requirement}/departments', RequirementDepartments::class)->name('requirements.departments');

    // Requirement Uploads
    Route::get('/requirement-uploads/review', ReviewUploads::class)->name('requirement-uploads.review');
    Route::get('/requirement-uploads/{upload}/download', [RequirementUploadDownloadController::class, 'show'])
        ->name('uploads.download')
        ->middleware('signed');
});
