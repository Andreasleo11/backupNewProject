<?php

namespace App\Presentation\Http\Requests;

use Illuminate\Validation\Rule;

class DepartmentRequest
{
    public static function storeRules(): array
    {
        return [
            'code' => ['required', 'string', 'max:10', 'unique:departments,code'],
            'name' => ['required', 'string', 'max:100'],
            'dept_no' => ['required', 'string', 'max:10', 'unique:departments,code'],
            'branch' => ['required', 'string', 'max:50', 'in:JAKARTA,KARAWANG'],
            'is_office' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    public static function updateRules(int $id): array
    {
        return [
            'code' => ['required', 'string', 'max:10', Rule::unique('departments')->ignore($id)],
            'name' => ['required', 'string', 'max:100'],
            'dept_no' => ['required', 'string', 'max:10', Rule::unique('departments')->ignore($id)],
            'branch' => ['required', 'string', 'max:50', 'in:JAKARTA,KARAWANG'],
            'is_office' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    public static function messagesArray(): array
    {
        return [
            'code.required' => 'Department code is required.',
            'code.unique' => 'This department code is already used.',
            'name.required' => 'Department name is required',
            'dept_no.required' => 'Department dept no is required.',
            'dept_no.unique' => 'This department dept no is already used.',
            'branch.required' => 'Branch is required',
            'branch.in' => 'Branch must be JAKARTA or KARAWANG',
        ];
    }
}
