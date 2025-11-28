<?php

use Illuminate\Foundation\Http\FormRequest;

class ChangeUserPasswordRequest extends FormRequest 
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.change-password') ?? false;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:8', 'confirmed']
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }
}