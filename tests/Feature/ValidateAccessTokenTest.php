<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ValidateAccessTokenTest extends TestCase
{
    use RefreshDatabase;

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
