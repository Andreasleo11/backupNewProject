<?php

namespace App\Services;

class PeriodValidator
{
    /** @return array<string, array<int>> [section => [1,3]] */
    public static function missing(array $sectionData): array
    {
        $sections = [
            'details' => $sectionData['details'] ?? [],
            'first inspections' => $sectionData['first_inspections'] ?? [],
            'seconds inspections' => $sectionData['second_inspections'] ?? [],
            'samples' => $sectionData['samples'] ?? [],
            'packagings' => $sectionData['packagings'] ?? [],
            'judgements' => $sectionData['judgements'] ?? [],
            'problems' => $sectionData['problems'] ?? [],
            'quantities' => $sectionData['quantities'] ?? [],
        ];

        $missing = [];
        foreach ($sections as $label => $data) {
            foreach (range(1, 4) as $p) {
                $key = "p$p";
                if (!array_key_exists($key, $data) || empty($data[$key])) {
                    $missing[$label][] = $p;
                }
            }
        }
        return $missing;    //  empty array => everything is complete
    }
}
