<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Application;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected $application; 
    
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

    public function test_applications_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('applications'));

        foreach ([
            'id',
            'name',
            'client_id',
            'client_secret',
            'grant_type',
            'url',
            'callback_url',
            'description',
            'created_at',
            'updated_at',
            'deleted_at'
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('applications', $column));
        }
    }

    public function test_applications_index(): void
    {
        $applications_to_make = 3;
        Application::factory()->count($applications_to_make)->create();

        $response = $this->getJson(route('applications.index'));
        $response->assertOk()->assertJsonCount($applications_to_make + 1); // we add one becaue one application is made in setUp()
    }

    public function test_applications_create(): void
    {
        $payload = Application::factory()->make();

        $response = $this->postJson(route('applications.store'), $payload->getAttributes());

        $response->assertCreated()->assertJsonPath('name', $payload->name);

        $this->assertDatabaseHas('applications', [
            'name' => $payload->name,
        ]);
    }
    
    public function test_validation_rules_for_name(): void
    {
        /** STORE method */
        // required
        $response = $this->postJson(route('applications.store'), []);
        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
        
        //string
        $response = $this->postJson(route('applications.store'), ['name' => 12345]);
        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
        
        //max
        $response = $this->postJson(route('applications.store'), ['name' => Str::random(256)]);
        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
        
        //unique
        $unique_name = 'unique_application_name';
        Application::factory()->create(['name' => $unique_name]);

        $response = $this->postJson(route('applications.store'), ['name' => $unique_name]);
        $response->assertStatus(422)->assertJsonValidationErrors(['name']);

        /** UPDATE method */
        $name = 'application_to_be_updated';
        $application = Application::factory()->create(['name' => $name]);
        
        //string
        $response = $this->putJson(route('applications.update', $application), ['name' => 12345]);
        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
        
        //max
        $response = $this->putJson(route('applications.update', $application), ['name' => Str::random(256)]);
        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
        
        //unique
        $unique_name = 'unique_application_name';

        $response = $this->putJson(route('applications.update', $application), ['name' => $unique_name]);
        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }
////
    ////public function test_validation_rules_for_client_id(): void
    ////{
    ////    /** STORE method */
    ////    // required
    ////    $response = $this->postJson(route('applications.store'), []);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['client_id']);
    ////    
    ////    //string
    ////    $response = $this->postJson(route('applications.store'), ['client_id' => 12345]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['client_id']);
    ////    
    ////    //max
    ////    $response = $this->postJson(route('applications.store'), ['client_id' => Str::random(256)]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['client_id']);
////
    ////    /** UPDATE method */
    ////    $application = Application::factory()->create();
////
    ////    //string
    ////    $response = $this->putJson(route('applications.update', $application), ['client_id' => 12345]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['client_id']);
    ////    
    ////    //max
    ////    $response = $this->putJson(route('applications.update', $application), ['client_id' => Str::random(256)]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['client_id']);
    ////}
////
    ////public function test_validation_rules_for_client_secret(): void
    ////{
    ////    /** STORE method */
    ////    // required
    ////    $response = $this->postJson(route('applications.store'), []);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['client_secret']);
    ////    
    ////    //string
    ////    $response = $this->postJson(route('applications.store'), ['client_secret' => 12345]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['client_secret']);
    ////    
    ////    //max
    ////    $response = $this->postJson(route('applications.store'), ['client_secret' => Str::random(256)]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['client_secret']);
////
    ////    /** UPDATE method */
    ////    $application = Application::factory()->create();
////
    ////    //string
    ////    $response = $this->putJson(route('applications.update', $application), ['client_secret' => 12345]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['client_secret']);
    ////    
    ////    //max
    ////    $response = $this->putJson(route('applications.update', $application), ['client_secret' => Str::random(256)]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['client_secret']);
    ////}
////
    ////public function test_validation_rules_for_grant_type(): void
    ////{
    ////    /** STORE method */
    ////    // required
    ////    $response = $this->postJson(route('applications.store'), []);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['grant_type']);
    ////    
    ////    //string
    ////    $response = $this->postJson(route('applications.store'), ['grant_type' => 12345]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['grant_type']);
    ////    
    ////    //max
    ////    $response = $this->postJson(route('applications.store'), ['grant_type' => Str::random(256)]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['grant_type']);
////
    ////    /** UPDATE method */
    ////    $application = Application::factory()->create();
////
    ////    //string
    ////    $response = $this->putJson(route('applications.update', $application), ['grant_type' => 12345]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['grant_type']);
    ////    
    ////    //max
    ////    $response = $this->putJson(route('applications.update', $application), ['grant_type' => Str::random(256)]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['grant_type']);
    ////}
////
    ////public function test_validation_rules_for_url(): void
    ////{
    ////    /** STORE method */
    ////    // required
    ////    $response = $this->postJson(route('applications.store'), []);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['url']);
    ////    
    ////    //string
    ////    $response = $this->postJson(route('applications.store'), ['url' => Str::random(50)]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['url']);
    ////    
    ////    //max
    ////    $response = $this->postJson(route('applications.store'), ['url' => Str::random(256)]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['url']);
////
    ////    /** UPDATE method */
    ////    $application = Application::factory()->create();
    ////    
    ////    //string
    ////    $response = $this->putJson(route('applications.update', $application), ['url' => Str::random(50)]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['url']);
    ////    
    ////    //max
    ////    $response = $this->putJson(route('applications.update', $application), ['url' => Str::random(256)]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['url']);
    ////}
////
    ////public function test_validation_rules_for_callback_url(): void
    ////{
    ////    /** STORE method */
    ////    // required
    ////    $response = $this->postJson(route('applications.store'), []);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['callback_url']);
    ////    
    ////    //string
    ////    $response = $this->postJson(route('applications.store'), ['callback_url' => Str::random(50)]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['callback_url']);
    ////    
    ////    //max
    ////    $response = $this->postJson(route('applications.store'), ['callback_url' => Str::random(256)]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['callback_url']);
////
    ////    /** UPDATE method */
    ////    $application = Application::factory()->create();
////
    ////    //string
    ////    $response = $this->putJson(route('applications.update', $application), ['callback_url' => Str::random(50)]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['callback_url']);
    ////    
    ////    //max
    ////    $response = $this->putJson(route('applications.update', $application), ['callback_url' => Str::random(256)]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['callback_url']);
    ////}
////
    ////public function test_validation_rules_for_description(): void
    ////{        
    ////    /** STORE method */
    ////    //string
    ////    $response = $this->postJson(route('applications.store'), ['description' => 12345]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['description']);
    ////    
    ////    //max
    ////    $response = $this->postJson(route('applications.store'), ['description' => Str::random(256)]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['description']);
////
    ////    /** UPDATE method */
    ////    $application = Application::factory()->create();
    ////    //string
    ////    $response = $this->putJson(route('applications.update', $application), ['description' => 12345]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['description']);
    ////    
    ////    //max
    ////    $response = $this->putJson(route('applications.update', $application), ['description' => Str::random(256)]);
    ////    $response->assertStatus(422)->assertJsonValidationErrors(['description']);
    ////}
////
    ////public function test_application_update(): void
    ////{
    ////    $app = Application::factory()->create();
////
    ////    $response = $this->putJson(route('applications.update', $app), [
    ////        'name' => 'Updated App Name',
    ////    ]);
////
    ////    $response->assertOk()->assertJsonPath('name', 'Updated App Name');
////
    ////    $this->assertDatabaseHas('applications', ['name' => 'Updated App Name']);
    ////}
////
    ////public function test_delete_application_softly(): void
    ////{
    ////    $application = Application::factory()->create();
////
    ////    $response = $this->deleteJson(route('applications.delete', $application));
////
    ////    $response->assertNoContent()->assertStatus(204);
////
    ////    $this->assertSoftDeleted('applications', ['id' => $application->id]);
    ////}
////
    ////public function test_show_application(): void
    ////{
    ////    $application = Application::factory()->create();
////
    ////    $response = $this->getJson(route('applications.show', $application));
////
    ////    $response->assertStatus(200)->assertJson([
    ////        'id' => $application->id,
    ////        'name' => $application->name,
    ////        'client_id' => $application->client_id,
    ////        'grant_type' => $application->grant_type,
    ////        'url' => $application->url,
    ////        'callback_url' => $application->callback_url,
    ////        'description' => $application->description,
    ////    ]);
    ////}
}