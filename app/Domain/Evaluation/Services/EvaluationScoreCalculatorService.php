<?php

namespace App\Domain\Evaluation\Services;

use App\Models\EvaluationData;

class EvaluationScoreCalculatorService
{
    /**
     * Score mapping for NEW evaluation criteria (Yayasan/Magang).
     */
    private const NEW_SCORE_MAPS = [
        'kemampuan_kerja' => ['A' => 17, 'B' => 14, 'C' => 11, 'D' => 8, 'E' => 0],
        'kecerdasan_kerja' => ['A' => 16, 'B' => 13, 'C' => 10, 'D' => 7, 'E' => 0],
        'qualitas_kerja' => ['A' => 11, 'B' => 9, 'C' => 7, 'D' => 4, 'E' => 0],
        'disiplin_kerja' => ['A' => 8, 'B' => 6, 'C' => 5, 'D' => 3, 'E' => 0],
        'kepatuhan_kerja' => ['A' => 10, 'B' => 8, 'C' => 6, 'D' => 4, 'E' => 0],
        'lembur' => ['A' => 10, 'B' => 8, 'C' => 6, 'D' => 4, 'E' => 0],
        'efektifitas_kerja' => ['A' => 10, 'B' => 8, 'C' => 6, 'D' => 4, 'E' => 0],
        'relawan' => ['A' => 10, 'B' => 8, 'C' => 6, 'D' => 4, 'E' => 0],
        'integritas' => ['A' => 8, 'B' => 6, 'C' => 5, 'D' => 3, 'E' => 0],
    ];

    /**
     * Score mapping for OLD evaluation criteria (Regular).
     */
    private const OLD_SCORE_MAPS = [
        'kerajinan_kerja' => ['A' => 10, 'B' => 7.5, 'C' => 5, 'D' => 2.5, 'E' => 0],
        'kerapian_kerja' => ['A' => 10, 'B' => 7.5, 'C' => 5, 'D' => 2.5, 'E' => 0],
        'prestasi' => ['A' => 20, 'B' => 15, 'C' => 10, 'D' => 5, 'E' => 0],
        'loyalitas' => ['A' => 10, 'B' => 7.5, 'C' => 5, 'D' => 2.5, 'E' => 0],
        'perilaku_kerja' => ['A' => 10, 'B' => 7.5, 'C' => 5, 'D' => 2.5, 'E' => 0],
    ];

    /**
     * Penalty multipliers for attendance issues.
     */
    private const PENALTIES = [
        'alpha' => 10,
        'izin' => 2,
        'sakit' => 1,
        'telat' => 0.5,
    ];

    /**
     * Calculate total score using NEW scoring system (Yayasan/Magang).
     */
    public function calculateTotal(array $scores, EvaluationData $evaluation): float
    {
        $total = 0;

        foreach ($scores as $field => $value) {
            if (isset(self::NEW_SCORE_MAPS[$field][$value])) {
                $total += self::NEW_SCORE_MAPS[$field][$value];
            }
        }

        $total -= $this->calculatePenalties($evaluation);

        return $total;
    }

    /**
     * Calculate total score using OLD scoring system (Regular).
     */
    public function calculateTotalOld(array $scores, EvaluationData $evaluation): float
    {
        // Base score starts at 40
        $total = 40;

        foreach ($scores as $field => $value) {
            if (isset(self::OLD_SCORE_MAPS[$field][$value])) {
                $total += self::OLD_SCORE_MAPS[$field][$value];
            }
        }

        $total -= $this->calculatePenalties($evaluation);

        return $total;
    }

    /**
     * Calculate penalties from attendance issues.
     */
    private function calculatePenalties(EvaluationData $evaluation): float
    {
        return ($evaluation->Alpha ?? 0) * self::PENALTIES['alpha']
            + ($evaluation->Izin ?? 0) * self::PENALTIES['izin']
            + ($evaluation->Sakit ?? 0) * self::PENALTIES['sakit']
            + ($evaluation->Telat ?? 0) * self::PENALTIES['telat'];
    }

    /**
     * Get the list of NEW evaluation fields that are scored.
     */
    public function getScoredFields(): array
    {
        return array_keys(self::NEW_SCORE_MAPS);
    }

    /**
     * Get the list of OLD evaluation fields that are scored.
     */
    public function getOldScoredFields(): array
    {
        return array_keys(self::OLD_SCORE_MAPS);
    }

    // ── Public static accessors (used by blade for synced legends/formulas) ──

    /** Full NEW scoring map: field => [grade => points]. */
    public static function getScoreMaps(): array
    {
        return self::NEW_SCORE_MAPS;
    }

    /** Full OLD scoring map (Regular): field => [grade => points]. */
    public static function getOldScoreMaps(): array
    {
        return self::OLD_SCORE_MAPS;
    }

    /** Penalty multipliers per absence type. */
    public static function getPenalties(): array
    {
        return self::PENALTIES;
    }
}
