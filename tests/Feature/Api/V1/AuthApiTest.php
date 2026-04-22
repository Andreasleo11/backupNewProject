<?php

use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Authentication API', function () {
    beforeEach(function () {
        $this->department = Department::factory()->create();

        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'department_id' => $this->department->id,
        ]);
    });

    it('allows user to login with valid credentials', function () {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'device_name' => 'test-device',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'token_type',
                ],
            ]);

        expect($response->json('data.token'))->not->toBeEmpty();
        expect($response->json('data.token_type'))->toBe('Bearer');
    });

    it('rejects login with invalid credentials', function () {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('requires email and password for login', function () {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    });

    it('returns authenticated user data', function () {
        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/auth/me');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $this->user->id,
                    'email' => $this->user->email,
                ],
            ]);
    });

    it('allows user to logout', function () {
        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/auth/logout');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);

        // Token should be revoked
        $response = $this->withToken($token)
            ->getJson('/api/v1/auth/me');

        $response->assertUnauthorized();
    });

    it('rejects unauthenticated requests to protected endpoints', function () {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertUnauthorized();
    });
});
