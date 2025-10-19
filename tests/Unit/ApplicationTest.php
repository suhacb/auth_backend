<?php

namespace Tests\Unit;

use App\Models\Application;
use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_model_has_expected_fillable_attributes()
    {
        $model = new Application();

        $this->assertEqualsCanonicalizing([
            'name',
            'client_id',
            'client_secret',
            'grant_type',
            'url',
            'callback_url',
            'description'
        ], $model->getFillable());
    }

    public function test_application_model_has_hidden_attributes()
    {
        $model = new Application();
        $this->assertContains('client_secret', $model->getHidden());
    }

    public function test_application_model_uses_soft_deletes()
    {
        $this->assertTrue(in_array('Illuminate\\Database\\Eloquent\\SoftDeletes', class_uses(Application::class)));
    }

    public function test_application_model_can_create_update_and_soft_delete()
    {
        $app = Application::create([
            'name' => 'MyApp',
            'client_id' => 'client123',
            'client_secret' => 'secret',
            'grant_type' => 'password',
            'callback_url' => 'https://myapp.com/callback',
        ]);

        $this->assertDatabaseHas('applications', ['name' => 'MyApp']);

        $app->update(['name' => 'NewName']);
        $this->assertDatabaseHas('applications', ['name' => 'NewName']);

        $app->delete();
        $this->assertSoftDeleted($app);
    }
}
