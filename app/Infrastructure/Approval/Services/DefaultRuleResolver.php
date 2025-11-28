<?php

namespace App\Infrastructure\Approval\Services;

use App\Domain\Approval\Contracts\RuleResolver;
use App\Infrastructure\Approval\Models\RuleTemplate;

final class DefaultRuleResolver implements RuleResolver
{
    public function resolveFor(string $modelType, array $context = []): ?RuleTemplate
    {
        $candidates = RuleTemplate::where('model_type', $modelType)
            ->where('active', true)
            ->orderBy('priority') // 1st by priority
            ->get();

        $best = null;
        $bestScore = -1;
        foreach ($candidates as $tpl) {
            $expr = $tpl->match_expr ?? [];
            $score = $this->matches($expr, $context) ? $this->score($expr) : -1;
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $tpl;
            }
        }

        return $best ?? null;
    }

    private function matches(array $expr, array $ctx): bool
    {
        foreach ($expr as $k => $v) {
            if ($k === 'amount_gt' && ! ($ctx['amount'] ?? null) > $v) {
                return false;
            }
            if ($k === 'amount_gte' && ! ($ctx['amount'] ?? null) >= $v) {
                return false;
            }
            if ($k === 'amount_lte' && ! ($ctx['amount'] ?? null) <= $v) {
                return false;
            }
            if ($k === 'any_tags' && isset($ctx['tags'])) {
                if (empty(array_intersect($v, (array) $ctx['tags']))) {
                    return false;
                }
            }
            if (! in_array($k, ['amount_gt', 'amount_gte', 'amount_lte', 'any_tags'])) {
                if (($ctx[$k] ?? null) != $v) {
                    return false;
                }
            }
        }

        return true;
    }

    private function score(array $expr): int
    {
        // Prefer more specific rules (more keys = higher score)
        return count($expr);
    }
}
