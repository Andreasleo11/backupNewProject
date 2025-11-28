<?php

namespace App\Application\Verification\DTOs;

final class ReportData
{
    public ?int $id;

    public ?string $rec_date;     // 'Y-m-d'

    public ?string $verify_date;  // 'Y-m-d'

    public ?string $customer;

    public ?string $invoice_number;

    /** @var array<string,mixed>|null */
    public ?array $meta;

    public function __construct(
        ?int $id,
        ?string $rec_date,
        ?string $verify_date,
        ?string $customer,
        ?string $invoice_number,
        ?array $meta = null
    ) {
        $this->id = $id;
        $this->rec_date = $rec_date;
        $this->verify_date = $verify_date;
        $this->customer = $customer;
        $this->invoice_number = $invoice_number;
        $this->meta = $meta;
    }

    public static function fromArray(array $a): self
    {
        return new self(
            $a['id'] ?? null,
            $a['rec_date'] ?? null,
            $a['verify_date'] ?? null,
            $a['customer'] ?? null,
            $a['invoice_number'] ?? null,
            isset($a['meta']) ? (array) $a['meta'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'rec_date' => $this->rec_date,
            'verify_date' => $this->verify_date,
            'customer' => $this->customer,
            'invoice_number' => $this->invoice_number,
            'meta' => $this->meta,
        ];
    }
}
