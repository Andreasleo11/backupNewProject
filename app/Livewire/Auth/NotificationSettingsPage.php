<?php

namespace App\Livewire\Auth;

use App\Infrastructure\Approval\Services\ApprovableModuleScanner;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationSettingsPage extends Component
{
    public string $global_mode = 'immediate';

    public array $module_preferences = [];

    public array $available_modules = [];

    public function mount(ApprovableModuleScanner $scanner)
    {
        $user = Auth::user();
        $this->global_mode = $user->email_notification_mode ?? 'both';
        $this->module_preferences = $user->notification_preferences ?? [];
        $this->available_modules = $scanner->scan();
    }

    public function save()
    {
        $user = Auth::user();

        $user->update([
            'email_notification_mode' => $this->global_mode,
            'notification_preferences' => $this->module_preferences,
        ]);

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Notification preferences updated successfully.',
        ]);
    }

    public function render()
    {
        return view('livewire.auth.notification-settings-page', [
            'pageTitle' => 'Notification Settings',
            'pageSubtitle' => 'Manage how and when you receive email notifications.',
        ])->layout('new.layouts.app');
    }
}
