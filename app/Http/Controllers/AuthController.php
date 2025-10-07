<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function login(AuthRequest $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');
        $url = 'host.docker.internal:7080/realms/nutrients/protocol/openid-connect/token';
        $response = Http::asForm()->post($url, [
            'grant_type' => 'password',
            'client_id' => 'nutrients-client',
            'client_secret' => 'PkPLAW1E7x7eQmJMAWa2igKj8RUNlvFy',
            'username' => $username,
            'password' => $password,
            'scope' => 'openid'
        ]);

        return $response->json();
    }
}
