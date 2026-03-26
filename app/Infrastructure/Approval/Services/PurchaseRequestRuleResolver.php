<?php

namespace App\infrastructure\Approval\Services;

use App\Domain\Approval\Contracts\RuleResolver;
use App\Infrastructure\Persistence\Eloquent\Models\RuleTemplate;

class PurchaseRequestRuleResolver implements RuleResolver
{
    public function resolveFor(string $modelType, array $context = []): ?RuleTemplate
    {
        // Sekarang masih simple: ambil rule aktif dengan model_type = PR, urut priority
        return RuleTemplate::where('model_type', $modelType)
            ->where('active', true)
            ->orderBy('priority') // priority kecil = lebih spesifik
            ->first();
    }
}
