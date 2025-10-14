<?php

namespace App\Application\Verification\DTOs;

final class ReportData
{
    public ?int $id;

    public string $title;

    public ?string $description;

    /** @var array<string,mixed>|null */
    public ?array $meta;

    public function __construct(
        ?int $id,
        string $title,
        ?string $description = null,
        ?array $meta = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->meta = $meta;
    }

    public static function fromArray(array $a): self
    {
        return new self(
            $a['id'] ?? null,
            (string) ($a['title'] ?? ''),
            $a['description'] ?? null,
            isset($a['meta']) ? (array) $a['meta'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'meta' => $this->meta,
        ];
    }
}
