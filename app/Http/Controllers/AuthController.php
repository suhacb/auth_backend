<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\Auth\LoginToken;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\AuthRequest;
use App\Contracts\Auth\TokenBroker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use App\Exceptions\Auth\IdentityProviderException;
use App\Exceptions\Auth\InvalidUserCredentialsException;
use App\Exceptions\Auth\InvalidClientCredentialsException;

class AuthController extends Controller
{
    public function __construct(private readonly TokenBroker $broker) {}
    
    public function login(AuthRequest $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        try{
            $response = $this->broker->requestToken($username, $password);
            return $response->toArray();
        } catch (InvalidUserCredentialsException $e) {
            return response()->json(
                [
                    'errors' => [
                        'username' => $e->getMessage(),
                        'password' => $e->getMessage(),
                    ]
                ], 401);
        } catch (InvalidClientCredentialsException $e) {
            return response()->json(
                [
                    'errors' => [
                        'error' => $e->getMessage(),
                    ]
                ], 500);
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
        $loginToken = new LoginToken(
            app: $request->json('app')
        );

        return response()->json($loginToken->token, 200);
    }

    public function validateAccessToken(Request $request): JsonResponse
    {
        $token = $request->header('Authorization');
        $application = $request->attributes->get('application');
        $base_url = config('keycloak.base_url');
        $url = $base_url . '/realms/' . $application->realm . '/protocol/openid-connect/token/introspect';

        if (!$token || !str_starts_with($token, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!$application) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $response = Http::asForm()->post($url, [
            'client_id' => $application->client_id,
            'client_secret' => $application->client_secret,
            'token' => substr($token, 7) // remove "Bearer "
        ]);

        if ($response->json('active') === true) {
            return response()->json($response->json('active'), $response->status());
        }

        return response()->json($response->body(), 401);
    }
}
