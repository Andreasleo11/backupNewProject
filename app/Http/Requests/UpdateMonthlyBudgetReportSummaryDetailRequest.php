<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMonthlyBudgetReportSummaryDetailRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'uom' => 'required|string|max:255',
            'supplier' => 'required|string|max:255',
            'cost_per_unit' => 'nullable|string', // Validate as string to allow non-numeric input
            'remark' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get the custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'quantity.required' => 'The quantity is required.',
            'quantity.integer' => 'The quantity must be an integer.',
            'uom.required' => 'The unit of measure is required.',
            'uom.string' => 'The unit of measure must be a string.',
            'uom.max' => 'The unit of measure may not be greater than 255 characters.',
            'supplier.required' => 'The supplier is required.',
            'supplier.string' => 'The supplier must be a string.',
            'supplier.max' => 'The supplier may not be greater than 255 characters.',
            'cost_per_unit.numeric' => 'The cost per unit must be a valid number.',
            'remark.string' => 'The remark must be a string.',
            'remark.max' => 'The remark may not be greater than 255 characters.',
        ];
    }
}
