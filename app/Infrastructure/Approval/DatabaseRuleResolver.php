<?php

namespace App\Infrastructure\Approval;

use App\Domain\Approval\Contracts\RuleResolver;
use App\Infrastructure\Persistence\Eloquent\Models\RuleTemplate;

class DatabaseRuleResolver implements RuleResolver
{
    /**
     * $modelType   : class string, contoh: App\Models\PurchaseRequest::class
     * $context     : data tambahan untuk matching, contoh:
     *                [
     *                  'from_department' => 'MOULDING',
     *                  'to_department'   => 'PURCHASING',
     *                  'at_office'       => false,
     *                  'is_design'       => true,
     *                ]
     */
    public function resolveFor(string $modelType, array $context = []): ?RuleTemplate
    {
        // 1) Ambil semua rule aktif untuk modelType, urut priority ascending
        $rules = RuleTemplate::query()
            ->where('model_type', $modelType)
            ->where('active', true)
            ->orderBy('priority', 'asc')
            ->get();

        // 2) Ambil rule pertama yang match dengan context
        foreach ($rules as $rule) {
            if ($this->matches($rule, $context)) {
                return $rule;
            }
        }

        // 3) Kalau tidak ada yang match sama sekali
        return null;
    }

    /**
     * Tentukan apakah satu RuleTemplate match dengan context:
     * - Kalau match_expr null => selalu true
     * - Kalau format JSON tidak valid => untuk sekarang kita anggap false (atau bisa dibikin true kalau mau)
     */
    protected function matches(RuleTemplate $rule, array $context): bool
    {
        $exprRaw = $rule->match_expr;

        // Tidak ada ekspresi => always match
        if ($exprRaw === null || $exprRaw === '') {
            return true;
        }

        $expr = json_decode($exprRaw, true);

        if (! is_array($expr)) {
            // Format salah / tidak bisa di-decode
            // Bisa pilih: return false; atau true;
            // Di sini kita buat false supaya rule rusak tidak kepakai.
            return false;
        }

        // ---------------------------------------------------
        // 1) from_department_in
        // ---------------------------------------------------
        if (isset($expr['from_department_in']) && isset($context['from_department'])) {
            $allowed = (array) $expr['from_department_in'];
            if (! in_array($context['from_department'], $allowed, true)) {
                return false;
            }
        }

        // ---------------------------------------------------
        // 2) from_department_not_in
        // ---------------------------------------------------
        if (isset($expr['from_department_not_in']) && isset($context['from_department'])) {
            $blocked = (array) $expr['from_department_not_in'];
            if (in_array($context['from_department'], $blocked, true)) {
                return false;
            }
        }

        // ---------------------------------------------------
        // 3) to_department_in
        // ---------------------------------------------------
        if (isset($expr['to_department_in']) && isset($context['to_department'])) {
            $allowed = (array) $expr['to_department_in'];
            if (! in_array($context['to_department'], $allowed, true)) {
                return false;
            }
        }

        // ---------------------------------------------------
        // 4) at_office (true/false)
        // ---------------------------------------------------
        if (array_key_exists('at_office', $expr) && array_key_exists('at_office', $context)) {
            if ((bool) $context['at_office'] !== (bool) $expr['at_office']) {
                return false;
            }
        }

        // ---------------------------------------------------
        // 5) is_design (true/false)
        // ---------------------------------------------------
        if (array_key_exists('is_design', $expr) && array_key_exists('is_design', $context)) {
            if ((bool) $context['is_design'] !== (bool) $expr['is_design']) {
                return false;
            }
        }

        // TODO (optional): tambahkan branch_in, etc kalau diperlukan

        // Kalau semua check lulus => match
        return true;
    }
}
