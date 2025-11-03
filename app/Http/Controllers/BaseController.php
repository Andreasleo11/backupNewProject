<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    protected function applyFilters(Builder $query, Request $request, array $validColumns)
    {
        $column = $request->input('filterColumn');
        $action = $request->input('filterAction');
        $value = $request->input('filterValue');

        if (in_array($column, $validColumns)) {
            switch ($action) {
                case 'equals':
                    $query->where($column, $value);
                    break;
                case 'contains':
                    $query->where($column, 'LIKE', "%{$value}%");
                    break;
                case 'startswith':
                    $query->where($column, 'LIKE', "{$value}%");
                    break;
                case 'endswith':
                    $query->where($column, 'LIKE', "%{$value}");
                    break;
            }
        }

        return $query;
    }
}
