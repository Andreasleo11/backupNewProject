<?php

namespace App\Domain\Approval\Contracts;

use App\Infrastructure\Persistence\Eloquent\Models\RuleTemplate;

interface RuleResolver
{
    public function resolveFor(string $modelType, array $context = []): ?RuleTemplate;
}
 