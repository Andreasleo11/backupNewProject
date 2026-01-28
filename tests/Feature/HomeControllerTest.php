<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_users_see_home_view()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/home');

        $response->assertStatus(200);
        $response->assertViewIs('home');
    }
}