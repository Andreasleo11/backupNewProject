<?php

/**
 * @deprecated This DataTable has been removed as part of the P&E Data (Monthly) deprecation.
 *             The monthly Excel-upload pipeline is replaced by live attendance_records queries.
 *             This file is safe to delete once route cache is cleared.
 *
 * @see App\Models\AttendanceRecord
 */

namespace App\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;

/**
 * @deprecated Stub only — controller and routes using this have been removed.
 */
class EvaluationDataManagementDataTable extends DataTable
{
    // Intentionally empty — removed as part of P&E Monthly deprecation.
}
