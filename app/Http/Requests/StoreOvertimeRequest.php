<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOvertimeRequest extends FormRequest
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
    public function rules()
    {
        return [
            'from_department' => 'required|exists:departments,id',
            'branch' => 'required|string',
            'date_form_overtime' => 'required|date',
            'items' => 'required_without:excel_file|array',
            'items.*.NIK' => 'required|string',
            'excel_file' => 'nullable|file|mimes:xlsx,xls'
        ];
    }
}
