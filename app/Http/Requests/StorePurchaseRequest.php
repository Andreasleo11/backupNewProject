<?php

namespace App\Http\Requests;

use App\Rules\SanitizedNumeric;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'from_department' => [
                'required',
                'string',
                'max:255',
                Rule::exists('departments', 'name')
            ],
            'to_department' => 'required|string|max:255',
            'date_of_pr' => 'required|date',
            'date_of_required' => 'required|date',
            'remark' => 'nullable|string|max:255',
            'supplier' => 'required|string|max:255',
            'pic' => 'required|string|max:255',
            'type' => [
                'nullable',
                'string',
                Rule::in(['factory', 'office'])
            ],
            'is_import' => 'nullable|boolean',
            'items' => 'required|array',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.price' => ['required', new SanitizedNumeric],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'from_department.required' => 'The from department field is required.',
            'to_department.required' => 'The to department field is required.',
            'date_of_pr.required' => 'The date of PR field is required.',
            'date_of_required.required' => 'The date required field is required.',
            'supplier.required' => 'The supplier field is required.',
            'pic.required' => 'The PIC field is required.',
            'type.required' => 'The type field is required.',
            'items.required' => 'At least one item is required.',
            'items.*.name.required' => 'Each item must have a name.',
            'items.*.price.required' => 'Each item must have a price.',
        ];
    }
}
