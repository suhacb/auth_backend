<?php

namespace Tests\Feature;

use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    protected ?array $config = null;
    protected ?Application $application = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = config('keycloak');

        // Set application
        $this->application = Application::factory()->create([
            'realm' => $this->config['realm'],
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
        ]);
 
        // Set the default X-Application-Name header
        $this->defaultHeaders = [
            'X-Application-Name' => $this->application->name,
            'X-Client-Url' => $this->application->url
        ];
    }

    public function test_logout_fails_if_no_token_is_provided()
    {
        $response = $this->postJson(route('auth.logout', []));

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'Unauthorized',
        ]);
    }

    public function test_fails_if_token_is_invalid()
    {
        $invalidToken = 'invalidtoken123';

        $response = $this->withToken($invalidToken)->postJson('auth.logout', []);

        $response->assertStatus(401);
        $response->assertJson([
             'error' => 'Unauthorized',
         ]);
    }

    public function test_logout_revokes_access_token_successfully()
    {
        // Test login using correct credentials
        $testuser = $this->config['testuser'];
        $response = $this->postJson(route('auth.login'), [
            'username' => $testuser['username'],
            'password' => $testuser['password'],
        ]);

        $response->assertStatus(200);

        $accessToken = $response->json('access_token');
        $response = $this->withToken($accessToken)->postJson(route('auth.logout'), []);

        $response->assertStatus(200);
        $response->assertExactJson(['message' => 'Logged out successfully']);

        // Check that revoked token is invalid in keycloak
        $response->withToken($accessToken)->postJson(route('auth.validate-access-token'), []);
        $response->assertStatus(401);
    }
}
