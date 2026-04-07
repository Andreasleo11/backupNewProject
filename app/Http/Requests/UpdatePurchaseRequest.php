<?php

namespace App\Http\Requests;

use App\Rules\SanitizedNumeric;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_import')) {
            $this->merge([
                'is_import' => filter_var($this->is_import, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'from_department' => 'required|string|max:255',
            'to_department' => 'required|string|max:255',
            'branch' => ['required', Rule::enum(\App\Enums\Branch::class)],
            'date_of_pr' => 'required|date',
            'date_of_required' => 'required|date',
            'remark' => 'nullable|string|max:255',
            'supplier' => 'required|string|max:255',
            'pic' => 'required|string|max:255',
            'items' => 'required|array',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.price' => ['required', new SanitizedNumeric],
            'items.*.uom' => 'required|string|max:50',
            'items.*.currency' => ['required', 'string', Rule::in(['IDR', 'CNY', 'USD'])],
            'items.*.purpose' => 'required|string|max:255',
            'is_import' => 'nullable|boolean',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date_of_pr.required' => 'The date of PR is required.',
            'date_of_pr.date' => 'The date of PR must be a valid date.',
            'date_of_required.required' => 'The date of required is required.',
            'date_of_required.date' => 'The date of required must be a valid date.',
            'remark.string' => 'The remark must be a string.',
            'remark.max' => 'The remark may not be greater than 255 characters.',
            'supplier.required' => 'The supplier is required.',
            'supplier.string' => 'The supplier must be a string.',
            'supplier.max' => 'The supplier may not be greater than 255 characters.',
            'pic.required' => 'The person in charge (PIC) is required.',
            'pic.string' => 'The PIC must be a string.',
            'pic.max' => 'The PIC may not be greater than 255 characters.',
            'items.required' => 'The items are required.',
            'items.array' => 'The items must be an array.',
            'items.*.item_name.required' => 'Each item name is required.',
            'items.*.item_name.string' => 'Each item name must be a string.',
            'items.*.item_name.max' => 'Each item name may not be greater than 255 characters.',
            'items.*.price.required' => 'Each item price is required.',
            'items.*.price.sanitized_numeric' => 'Each item price must be a sanitized numeric value.',
            'items.*.uom.required' => 'Each item unit of measure (UOM) is required.',
            'items.*.uom.string' => 'Each item UOM must be a string.',
            'items.*.currency.required' => 'Each item currency is required.',
            'items.*.currency.string' => 'Each item currency must be a string.',
            'items.*.currency.in' => 'Each item currency must be one of the following: IDR, CNY, USD.',
            'items.*.purpose.required' => 'Each item purpose is required.',
            'items.*.purpose.string' => 'Each item purpose must be a string.',
            'items.*.purpose.max' => 'Each item purpose may not be greater than 255 characters.',
            'is_import.boolean' => 'The import field must be true or false.',
        ];
    }
}
