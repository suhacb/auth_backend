<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name',
        'realm',
        'client_id',
        'client_secret',
        'grant_type',
        'url',
        'callback_url',
        'description'
    ];

    protected $hidden = [
        'client_secret'
    ];
}
