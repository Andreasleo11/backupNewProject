<?php

declare(strict_types=1);

namespace App\Domain\Overtime\Entities;

/**
 * DDD Entity: DetailFormOvertime
 *
 * @see \App\Models\DetailFormOvertime for the Eloquent source of truth.
 */
class DetailFormOvertime extends \App\Models\DetailFormOvertime
{
    // Inherits all relationships (employee, header, actualOvertimeDetail),
    // fillable columns, and casts from the parent.
}
