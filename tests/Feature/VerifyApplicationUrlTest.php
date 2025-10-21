<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Application;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerifyApplicationUrlTest extends TestCase
{
    use RefreshDatabase;

    protected Application $application;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a default application
        $this->application = Application::factory()->create();
 
        // Set the default X-Application-Name header
        $this->defaultHeaders = [
            'X-Application-Name' => $this->application->name,
            'X-Client-Url' => $this->application->url
        ];
    }
    
    public function test_allows_access_by_the_registered_application(): void
    {
        // I just want to make sure that the middleware is passed
        $response = $this->postJson(route('auth.login'), []);
        $status = $response->status();
        $this->assertTrue(
            ($status >= 200 && $status < 300) || $status === 422,
            "Expected response status 2xx or 422, got {$status}."
        );

        $response = $this->postJson(route('auth.login_token'), []);
        $status = $response->status();
        $this->assertTrue(
            ($status >= 200 && $status < 300) || $status === 422,
            "Expected response status 2xx or 422, got {$status}."
        );
    }

    public function test_deny_access_when_no_headers_are_provided(): void
    {
        $this->defaultHeaders = [];
        $response = $this->postJson(route('auth.login'), []);
        $response->assertStatus(400)->assertJson(['error' => 'Application name is required']);
    }

    public function test_deny_access_when_application_name_is_missing()
    {
        $this->defaultHeaders = [
            'X-Client-Url' => $this->application->url
        ];
        $response = $this->postJson(route('auth.login'), []);
        $response->assertStatus(400)->assertJson(['error' => 'Application name is required']);
    }

    public function test_deny_access_when_client_url_is_missing(): void
    {
        $this->defaultHeaders = [
            'X-Application-Name' => $this->application->name,
        ];
        $response = $this->postJson(route('auth.login'), []);
        $response->assertStatus(400)->assertJson(['error' => 'Application URL is required']);
    }

    public function test_deny_access_when_application_name_is_invalid(): void
    {
        // Application with provided name does not exist
        $this->defaultHeaders = [
            'X-Application-Name' => 'non-existing-application',
            'X-Client-Url' => $this->application->url
        ];

        $response = $this->postJson(route('auth.login'), []);
        $response->assertStatus(403)->assertJson(['error' => 'Unauthorized application']);
    }

    public function test_deny_access_when_client_url_is_invalid(): void
    {
        // Application with provided name exists but is not auth-frontend
        $this->defaultHeaders = [
            'X-Application-Name' => $this->application->name,
            'X-Client-Url' => 'http://malicious-app.example.com'
        ];

        $response = $this->postJson(route('auth.login'), []);
        $response->assertStatus(403)->assertJson(['error' => 'Unauthorized application']);
    }
}
