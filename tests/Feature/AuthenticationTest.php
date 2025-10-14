<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected array $config;
    protected string $adminToken;
    protected array $userCredentials;
    protected ?string $accessToken;
    protected ?string $idToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = config('keycloak');

        // $adminResponse = Http::asForm()->post('http://host.docker.internal:7080/realms/master/protocol/openid-connect/token', [
        //     'grant_type' => 'password',
        //     'client_id' => $this->config['admin_client_id'],
        //     'username' => $this->config['admin_username'],
        //     'password' => $this->config['admin_password'],
        // ]);
// 
        $this->accessToken = null;
        $this->idToken = null;
        // $this->adminToken = $adminResponse->json('access_token');
    }

    /**
     * Test AuthController requests validation
     */
    public function test_login_route(): void
    {
        $username = Str::random(12);
        $password = Str::random(12);

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
            'username' => random_int(10000, 99999),
            'password' => $password
        ]);

        $response->assertStatus(422, 'Expected HTTP 422 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonValidationErrors(['username']);

        // Test password must be string
        $response = $this->postJson(route('auth.login'), [
            'username' => $username,
            'password' => random_int(10000, 99999)
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
            'username' => Arr::get($this->config, 'test_user.username'),
            'password' => Arr::get($this->config, 'test_user.password')
        ]);
        $response->assertStatus(200, 'Expected HTTP 200 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
            'refresh_token',
            'refresh_expires_in',
            'scope',
            'id_token'
        ]);
        $this->accessToken = $response->json('access_token');
        $this->idToken = $response->json('id_token');

        // Test login using incorrect username
        $response = $this->postJson(route('auth.login'), [
            'username' => Str::random(12),
            'password' => Arr::get($this->config, 'test_user.password')
        ]);
        $response->assertStatus(401, 'Expected HTTP 401 for validation error, but received ' . $response->status() . '.');
        $allowed = ['username', 'password'];
        $response->assertJsonStructure(['errors' => $allowed]);

        // Test login using incorrect password
        $response = $this->postJson(route('auth.login'), [
            'username' => Arr::get($this->config, 'test_user.username'),
            'password' => Str::random(12)
        ]);
        $response->assertStatus(401, 'Expected HTTP 401 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonStructure(['errors' => $allowed]);
    }

    public function test_login_using_incorrect_client_id(): void
    {
        // Test login using incorrect client_id
        Config::set('keycloak.client_id', 'wrong_client_id');
        $response = $this->postJson(route('auth.login'), [
            'username' => Arr::get($this->config, 'test_user.username'),
            'password' => Arr::get($this->config, 'test_user.password')
        ]);
        $response->assertStatus(500, 'Expected HTTP 500 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonStructure([
            'errors',
        ]);
    }

    // Test login using incorrect client_secret
    public function test_login_using_incorrect_client_secret(): void
    {
        Config::set('keycloak.client_id', 'wrong_client_secret');
        $response = $this->postJson(route('auth.login'), [
            'username' => Arr::get($this->config, 'test_user.username'),
            'password' => Arr::get($this->config, 'test_user.password')
        ]);
        $response->assertStatus(500, 'Expected HTTP 500 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonStructure([
            'errors',
        ]);
    }

    // Test login using incorrect grant_type
    public function test_login_using_incorrect_grant_type(): void
    {
        Config::set('keycloak.grant_type', 'wrong_grant_type');
        $response = $this->postJson(route('auth.login'), [
            'username' => Arr::get($this->config, 'test_user.username'),
            'password' => Arr::get($this->config, 'test_user.password')
        ]);
        $response->assertStatus(500, 'Expected HTTP 500 for validation error, but received ' . $response->status() . '.');
        $response->assertJsonStructure([
            'errors',
        ]);
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

    /** This method is for cleanup Keycloak tokens after all tests are done */
    // public static function tearDownAfterClass(): void
    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->idToken) {
            $response = Http::get(Arr::get($this->config, 'base_url') . '/realms/' . Arr::get($this->config, 'realm') . '/protocol/openid-connect/logout', [
                'id_token_hint' => $this->idToken
            ]);
        }
    }
}
