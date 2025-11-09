<?php

namespace Tests\Feature;

use App\Classes\Auth\AccessToken;
use Tests\TestCase;
use App\Models\Application;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ValidateAccessTokenTest extends TestCase
{
    use RefreshDatabase;

    protected Application $application;

    protected ?string $accessToken = null;
    protected ?string $refreshToken = null;
    protected ?string $idToken = null;
    protected ?array $config = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = config('keycloak');
        // Create a default application
        $this->application = Application::factory()->create([
            'name' => 'auth-frontend',
            'url' => 'http://localhost:9020',
            'client_id' => config('keycloak.client_id'),
            'client_secret' => config('keycloak.client_secret'),
            'realm' => config('keycloak.realm'),
            'grant_type' => 'password'
        ]);
 
        // Set the default X-Application-Name header
        $this->defaultHeaders = [
            'X-Application-Name' => $this->application->name,
            'X-Client-Url' => $this->application->url
        ];
    }

    public function test_returns_401_if_there_is_no_access_token (): void
    {
        $response = $this->getJson(route('auth.validate-access-token'));
        $response->assertStatus(401);
        $response->assertJson(['error' => 'Unauthorized']);
    }

    public function test_returns_success_if_token_is_present()
    {
        // Login user to obtain access token
        $response = $this->postJson(route('auth.login'), [
            'username' => config('keycloak.testuser.username'),
            'password' => config('keycloak.testuser.password'),
        ]);

        $response->assertStatus(200);

        $this->accessToken = $response->json('access_token');
        $this->refreshToken = $response->json('refresh_token');

        $response = $this
            ->withToken($this->accessToken)
            ->withHeaders(['X-Refresh-Token' => $this->refreshToken])
            ->getJson(route('auth.validate-access-token'));
        $response->assertStatus(200);
    }

    public function test_returns_401_if_invalid_token_is_present()
    {
        $this->accessToken = 'invalid-access-token';
        $this->refreshToken = 'invalid-refresh-token';

        $response = $this
            ->withToken($this->accessToken)
            ->withHeaders(['X-Refresh-Token' => $this->refreshToken])
            ->getJson(route('auth.validate-access-token'));
        
        $response->assertStatus(401);
        $response->assertJson(['error' => 'Unauthorized']);
    }

    public function test_returns_fresh_access_token(): void
    {
        // Login user to obtain access token
        $response = $this->postJson(route('auth.login'), [
            'username' => config('keycloak.testuser.username'),
            'password' => config('keycloak.testuser.password'),
        ]);

        $response->assertStatus(200);

        $this->accessToken = 'invalid-access-token';
        $this->refreshToken = $response->json('refresh_token');

        $response = $this
            ->withToken($this->accessToken)
            ->withHeaders(['X-Refresh-Token' => $this->refreshToken])
            ->getJson(route('auth.validate-access-token'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
            'refresh_token',
            'refresh_expires_in',
            'scope',
            'id_token',
            'not_before_policy',
            'session_state',
        ]);
    }

    /** This method is for cleanup Keycloak tokens after all tests are done */
    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->idToken) {
            Http::get($this->config['base_url'] . '/realms/' . $this->config['realm'] . '/protocol/openid-connect/logout', [
                'id_token_hint' => $this->idToken
            ]);
        }
    }
}
