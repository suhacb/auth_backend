<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_login_route(): void
    {
        $username = 'username';
        $password = 'password';
        $response = $this->postJson(route('login'), [
            'username' => $username,
            'password' => $password
        ]);
        $response->assertStatus(200, 'Expected HTTP 200 for login, but received ' . $response->status() . '.');
        $response->assertExactJson([
            'username' => $username,
            'password' => $password
        ], 'The login response JSON does not match the expected credentials.');

        // Test username is required
        $response = $this->postJson(route('login'), [
            'password' => $password
        ]);
        $response->assertStatus(422, 'Expected HTTP 422 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonValidationErrors(['username']);

        // Test password is required
        $response = $this->postJson(route('login'), [
            'username' => $username
        ]);

        $response->assertStatus(422, 'Expected HTTP 422 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonValidationErrors(['password']);

        // Test username must be string
        $response = $this->postJson(route('login'), [
            'username' => 12345,
            'password' => $password
        ]);

        $response->assertStatus(422, 'Expected HTTP 422 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonValidationErrors(['username']);

        // Test password must be string
        $response = $this->postJson(route('login'), [
            'username' => $username,
            'password' => 12345
        ]);

        $response->assertStatus(422, 'Expected HTTP 422 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonValidationErrors(['password']);
    }
}
