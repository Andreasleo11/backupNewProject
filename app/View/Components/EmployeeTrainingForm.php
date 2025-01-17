<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class EmployeeTrainingForm extends Component
{
    public $action;
    public $method;
    public $employees;
    public $employeeId;
    public $description;
    public $lastTrainingAt;
    public $evaluated;
    public $submitLabel;

    /**
     * Create a new component instance.
     */
    public function __construct(
        $action,
        $method = 'POST',
        $employees = [],
        $employeeId = null,
        $description = null,
        $lastTrainingAt = null,
        $evaluated = null,
        $submitLabel = 'Save'
    ) {
        $this->action = $action;
        $this->method = $method;
        $this->employees = $employees;
        $this->employeeId = $employeeId;
        $this->description = $description;
        $this->lastTrainingAt = $lastTrainingAt;
        $this->evaluated = $evaluated;
        $this->submitLabel = $submitLabel;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.employee-training-form');
    }
}
