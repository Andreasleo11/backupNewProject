<?php
declare(strict_types=1);

namespace App\Services\Payroll\Dto;

use Carbon\CarbonImmutable;

final class AttendanceDto
{
    public function __construct(
        public readonly string $nik,
        public readonly CarbonImmutable $shiftDate,
        public readonly int $alpha,
        public readonly int $telat,
        public readonly int $izin,
        public readonly int $sakit,
    ) {}

    public static function fromApi(array $r): self
    {
        $date = CarbonImmutable::createFromFormat('d/m/Y', $r['ShiftDate']);
        $sakit = (int)($r['OP'] ?? 0) + (int)($r['HOS'] ?? 0) + (int)($r['WA'] ?? 0) + (int)($r['HOSWA'] ?? 0);

        return new self(
            nik: (string)($r['NIK'] ?? ''),
            shiftDate: $date->startOfDay(),
            alpha: (int)($r['ABS'] ?? 0),
            telat: (int)($r['LT']  ?? 0),
            izin:  (int)($r['CT']  ?? 0),
            sakit: $sakit,
        );
    }
}
