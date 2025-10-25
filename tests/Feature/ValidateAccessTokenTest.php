<?php

namespace Tests\Feature;

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
            'realm' => config('keycloak.realm')
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
        $this->idToken = $response->json('id_token');
        $response = $this->withToken($this->accessToken)->getJson(route('auth.validate-access-token'));
        $response->assertStatus(200);
    }

    /** This method is for cleanup Keycloak tokens after all tests are done */
    // public static function tearDownAfterClass(): void
    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->idToken) {
            $response = Http::get($this->config['base_url'] . '/realms/' . $this->config['realm'] . '/protocol/openid-connect/logout', [
                'id_token_hint' => $this->idToken
            ]);
        }
    }
}
