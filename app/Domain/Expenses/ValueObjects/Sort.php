<?php

namespace App\Domain\Expenses\ValueObjects;

/**
 * Framework-agnostic sort value object.
 * - Validates direction (asc/desc)
 * - Resolves the requested field against an allowed map/list
 * - Keeps domain free of Query/DB types
 */
final class Sort
{
    /** @var string */
    private $field;

    /** @var string asc|desc */
    private $direction;

    private function __construct(string $field, string $direction)
    {
        $this->field = $field;
        $this->direction = $direction;
    }

    /**
     * Build from user input.
     *
     * @param  string|null  $field  Requested field (e.g. "expense_date")
     * @param  string|null  $dir  "asc"|"desc" (anything else => "asc")
     * @param  array  $allowed  Either:
     *                          - ['expense_date','item_name','line_total']  (list)
     *                          - ['date' => 'expense_date', 'item' => 'item_name'] (alias map)
     * @param  string  $defaultField  Fallback field if invalid
     * @param  string  $defaultDir  "asc" | "desc"
     */
    public static function fromInput(
        ?string $field,
        ?string $dir,
        array $allowed,
        string $defaultField,
        string $defaultDir = 'asc'
    ): self {
        $direction = strtolower((string) $dir) === 'desc' ? 'desc' : 'asc';

        // Normalize allowed into a [alias => column] map
        $map = [];
        foreach ($allowed as $k => $v) {
            if (is_int($k)) {
                // plain list: value is both alias and column
                $map[$v] = $v;
            } else {
                // alias map: key is alias, value is column
                $map[$k] = $v;
            }
        }

        $requested = (string) $field;
        $column = $map[$requested]
            ?? $map[$defaultField]
            ?? reset($map); // first allowed as last resort

        return new self($column, $direction);
    }

    public function column(): string
    {
        return $this->field;
    }

    public function direction(): string
    {
        return $this->direction;
    }

    /**
     * Convenience to apply ordering in Infrastructure code:
     * $sort->apply(function($col,$dir){ $q->orderBy($col,$dir); });
     */
    public function apply(callable $orderBy): void
    {
        $orderBy($this->field, $this->direction);
    }
}
