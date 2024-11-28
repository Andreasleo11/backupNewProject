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
            'po_number' => 'required|numeric|unique:purchase_orders,po_number',
            'vendor_name' => 'required|string|max:255',
            'invoice_date' => 'required|string',
            'invoice_number' => 'required|string',
            'tanggal_pembayaran' => 'required|date',
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
            'invoice_date.required' => 'The Invoice Date is required.',
            'invoice_number.string' => 'The Invoice Number must be a valid string.',
            'invoice_number.required' => 'The Invoice Number is required.',
            'tanggal_pembayaran.required' => 'The Tanggal Pembayaran is required.',
            'tanggal_pembayaran.date' => 'The Tanggal Pembayaran must be a valid date.',
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
