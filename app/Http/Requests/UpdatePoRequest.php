<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // Route parameter is {po} — resolves to the PO's primary key
        $poId = $this->route('po');

        return [
            'po_number' => [
                'required',
                'string',
                'max:255',
                // Allow the PO to keep its own number; exclude soft-deleted records
                Rule::unique('purchase_orders', 'po_number')
                    ->ignore($poId)
                    ->whereNull('deleted_at'),
            ],
            'purchase_order_category_id' => 'required|numeric|exists:purchase_order_categories,id',
            'vendor_name' => 'required|string|max:255',
            'currency' => 'required|in:IDR,YUAN,USD',
            'total' => 'required|regex:/^\d{1,3}(,\d{3})*(\.\d+)?$/',
            'pdf_file' => 'nullable|file|mimes:pdf|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'po_number.required' => 'The PO number is required.',
            'po_number.string' => 'The PO number must be a string.',
            'po_number.max' => 'The PO number must not exceed 255 characters.',
            'po_number.unique' => 'This PO number already exists.',
            'purchase_order_category_id.required' => 'The category is required.',
            'purchase_order_category_id.numeric' => 'The category must be a number.',
            'purchase_order_category_id.exists' => 'This category does not exist.',
            'vendor_name.required' => 'The vendor name is required.',
            'vendor_name.string' => 'The vendor name must be a valid string.',
            'vendor_name.max' => 'The vendor name should not exceed 255 characters.',
            'currency.required' => 'Please select a currency.',
            'currency.in' => 'The selected currency is invalid.',
            'total.required' => 'The total amount is required.',
            'total.regex' => 'The total must be a valid currency format.',
            'pdf_file.mimes' => 'The file must be a PDF.',
            'pdf_file.max' => 'The PDF file size should not exceed 2MB.',
        ];
    }
}
