<?php

namespace Tests\Unit;

use App\Models\Application;
use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApplicationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ApplicationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ApplicationService::class);
    }

    public function test_application_service_creates_an_application()
    {
        $data = [
            'name' => 'App A',
            'client_id' => 'a123',
            'client_secret' => 'secret',
            'grant_type' => 'password',
            'callback_url' => 'https://a.com/callback',
        ];

        $app = $this->service->create($data);

        $this->assertInstanceOf(Application::class, $app);
        $this->assertDatabaseHas('applications', ['name' => 'App A']);
    }

    public function test_application_service_updates_an_application()
    {
        $app = Application::factory()->create(['name' => 'OldName']);

        $updated = $this->service->update($app, ['name' => 'NewName']);

        $this->assertEquals('NewName', $updated->name);
    }

    public function test_application_service_it_soft_deletes_an_application()
    {
        $app = Application::factory()->create();

        $this->service->delete($app);

        $this->assertSoftDeleted('applications', ['id' => $app->id]);
    }

    public function test_application_service_it_returns_all_applications()
    {
        Application::factory()->count(2)->create();
        $this->assertCount(2, $this->service->all());
    }
}
