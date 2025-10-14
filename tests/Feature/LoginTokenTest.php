<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Classes\Auth\LoginToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTokenTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_login_token_migration(): void
    {
        // Test that login_tokens table exists
        $this->assertTrue(Schema::hasTable('login_tokens'), 'The login_tokens table does not exist in the database.');

        // Test that login_tokens table has all the fields
        $expectedColumns = [
            'id' => 'integer',
            'app' => 'string',
            'login_token' => 'string',
            'issued_at' => 'datetime',
            'valid_until' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];

        $this->assertTrue(
            Schema::hasColumns('login_tokens', array_keys($expectedColumns)),
            'The users table does not have the expected columns: ' . implode(', ', array_keys($expectedColumns))
        );
        
        // Test that login_tokens table fields are of correct type
        $migrationPath = database_path('migrations/2025_10_11_150839_create_login_tokens_table.php');
        $migrationContent = file_get_contents($migrationPath);
        $this->assertStringContainsString('$table->id()', $migrationContent, 'Migration should create primary key id.');
        $this->assertStringContainsString("string('app')", $migrationContent, 'Migration should create string app.');
        $this->assertStringContainsString("string('login_token')", $migrationContent, 'Migration should create string login_token.');
        $this->assertStringContainsString("datetime('issued_at')", $migrationContent, 'Migration should create datetime issued_at.');
        $this->assertStringContainsString("datetime('valid_until')", $migrationContent, 'Migration should create datetime valid_until.');

        // Test for unique fields
        $lines = explode("\n", $migrationContent);
        $search = "string('login_token')";
        $foundLine = null;

        foreach ($lines as $line) {
            if (strpos($line, $search) !== false) {
                $foundLine = $line;
                break; // stop at first match
            }
        }

        $this->assertStringContainsString('unique()', $foundLine, 'Migration should make login_token unique.');
    }

    public function test_set_issued_at(): void
    {
        // Test that issued_at is now
        $now = Carbon::now();
        $loginToken = new LoginToken(
            app: 'nutrients'
        );
        $this->assertArrayHasKey('issued_at', $loginToken->token->getAttributes());
        $this->assertArrayHasKey('valid_until', $loginToken->token->getAttributes());
        $this->assertInstanceOf(Carbon::class, $loginToken->token->issued_at);
        $this->assertTrue($now->diffInSeconds($loginToken->token->issued_at)<1);
        $this->assertEquals($loginToken->token->issued_at->diffInMinutes($loginToken->token->valid_until), 5);

        $now = Carbon::now();
        $loginToken = new LoginToken(
            app: 'nutrients',
            time: $now
        );
        $this->assertTrue($loginToken->token->issued_at->diffInSeconds($now) < 2);
        $this->assertEquals($loginToken->token->issued_at->diffInMinutes($loginToken->token->valid_until), 5);
    }

    public function test_login_token_is_valid_method(): void
    {
        // Test that login token is valid
        $now = Carbon::now();
        $valid_time = $now->copy()->addMinutes(1);
        $invalid_time = $now->copy()->addMinutes(5)->addSeconds(1);
        $loginToken = new LoginToken(
            app: 'nutrients'
        );
        $this->assertTrue($loginToken->isValid($valid_time));
        $this->assertFalse($loginToken->isValid($invalid_time));
    }

    public function test_login_token_use_method(): void
    {
        // Test that the use method soft deletes the valid token and returns true
        $now = Carbon::now();
        $loginToken = new LoginToken(
            app: 'nutrients',
            time: $now
        );
        $token_is_used = $loginToken->use();
        $this->assertTrue($token_is_used);
        $this->assertSoftDeleted('login_tokens', [
            'id' => $loginToken->token->id,
        ]);

        // Test that the use method soft deletes the token
        $past_time = Carbon::now()->subMinutes(10);
        $loginToken = new LoginToken(
            app: 'nutrients',
            time: $past_time
        );
        $token_is_used = $loginToken->use();
        $this->assertFalse($token_is_used);
        $this->assertEmpty(\App\Models\LoginToken::where(['id' => $loginToken->token->id])->get());
    }

    public function test_login_token_is_used_method(): void
    {
        $now = Carbon::now();
        $loginToken = new LoginToken(
            app: 'nutrients',
            time: $now
        );

        // Test that the isUsed() method returns true on a used token
        $this->assertFalse($loginToken->isUsed($loginToken->token->id));
        $this->assertFalse($loginToken->isUsed($loginToken->token->login_token));

        // Test that the isUsed() method returns false on an unused and valid token
        $loginToken->use();
        $this->assertTrue($loginToken->isUsed($loginToken->token->id));
        $this->assertTrue($loginToken->isUsed($loginToken->token->login_token));
    }
}
