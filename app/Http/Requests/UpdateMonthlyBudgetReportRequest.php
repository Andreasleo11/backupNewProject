<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMonthlyBudgetReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'dept_no' => [
                'required',
                Rule::exists('departments', 'dept_no')
            ],
            'report_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',
            'items.*.name' => 'required|string|max:255',
            'items.*.uom' => 'required|string|max:10',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.remark' => 'nullable|string|max:255',
        ];
    }
}
