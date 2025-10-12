<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test AuthController requests validation
     */
    public function test_login_route(): void
    {
        $username = 'username';
        $password = 'password';

        // Test username is required
        $response = $this->postJson(route('auth.login'), [
            'password' => $password
        ]);
        $response->assertStatus(422, 'Expected HTTP 422 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonValidationErrors(['username']);

        // Test password is required
        $response = $this->postJson(route('auth.login'), [
            'username' => $username
        ]);

        $response->assertStatus(422, 'Expected HTTP 422 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonValidationErrors(['password']);

        // Test username must be string
        $response = $this->postJson(route('auth.login'), [
            'username' => 12345,
            'password' => $password
        ]);

        $response->assertStatus(422, 'Expected HTTP 422 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonValidationErrors(['username']);

        // Test password must be string
        $response = $this->postJson(route('auth.login'), [
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
        $response = $this->postJson(route('auth.login'), [
            'username' => 'test',
            'password' => '!2ndArmored'
        ]);
        $response->assertStatus(200, 'Expected HTTP 200 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
            'refresh_token',
            'refresh_expires_in',
            'scope'
        ]);
        $access_token = $response->json('access_token');

        // Test login using incorrect username
        $response = $this->postJson(route('auth.login'), [
            'username' => 'incorrect_username',
            'password' => '!2ndArmored'
        ]);
        $response->assertStatus(401, 'Expected HTTP 401 for validation error, but received ' . $response->status() . '.');
        $allowed = ['username', 'password'];
        $response->assertJsonStructure(['errors' => $allowed]);

        // Test login using incorrect password
        $response = $this->postJson(route('auth.login'), [
            'username' => 'test',
            'password' => 'incorrect_password'
        ]);
        $response->assertStatus(401, 'Expected HTTP 401 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonStructure(['errors' => $allowed]);

        // Test login using incorrect client_id
        $original = config('keycloak.client_id');
        Config::set('keycloak.client_id', 'wrong_client_id');
        $response = $this->postJson(route('auth.login'), [
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
        $response = $this->postJson(route('auth.login'), [
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
        $response = $this->postJson(route('auth.login'), [
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

    /**
     * Test AuthController login token
     */
    public function test_login_token (): void {
        /** Assert rule that app is required. */
        $response = $this->postJson(route('auth.login_token'), ['prohibited-key' => 'prohibited content']);
        $response->assertStatus(422, 'Expected HTTP 422 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonValidationErrors(['app']);

        /** Assert rule that app is string. */
        $response = $this->postJson(route('auth.login_token'), ['app' => 12345]);
        $response->assertStatus(422, 'Expected HTTP 422 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonValidationErrors(['app']);

        /** Assert rule that app value is allowed. */
        $response = $this->postJson(route('auth.login_token'), ['app' => 'some non existing app']);
        $response->assertStatus(422, 'Expected HTTP 422 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonValidationErrors(['app']);

        /** Assert that loginToken returns one-time login token. */
        $response = $this->postJson(route('auth.login_token'), ['app' => 'nutrients']);
        $response->assertStatus(200, 'Expected HTTP 200 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonStructure([
            'app',
            'login_token',
            'issued_at',
            'valid_until'
        ]);

        /** Assert that login_token is valid for 5 minutes. */
        $issued_at = Carbon::parse($response->json('issued_at'));
        $valid_until = Carbon::parse($response->json('valid_until'));

        $diffInMinutes = $issued_at->diffInMinutes($valid_until);
        $this->assertEquals(5, $diffInMinutes, "Token validity duration should be 5 minutes");
    }
}
