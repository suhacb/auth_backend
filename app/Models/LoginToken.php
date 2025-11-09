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
}