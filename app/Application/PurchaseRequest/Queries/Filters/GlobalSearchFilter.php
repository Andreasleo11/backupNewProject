<?php

namespace App\Application\PurchaseRequest\Queries\Filters;

use Illuminate\Database\Eloquent\Builder;

class GlobalSearchFilter implements PurchaseRequestFilter
{
    public function __construct(
        private readonly string $term
    ) {}

    public function apply(Builder $query): void
    {
        if (empty($this->term)) {
            return;
        }

        $query->where(function ($q) {
            $q->where('pr_no', 'like', "%{$this->term}%")
              ->orWhere('doc_num', 'like', "%{$this->term}%")
              ->orWhere('supplier', 'like', "%{$this->term}%")
              ->orWhere('po_number', 'like', "%{$this->term}%")
              ->orWhere('from_department', 'like', "%{$this->term}%")
              ->orWhereHas('createdBy', function ($sub) {
                  $sub->where('name', 'like', "%{$this->term}%");
              });
        });
    }
}
