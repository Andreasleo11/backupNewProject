<?php

namespace App\Domain\Expenses\ValueObjects;

final readonly class Month
{
    public function __construct(public string $ym)
    {
        // expect 'YYYY-MM'; validate lightly
        if (! preg_match('/^\d{4}-\d{2}$/', $ym)) {
            throw new \InvalidArgumentException('Invalid month');
        }
    }

    public function start(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->ym.'-01 00:00:00');
    }

    public function end(): \DateTimeImmutable
    {
        return (new \DateTimeImmutable($this->ym.'-01'))->modify('last day of this month 23:59:59');
    }
}
