<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Application;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ValidateAccessTokenTest extends TestCase
{
    use RefreshDatabase;

    protected Application $application;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a default application
        $this->application = Application::factory()->create([
            'name' => 'auth-frontend',
            'url' => 'https://auth-frontend.example.com',
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
        $response->assertJson(['message' => 'Unauthorized']);
    }

    public function test_returns_success_if_token_is_present()
    {
        $token = 'dummy-access-token';

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->getJson(route('auth.validate-access-token'));

        $response->assertStatus(200);
    }
}
