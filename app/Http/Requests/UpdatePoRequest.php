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
        return [
            'category' => 'required|string',
            'tanggal_pembayaran' => 'required|date',
            'currency' => 'required|in:IDR,YUAN,USD',
            'total' => 'required|regex:/^\d{1,3}(,\d{3})*(\.\d+)?$/',
            'is_need_sign' => 'required|in:1,0',
        ];
    }

    public function messages()
    {
        return [
            'category.required' => 'The category is required.',
            'category.string' => 'The category must be a string.',
            'tanggal_pembayaran.required' => 'The Tanggal Pembayaran is required.',
            'tanggal_pembayaran.date' => 'The Tanggal Pembayaran must be a valid date.',
            'currency.required' => 'Please select a currency.',
            'currency.in' => 'The selected currency is invalid.',
            'total.required' => 'The total amount is required.',
            'total.regex' => 'The total must be a valid currency format.',
            'is_need_sign.required' => 'Please select if the PO needs a signature.',
            'is_need_sign.in' => 'The selected option for signature requirement is invalid.',
        ];
    }
}
