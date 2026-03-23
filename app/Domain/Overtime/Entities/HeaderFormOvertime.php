<?php

declare(strict_types=1);

namespace App\Domain\Overtime\Entities;

/**
 * DDD Entity: HeaderFormOvertime
 *
 * This class lives in the Domain layer. It extends the App\Models version,
 * which is the Eloquent persistence mechanism. The separation allows domain
 * services to import from App\Domain\Overtime\Entities\* without breaking
 * existing code that still references App\Models\HeaderFormOvertime.
 *
 * Migration path:
 *   Phase A (current): Domain services use this class; App\Models copy is the Eloquent source of truth.
 *   Phase B (future):  Move all Eloquent setup here; App\Models version becomes a thin alias.
 */
class HeaderFormOvertime extends \App\Models\HeaderFormOvertime
{
    // No overrides needed yet — inherits full Eloquent behaviour, Approvable contract,
    // relationships, currentStep(), nextStep(), and all fillable/casts from the parent.
}
