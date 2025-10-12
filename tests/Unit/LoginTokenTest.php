<?php

namespace Tests\Unit;

use App\Models\LoginToken;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoginTokenTest extends TestCase
{
    public function test_login_token_model_basic_settings(): void
    {
        // Test fillable attributes
        $loginToken = new LoginToken();
        $this->assertEqualsCanonicalizing([
            'app',
            'login_token',
            'issued_at',
            'valid_until',
        ], $loginToken->getFillable());

        // Test that LoginToken model uses softdeletes
        $this->assertContains(
            SoftDeletes::class,
            class_uses(LoginToken::class)
        );

        // Test casts
        $expectedCasts = [
            'issued_at' => 'datetime',
            'valid_until' => 'datetime',
        ];

        foreach ($expectedCasts as $attribute => $type) {
            $this->assertArrayHasKey($attribute, $loginToken->getCasts());
            $this->assertEquals($type, $loginToken->getCasts()[$attribute]);
        }
    }
}
