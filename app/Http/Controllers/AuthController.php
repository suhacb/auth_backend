<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AuthRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function login(AuthRequest $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');
        $url = config('keycloak.base_url') . '/realms/' . config('keycloak.realm') . '/protocol/openid-connect/token';
        
        // Get token from keycloak
        $response = Http::asForm()->post($url, [
            'grant_type' => config('keycloak.grant_type'),
            'client_id' => config('keycloak.client_id'),
            'client_secret' => config('keycloak.client_secret'),
            'username' => $username,
            'password' => $password,
            'scope' => config('keycloak.scope')
        ]);

        if ($response->failed()) {
            return response()->json($response->json(), $response->status());
        }

        // Keycloak may return 200 even if the credentials are wrong
        if (array_key_exists('error', $response->json())) {
            return response()->json([
                'error' => $response->json('error'),
                'error_description' => $response->json('error_description')
            ], 401);
        }

        // Return the token response from Keycloak
        return $response->json();
    }
}
