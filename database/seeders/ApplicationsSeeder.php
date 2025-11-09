<?php

namespace Database\Seeders;

use App\Models\Application;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApplicationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Application::factory()->create([
            'name' => 'auth-frontend',
            'realm' => config('keycloak.admin_realm'),
            'client_id' => config('keycloak.admin_client_id'),
            'client_secret' => null,
            'grant_type' => 'password',
            'url' => 'http://localhost:9020',
            'callback_url' => 'http://localhost:9020/callback',
            'description' => 'Authentication frontend app.'
        ]);
    }
}
