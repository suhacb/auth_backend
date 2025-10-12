<?php

namespace App\Http\Controllers;

use App\Models\LoginToken;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\AuthRequest;
use App\Contracts\Auth\TokenBroker;
use Illuminate\Http\Client\ConnectionException;
use App\Exceptions\Auth\IdentityProviderException;
use App\Exceptions\Auth\InvalidCredentialsException;

class AuthController extends Controller
{
    public function __construct(private readonly TokenBroker $broker) {}
    
    public function login(AuthRequest $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');
        // $url = config('keycloak.base_url') . '/realms/' . config('keycloak.realm') . '/protocol/openid-connect/token';

        try{
            $response = $this->broker->requestToken($username, $password);
            return $response->toArray();
        } catch (InvalidCredentialsException $e) {
            return response()->json(
                [
                    'errors' => [
                        'username' => $e->getMessage(),
                        'password' => $e->getMessage(),
                    ]
                ], $e->status);
        } catch (IdentityProviderException $e) {
            return response()->json([
              'error_message' => $e->getMessage()  
            ], 500);
        } catch (ConnectionException $e) {
            // Service unreachable
            return response()->json([
                'error_message' => 'Authentication service is unreachable. Please try again later.',
            ], 500);
        }
    }

    public function refresh(): JsonResponse
    {
        request()->validate(['refresh_token' => 'required|string']);
        try {
            $token = $this->broker->refreshToken(request('refresh_token'));
            return response()->json($token->toArray(), 200);
        } catch (IdentityProviderException $e) {
            return response()->json([
                'error' => 'refresh_failed',
                'error_description' => 'Unable to refresh token.',
            ], 400);
        }
    }
    /**
     * Returns a one-time login token. This token is passed from
     * application to auth frontends and the auth frontend
     * will then pass it along with user credentials.
     */
    public function loginToken (AuthRequest $request) {
        $login_token = LoginToken::create(['app' => $request->json('app')]);
        return response()->json($login_token, 200);
    }
}
