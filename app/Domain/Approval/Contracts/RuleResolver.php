<?php

namespace App\Domain\Approval\Contracts;

use App\Infrastructure\Approval\Models\RuleTemplate;

interface RuleResolver
{
    public function resolveFor(string $modelType, array $context = []): ?RuleTemplate;
}
