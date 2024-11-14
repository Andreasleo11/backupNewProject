<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'po_number' => 'required|numeric|unique:master_po,po_number', // assuming `pos` is your table name
            'vendor_name' => 'required|string|max:255',
            'po_date' => 'required|string',
            'currency' => 'required|in:IDR,YUAN,USD',
            'total' => 'required|regex:/^\d{1,3}(,\d{3})*(\.\d+)?$/', // validates currency format
            'pdf_file' => 'required|file|mimes:pdf|max:2048', // max 2MB
        ];
    }

    public function messages()
    {
        return [
            'po_number.required' => 'The PO number is required.',
            'po_number.numeric' => 'The PO number must be a number.',
            'po_number.unique' => 'This PO number already exists.',
            'vendor_name.required' => 'The vendor name is required.',
            'vendor_name.string' => 'The vendor name must be a valid string.',
            'vendor_name.max' => 'The vendor name should not exceed 255 characters.',
            'po_date.required' => 'The PO date is required.',
            'po_date.date' => 'The PO date must be a valid date.',
            'currency.required' => 'Please select a currency.',
            'currency.in' => 'The selected currency is invalid.',
            'total.required' => 'The total amount is required.',
            'total.regex' => 'The total must be a valid currency format.',
            'pdf_file.required' => 'Please upload a PDF file.',
            'pdf_file.mimes' => 'The file must be a PDF.',
            'pdf_file.max' => 'The PDF file size should not exceed 2MB.',
        ];
    }
}
