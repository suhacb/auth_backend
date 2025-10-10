<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationTest extends TestCase
{
    /**
     * Test AuthController requests validation
     */
    public function test_login_route(): void
    {
        $username = 'username';
        $password = 'password';

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

    /**
     * Test AuthController login procedure using keycloak
     */
    public function test_login_procedure(): void
    {
        // Test login using correct credentials
        $response = $this->postJson(route('login'), [
            'username' => 'test',
            'password' => '!2ndArmored'
        ]);
        $response->assertStatus(200, 'Expected HTTP 200 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonStructure([
            'access_token',
            'expires_in',
            'refresh_expires_in',
            'refresh_token',
            'token_type',
            'not-before-policy',
            'session_state',
            'scope',
        ]);
        $access_token = $response->json('access_token');

        // Test login using incorrect username
        $response = $this->postJson(route('login'), [
            'username' => 'incorrect_username',
            'password' => '!2ndArmored'
        ]);
        $response->assertStatus(401, 'Expected HTTP 401 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonStructure([
            'error',
            'error_description'
        ]);

        // Test login using incorrect password
        $response = $this->postJson(route('login'), [
            'username' => 'test',
            'password' => 'incorrect_password'
        ]);
        $response->assertStatus(401, 'Expected HTTP 401 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonStructure([
            'error',
            'error_description',
        ]);

        // Test login using incorrect client_id
        $original = config('keycloak.client_id');
        Config::set('keycloak.client_id', 'wrong_client_id');
        $response = $this->postJson(route('login'), [
            'username' => 'test',
            'password' => '!2ndArmored'
        ]);
        $response->assertStatus(401, 'Expected HTTP 401 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonStructure([
            'error',
            'error_description'
        ]);
        Config::set('keycloak.client_id', $original);

        // Test login using incorrect client_secret
        $original = config('keycloak.client_secret');
        Config::set('keycloak.client_id', 'wrong_client_secret');
        $response = $this->postJson(route('login'), [
            'username' => 'test',
            'password' => '!2ndArmored'
        ]);
        $response->assertJsonStructure([
            'error',
            'error_description'
        ]);
        Config::set('keycloak.client_secret', $original);

        // Test login using incorrect grant_type
        $original = config('keycloak.client_secret');
        Config::set('keycloak.client_id', 'wrong_client_secret');
        $response = $this->postJson(route('login'), [
            'username' => 'test',
            'password' => '!2ndArmored'
        ]);
        $response->assertJsonStructure([
            'error',
            'error_description'
        ]);

        Config::set('keycloak.client_secret', $original);

        // Clean up keycloak sessions for the user
        $user = Http::withToken($access_token)->get(config('keycloak.base_url') . '//realms/' . config('keycloak.realm') . '/protocol/openid-connect/userinfo')->json();
        $user_id = $user['sub'];
        $response = Http::asForm()->post(config('keycloak.base_url') . '/realms/master/protocol/openid-connect/token', [
            'grant_type' => 'password',
            'client_id' => 'admin-cli',
            'username' => 'admin',
            'password' => 'admin',
        ]);
        $response = Http::withToken($response->json('access_token'))->post(config('keycloak.base_url') . "/admin/realms/" . config('keycloak.realm') . "/users/{$user_id}/logout");
    }
}
