<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(AuthRequest $request)
    {
        $credentials = $request->only('username', 'password');
        // Here you would typically validate the credentials and generate a token
        // For simplicity, we will just return the credentials as JSON
        return response()->json($credentials);
    }
}
