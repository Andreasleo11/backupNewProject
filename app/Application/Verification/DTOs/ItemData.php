<?php

namespace App\Application\Verification\DTOs;

final class ItemData
{
    public ?int $id;

    public string $part_name;

    public float $rec_quantity;

    public float $verify_quantity;

    public float $can_use;

    public float $cant_use;

    public float $price;

    public string $currency;

    public array $defects = [];

    public function __construct(
        ?int $id,
        string $part_name,
        float $rec_quantity,
        float $verify_quantity,
        float $can_use,
        float $cant_use,
        float $price,
        string $currency = 'IDR',
        array $defects = [],
    ) {
        $this->id = $id;
        $this->part_name = $part_name;
        $this->rec_quantity = $rec_quantity;
        $this->verify_quantity = $verify_quantity;
        $this->can_use = $can_use;
        $this->cant_use = $cant_use;
        $this->price = $price;
        $this->currency = $currency;
        $this->defects = $defects;

    }

    public static function fromArray(array $a): self
    {
        return new self(
            $a['id'] ?? null,
            (string) ($a['part_name'] ?? ''),
            (float) ($a['rec_quantity'] ?? 0),
            (float) ($a['verify_quantity'] ?? 0),
            (float) ($a['can_use'] ?? 0),
            (float) ($a['cant_use'] ?? 0),
            (float) ($a['price'] ?? 0),
            (string) ($a['currency'] ?? 'IDR'),
            array_map(
                fn ($d) => DefectData::fromArray((array) $d), (array) $a['defects'] ?? []
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'part_name' => $this->part_name,
            'rec_quantity' => $this->rec_quantity,
            'verify_quantity' => $this->verify_quantity,
            'can_use' => $this->can_use,
            'cant_use' => $this->cant_use,
            'price' => $this->price,
            'currency' => $this->currency,
            'defects' => array_map(
                fn ($d) => $d instanceof DefectData ? $d->toArray() : [], $this->defects
            ),
        ];
    }
}
