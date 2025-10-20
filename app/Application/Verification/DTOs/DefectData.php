<?php

namespace App\Application\Verification\DTOs;

final class DefectData
{
    public ?int $id;

    public ?string $code;

    public string $name;

    public string $severity; // LOW|MEDIUM|HIGH

    public string $source; // CUSTOMER|DAIJO|SUPPLIER

    public float $quantity;

    public ?string $notes;

    public function __construct(
        ?int $id,
        ?string $code,
        string $name,
        string $severity,
        string $source,
        float $quantity,
        ?string $notes
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->name = $name;
        $this->severity = $severity;
        $this->source = $source;
        $this->quantity = $quantity;
        $this->notes = $notes;
    }

    public static function fromArray(array $a): self
    {
        return new self(
            $a['id'] ?? null,
            $a['code'] ?? null,
            (string) ($a['name'] ?? ''),
            (string) ($a['severity'] ?? 'LOW'),
            (string) ($a['source'] ?? 'DAIJO'),
            (float) ($a['quantity'] ?? 0),
            $a['notes'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'severity' => $this->severity,
            'source' => $this->source,
            'quantity' => $this->quantity,
            'notes' => $this->notes,
        ];
    }
}
