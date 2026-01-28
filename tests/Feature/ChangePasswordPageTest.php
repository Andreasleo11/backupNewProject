<?php

namespace Tests\Feature;

use App\Livewire\Auth\ChangePasswordPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class ChangePasswordPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_change_success()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        Livewire::actingAs($user)
            ->test(ChangePasswordPage::class)
            ->set('current_password', 'oldpassword')
            ->set('password', 'newpassword123')
            ->set('password_confirmation', 'newpassword123')
            ->call('changePassword')
            ->assertHasNoErrors()
            ->assertSessionHas('success', 'Your password has been updated.');

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_wrong_current_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        Livewire::actingAs($user)
            ->test(ChangePasswordPage::class)
            ->set('current_password', 'wrongpassword')
            ->set('password', 'newpassword123')
            ->set('password_confirmation', 'newpassword123')
            ->call('changePassword')
            ->assertHasErrors(['current_password' => 'The current password is incorrect.']);
    }

    public function test_password_confirmation_mismatch()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ChangePasswordPage::class)
            ->set('current_password', 'password')
            ->set('password', 'newpassword123')
            ->set('password_confirmation', 'different')
            ->call('changePassword')
            ->assertHasErrors(['password' => 'The password field confirmation does not match.']);
    }

    public function test_password_too_short()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ChangePasswordPage::class)
            ->set('current_password', 'password')
            ->set('password', 'short')
            ->set('password_confirmation', 'short')
            ->call('changePassword')
            ->assertHasErrors(['password' => 'The password field must be at least 8 characters.']);
    }

    public function test_rate_limiting()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        // Simulate too many attempts
        RateLimiter::hit('change-password:' . $user->id, 5);

        Livewire::actingAs($user)
            ->test(ChangePasswordPage::class)
            ->set('current_password', 'oldpassword')
            ->set('password', 'newpassword123')
            ->set('password_confirmation', 'newpassword123')
            ->call('changePassword')
            ->assertHasErrors(['general' => 'Too many attempts. Please try again later.']);
    }

    public function test_rate_limiter_hit_on_wrong_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        Livewire::actingAs($user)
            ->test(ChangePasswordPage::class)
            ->set('current_password', 'wrong')
            ->set('password', 'newpassword123')
            ->set('password_confirmation', 'newpassword123')
            ->call('changePassword');

        $this->assertTrue(RateLimiter::tooManyAttempts('change-password:' . $user->id, 5));
    }

    public function test_rate_limiter_cleared_on_success()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        // Hit once
        RateLimiter::hit('change-password:' . $user->id);

        Livewire::actingAs($user)
            ->test(ChangePasswordPage::class)
            ->set('current_password', 'oldpassword')
            ->set('password', 'newpassword123')
            ->set('password_confirmation', 'newpassword123')
            ->call('changePassword');

        $this->assertFalse(RateLimiter::tooManyAttempts('change-password:' . $user->id, 5));
    }
}
