<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Filter extends Component
{
    public $filterColumn;

    public $filterAction;

    public $filterValue;

    public $itemsPerPage;

    public $columns;

    public $actions;

    public function __construct(
        $filterColumn = null,
        $filterAction = null,
        $filterValue = null,
        $itemsPerPage = null,
        $columns = [],
        $actions = [],
    ) {
        $this->filterColumn = $filterColumn;
        $this->filterAction = $filterAction;
        $this->filterValue = $filterValue;
        $this->itemsPerPage = $itemsPerPage;
        $this->columns = $columns;
        $this->actions = $actions;
    }

    public function render()
    {
        return view('components.filter');
    }
}
