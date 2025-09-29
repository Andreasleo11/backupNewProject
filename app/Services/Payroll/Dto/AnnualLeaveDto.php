<?php

declare(strict_types=1);

namespace App\Services\Payroll\Dto;

final class AnnualLeaveDto
{
    public function __construct(public readonly string $nik, public readonly ?int $remain) {}

    public static function fromApi(array $r): self
    {
        return new self(
            nik: (string) ($r['NIK'] ?? ''),
            remain: isset($r['Remain']) ? (int) $r['Remain'] : null,
        );
    }
}
