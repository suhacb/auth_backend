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
            'client_id' => config('keycloak.client_id'),
            'client_secret' => config('keycloak.client_secret'),
            'grant_type' => 'password',
            'url' => 'http://host.docker.internal:9010',
            'callback_url' => 'http://host.docker.internal:9010/callback',
            'description' => 'Authentication frontend app.'
        ]);
    }
}
