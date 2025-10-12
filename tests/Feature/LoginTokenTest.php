<?php

namespace Tests\Feature;

use App\Models\LoginToken;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class LoginTokenTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_login_token_migration(): void
    {
        // Test that login_tokens table exists

        // Test that login_tokens table has all the fields
        
        // Test that login_tokens table fields are of correct type

        // Test for unique fields
    }

    public function test_set_issued_at(): void
    {
        $loginToken = new LoginToken();

        // Test that issued_at is now
        $now = Carbon::now();
        $loginToken->setIssuedAtAndValidUntil();
        $this->assertArrayHasKey('issued_at', $loginToken->getAttributes());
        $this->assertArrayHasKey('valid_until', $loginToken->getAttributes());
        $this->assertInstanceOf(Carbon::class, $loginToken->issued_at);
        $this->assertTrue($now->diffInSeconds($loginToken->issued_at)<1);
        $this->assertEquals($loginToken->issued_at->diffInMinutes($loginToken->valid_until), 5);

        $loginToken = new LoginToken();
        $now = Carbon::now();
        $loginToken->setIssuedAtAndValidUntil($now);
        $this->assertTrue($loginToken->issued_at->diffInSeconds($now) < 2);
        $this->assertEquals($loginToken->issued_at->diffInMinutes($loginToken->valid_until), 5);
    }

    public function test_login_token_create_method(): void
    {
        // Test that login token is created correctly
        $now = Carbon::now();
        $loginToken = new LoginToken(['app' => 'nutrients']);
        $loginToken->setIssuedAtAndValidUntil($now);
        $loginToken->save();
        $loginToken = LoginToken::where(['login_token' => $loginToken->login_token])->first();
        $this->assertEquals('nutrients', $loginToken->app);
        $this->assertInstanceOf(Carbon::class, $loginToken->issued_at);
        $this->assertInstanceOf(Carbon::class, $loginToken->valid_until);
        $this->assertTrue($loginToken->issued_at->diffInSeconds($now) < 2);
        $this->assertEquals($loginToken->issued_at->diffInMinutes($loginToken->valid_until), 5);
    }

    public function test_login_token_is_valid_method(): void
    {
        // Test that login token is valid
        $now = Carbon::now();
        $valid_time = $now->copy()->addMinutes(1);
        $invalid_time = $now->copy()->addMinutes(5)->addSeconds(1);
        $loginToken = new LoginToken(['app' => 'nutrients']);
        $this->assertTrue($loginToken->isValid($valid_time));
        $this->assertFalse($loginToken->isValid($invalid_time));
    }

    public function test_login_token_use_method(): void
    {
        // Test that the use method soft deletes the valid token and returns true
        $now = Carbon::now();
        $loginToken = LoginToken::create(['app' => 'nutrients', 'issued_at' => $now]);
        $token_is_used = $loginToken->use();
        $this->assertTrue($token_is_used);
        $this->assertSoftDeleted('login_tokens', [
            'id' => $loginToken->id,
        ]);

        // Test that the use method soft deletes the token
        $now = Carbon::now()->subMinutes(10);
        $loginToken = LoginToken::create(['app' => 'nutrients', 'issued_at' => $now]);
        Log::info('now:' . Carbon::now());
        Log::info($loginToken);
        $token_is_used = $loginToken->use();
        $this->assertFalse($token_is_used);
        $this->assertEmpty(LoginToken::where(['id' => $loginToken->id])->get());
    }

    public function test_login_token_is_used_method(): void
    {
        // Test that the isUsed() method returns true on a used token

        // Test that the isUsed() method returns false on an unused and valid token
    }
}
