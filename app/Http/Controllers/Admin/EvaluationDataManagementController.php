<?php

/**
 * @deprecated This controller has been removed as part of the P&E Data (Monthly) deprecation.
 *             The monthly Excel-upload pipeline is replaced by live attendance_records queries.
 *             This file is safe to delete once route cache is cleared.
 *
 * @see App\Models\AttendanceRecord
 * @see App\Http\Controllers\EvaluationController
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * @deprecated Stub only — all routes pointing here have been removed.
 */
class EvaluationDataManagementController extends Controller
{
    // Intentionally empty — routes removed from routes/administration.php
}
