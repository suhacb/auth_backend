<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Application;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerifyAuthApplicationTest extends TestCase
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
    

    public function test_allows_access_by_the_auth_frontend(): void
    {
        $applications_to_make = 3;
        Application::factory()->count($applications_to_make)->create();

        // index
        $response = $this->getJson(route('applications.index'));
        $response->assertStatus(200);
        
        // show
        $response = $this->getJson(route('applications.show', $applications_to_make));
        $response->assertStatus(200);
        
        // store
        $application_to_store = Application::factory()->make();
        logger()->info($application_to_store->toJson());
        $response = $this->postJson(route('applications.store'), $application_to_store->getAttributes());
        $response->assertStatus(201);
        
        // update
        $response = $this->putJson(route('applications.update', $applications_to_make), ['description' => 'Updated description.']);
        $response->assertStatus(200);
        
        // delete
        $response = $this->deleteJson(route('applications.delete', $applications_to_make));
        $response->assertStatus(204);
    }

    public function test_deny_access_when_no_headers_are_provided(): void
    {
        $this->defaultHeaders = [];
        $response = $this->getJson(route('applications.index'));
        $response->assertStatus(400)->assertJson(['error' => 'Application name is required']);
    }

    public function test_deny_access_when_application_name_is_missing()
    {
        $this->defaultHeaders = [
            'X-Client-Url' => $this->application->url
        ];
        $response = $this->getJson(route('applications.index'));
        $response->assertStatus(400)->assertJson(['error' => 'Application name is required']);
    }

    public function test_deny_access_when_client_url_is_missing(): void
    {
        $this->defaultHeaders = [
            'X-Application-Name' => $this->application->name,
        ];
        $response = $this->getJson(route('applications.index'));
        $response->assertStatus(400)->assertJson(['error' => 'Application URL is required']);
    }

    public function test_deny_access_when_application_name_is_invalid(): void
    {
        // Application with provided name does not exist
        $this->defaultHeaders = [
            'X-Application-Name' => 'non-existing-application',
            'X-Client-Url' => $this->application->url
        ];

        $response = $this->getJson(route('applications.index'));
        $response->assertStatus(403)->assertJson(['error' => 'Unauthorized application']);

        // Application with provided name exists but is not auth-frontend
        $invalid_application = Application::factory()->create(['name' => 'invalid-application']);
        $this->defaultHeaders = [
            'X-Application-Name' => $invalid_application->name,
            'X-Client-Url' => $this->application->url   // we still pass auth-frontend URL
        ];

        $response = $this->getJson(route('applications.index'));
        $response->assertStatus(403)->assertJson(['error' => 'Unauthorized application']);
    }

    public function test_deny_access_when_client_url_is_invalid(): void
    {
        // Application with provided name exists but is not auth-frontend
        $this->defaultHeaders = [
            'X-Application-Name' => 'auth-frontend',
            'X-Client-Url' => 'http://malicious-app.example.com'
        ];

        $response = $this->getJson(route('applications.index'));
        $response->assertStatus(403)->assertJson(['error' => 'Unauthorized application']);
    }
}
