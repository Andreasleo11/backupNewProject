<?php

namespace App\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class UserRequest extends FormRequest
{
    /**
     * Rules for CREATE user (password required).
     */
    public static function storeRules(): array
    {
        return [
            'employeeId' => ['required', 'integer', 'exists:employees,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'selectedRoles' => ['nullable', 'array'],
            'selectedRoles.*' => ['string'],
            'active' => ['boolean'],
        ];
    }

    /**
     * Rules for UPDATE user (email unique except this user, no password here).
     */
    public static function updateRules(int $userId): array
    {
        return [
            'employeeId' => ['nullable', 'integer', 'exists:employees,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'selectedRoles' => ['nullable', 'array'],
            'selectedRoles.*' => ['string'],
            'active' => ['boolean'],
        ];
    }

    public static function messagesArray(): array
    {
        return [
            'employeeId.required' => 'Karyawan wajib dipilih',
            'employee.exists' => 'Data karyawan tidak ditemukan',
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh pengguna lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }

    public function messages(): array
    {
        return static::messagesArray();
    }
}
