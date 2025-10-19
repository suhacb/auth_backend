<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Application;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

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
        Application::factory()->count(3)->create();

        $response = $this->getJson(route('applications,index'));

        $response->assertOk()
                 ->assertJsonCount(3, 'data');
    }

    public function test_applications_create(): void
    {
        $payload = [
            'name' => 'Frontend App',
            'client_id' => 'frontend-client',
            'client_secret' => 'secret123',
            'grant_type' => 'password',
            'callback_url' => 'https://frontend.app/callback',
        ];

        $response = $this->postJson(route('applications.store'), $payload);

        $response->assertCreated()
                 ->assertJsonPath('data.name', 'Frontend App');

        $this->assertDatabaseHas('applications', [
            'client_id' => 'frontend-client',
        ]);
    }

    public function test_required_fields_on_create(): void
    {
        $response = $this->postJson(route('applications.store'), []);

        $response->assertStatus(422)->assertJsonValidationErrors([
            'name',
            'client_id',
            'client_secret',
            'grant_type',
            'url',
            'callback_url'
        ]);
    }

    public function test_application_update(): void
    {
        $app = Application::factory()->create();

        $response = $this->putJson(route('applications.update', $app->id), [
            'name' => 'Updated App Name',
        ]);

        $response->assertOk()
                 ->assertJsonPath('data.name', 'Updated App Name');

        $this->assertDatabaseHas('applications', ['name' => 'Updated App Name']);
    }

    public function test_required_fields_on_update(): void
    {
        $app = Application::factory()->create();

        $response = $this->putJson(route('applications.update', $app->id), [
            'client_id' => '',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['client_id']);
    }

    public function test_delete_application_softly(): void
    {
        $app = Application::factory()->create();

        $response = $this->deleteJson(route('applications.delete', $app->id));

        $response->assertNoContent();

        $this->assertSoftDeleted('applications', ['id' => $app->id]);
    }
}
