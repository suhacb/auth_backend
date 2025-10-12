<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoginToken extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'app',
        'login_token',
        'issued_at',
        'valid_until',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'valid_until' => 'datetime'
    ];

    protected static function booted()
    {
        static::creating(function ($token) {
            $token->setIssuedAtAndValidUntil($token->issued_at ?? null);
            // Generate token if not already set
            do {
                $tokenString = Str::uuid()->toString(); // or Str::random(32)
            } while (\App\Models\LoginToken::withTrashed()->where('login_token', $tokenString)->exists());
            $token->login_token = $tokenString;
        });
    }

    public function setIssuedAtAndValidUntil(?Carbon $time = null): void
    {
        $this->setIssuedAt($time);
        $this->setValidUntil($this->issued_at);
    }
    
    protected function setIssuedAt(?Carbon $issued_at = null): void
    {
        $this->issued_at = $issued_at ?? Carbon::now();
    }

    protected function setValidUntil(Carbon $issued_at): void
    {
        $this->valid_until = $issued_at->copy()->addMinutes(5);
    }

    public function isValid(?Carbon $time = null): bool
    {
        $time = $time ?? Carbon::now();
        return $time->lte($this->valid_until);
    }

    public function use(): bool
    {
        $this->delete();
        return $this->isValid() ? true : false;
    }
}
