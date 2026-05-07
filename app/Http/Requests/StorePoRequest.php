<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'parent_po_number' => 'nullable|string',
            'po_number' => [
                'required',
                'string',
                'max:255',
                // Enforce uniqueness only among active (non-soft-deleted) POs
                Rule::unique('purchase_orders', 'po_number')->whereNull('deleted_at'),
            ],
            'purchase_order_category_id' => 'required|numeric|exists:purchase_order_categories,id',
            'vendor_name' => 'required|string|max:255',
            'currency' => 'required|in:IDR,YUAN,USD',
            'total' => 'required|regex:/^\d{1,3}(,\d{3})*(\.\d+)?$/',
            'pdf_file' => 'required|file|mimes:pdf|max:5120',
        ];
    }

    public function messages()
    {
        return [
            'parent_po_number.string' => 'The parent PO number must be a string.',
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
            'pdf_file.required' => 'Please upload a PDF file.',
            'pdf_file.mimes' => 'The file must be a PDF.',
            'pdf_file.max' => 'The PDF file size should not exceed 5MB.',
        ];
    }
}
