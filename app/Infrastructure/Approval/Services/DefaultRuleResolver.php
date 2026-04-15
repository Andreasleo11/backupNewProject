<?php

namespace App\Infrastructure\Approval\Services;

use App\Domain\Approval\Contracts\RuleResolver;
use App\Infrastructure\Persistence\Eloquent\Models\RuleTemplate;

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
            if ($k === 'amount_gt' && ! (($ctx['amount'] ?? null) > $v)) {
                return false;
            }
            if ($k === 'amount_gte' && ! (($ctx['amount'] ?? null) >= $v)) {
                return false;
            }
            if ($k === 'amount_lte' && ! (($ctx['amount'] ?? null) <= $v)) {
                return false;
            }
            if ($k === 'any_tags' && isset($ctx['tags'])) {
                if (empty(array_intersect($v, (array) $ctx['tags']))) {
                    return false;
                }
            }

            if (str_ends_with($k, '_in')) {
                $baseKey = substr($k, 0, -3);
                $ctxVal = $ctx[$baseKey] ?? null;
                $allowedValues = (array) $v;

                if (is_string($ctxVal)) {
                    $normalizedAllowedValues = array_map(fn ($val) => is_string($val) ? strtoupper((string) $val) : $val, $allowedValues);
                    if (! in_array(strtoupper((string) $ctxVal), $normalizedAllowedValues, true)) {
                        return false;
                    }
                } else {
                    if (! in_array($ctxVal, $allowedValues)) {
                        return false;
                    }
                }
                continue;
            }

            if (str_ends_with($k, '_not_in')) {
                $baseKey = substr($k, 0, -7);
                $ctxVal = $ctx[$baseKey] ?? null;
                $blockedValues = (array) $v;

                if (is_string($ctxVal)) {
                    $normalizedBlockedValues = array_map(fn ($val) => is_string($val) ? strtoupper((string) $val) : $val, $blockedValues);
                    if (in_array(strtoupper((string) $ctxVal), $normalizedBlockedValues, true)) {
                        return false;
                    }
                } else {
                    if (in_array($ctxVal, $blockedValues)) {
                        return false;
                    }
                }
                continue;
            }

            if (! in_array($k, ['amount_gt', 'amount_gte', 'amount_lte', 'any_tags'])) {
                $ctxVal = $ctx[$k] ?? null;
                if (is_string($ctxVal) && is_string($v)) {
                    if (strtoupper((string) $ctxVal) != strtoupper((string) $v)) {
                        return false;
                    }
                } else {
                    if ($ctxVal != $v) {
                        return false;
                    }
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
