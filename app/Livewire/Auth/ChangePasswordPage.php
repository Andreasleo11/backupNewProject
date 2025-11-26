<?php

namespace App\Livewire\Auth;

use App\Application\User\UseCases\ChangeUserPassword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ChangePasswordPage extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function rules(): array
    {
        return [
            'current_password'      => ['required', 'string'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    public function changePassword(ChangeUserPassword $changeUserPassword): void
    {
        $this->validate();

        $user = Auth::user();

        if (! $user) {
            session()->flash('error', 'You must be logged in.');
            return;
        }

        // Verify current password
        if (! Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'The current password is incorrect.');
            return;
        }

        // Use the same use case as admin
        $changeUserPassword->execute($user->id, $this->password);

        // Clean up fields
        $this->reset(['current_password', 'password', 'password_confirmation']);

        session()->flash('success', 'Your password has been updated.');
    }

    public function render()
    {
        return view('livewire.auth.change-password-page', [
            'pageTitle' => 'Account Security',
            'pageSubtitle' => 'Manage your password.',
        ])->layout('new.layouts.app');
    }
}
