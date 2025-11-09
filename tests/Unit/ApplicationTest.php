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
            'realm',
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

    public function test_application_model_uses_has_factory()
    {
        $this->assertTrue(in_array('Illuminate\Database\Eloquent\Factories\HasFactory', class_uses(Application::class)));
    }
}
