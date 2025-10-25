<?php

namespace App\Services\Auth;

use App\Classes\Auth\AccessToken;
use App\Contracts\Auth\TokenBroker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Exceptions\Auth\IdentityProviderException;
use App\Exceptions\Auth\InvalidClientCredentialsException;
use App\Exceptions\Auth\InvalidCredentialsException;
use App\Exceptions\Auth\InvalidUserCredentialsException;
use App\Models\Application;

class KeycloakTokenBroker implements TokenBroker
{
    public function __construct(private readonly array $config, private readonly Application $application) {}

    protected function endpoint(string $path = 'token'): string
    {
        $base = rtrim($this->config['base_url'], '/');
        $realm = $this->application->realm;
        return "{$base}/realms/{$realm}/protocol/openid-connect/{$path}";
    }

    protected function client()
    {
        $retryTimes = (int) ($this->config['retry']['times'] ?? 1);
        $retrySleep = (int) ($this->config['retry']['sleep'] ?? 100);
        $timeout    = (int) ($this->config['timeout'] ?? 5);

        return Http::asForm()
            ->timeout($timeout)
            ->retry($retryTimes, $retrySleep, throw: false);
    }

    public function requestToken(string $username, string $password): AccessToken
    {
        $payload = [
            'grant_type'    => $this->application->grant_type,
            'client_id'     => $this->application->client_id,
            'client_secret' => $this->application->client_secret,
            'username'      => $username,
            'password'      => $password,
            'scope'         => 'openid profile email'
        ];

        $response = $this->client()->post($this->endpoint('token'), $payload);

        // Wrong grant_type
        if ($response->status() === 400 && in_array($response->json('error'), ['unsupported_grant_type'], true)) {
            throw new InvalidClientCredentialsException($response->json('error_description') ?? 'unsupported_grant_type.');
        }

        // Wrong client_id or client_secret
        if ($response->status() === 401 && in_array($response->json('error'), ['invalid_client', 'unauthorized_client'], true)) {
            throw new InvalidClientCredentialsException($response->json('error_description') ?? 'Invalid client or Invalid client credentials.');
        }

        // Wrong username or password
        if ($response->status() === 401 && in_array($response->json('error'), ['invalid_grant'], true)) {
            throw new InvalidUserCredentialsException($response->json('error_description') ?? 'Invalid user credentials.');
        }

        // Handle most common invalid creds signaling
        if ($response->status() === 400) {
            throw new InvalidClientCredentialsException($response->json());
        }

        if ($response->failed()) {
            throw new IdentityProviderException(
                "Keycloak token endpoint failed with status {$response->status()}.",
                status: $response->status(),
                payload: $response->json()
            );
        }

        $data = $response->json();

        return AccessToken::fromArray($data);
    }

    public function refreshToken(string $refreshToken): AccessToken
    {
        $payload = [
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->application->client_id,
            'client_secret' => $this->application->client_secret,
            'refresh_token' => $refreshToken,
        ];

        $response = $this->client()->post($this->endpoint('token'), $payload);

        if ($response->failed()) {
            throw new IdentityProviderException(
                "Keycloak refresh failed with status {$response->status()}.",
                status: $response->status(),
                payload: $response->json()
            );
        }

        $data = $response->json();
        if (isset($data['error'])) {
            throw new IdentityProviderException($data['error_description'] ?? $data['error'], 502, $data);
        }

        return AccessToken::fromArray($data);
    }

    /**
     * Validate an access token via Keycloak introspection.
     *
     * @param string $accessToken The raw access token (without "Bearer ")
     * @return bool True if token is active, false if invalid
     * @throws IdentityProviderException on HTTP errors
     */
    public function validateAccessToken(string $accessToken): bool
    {
        $response = $this->client()->post(
            $this->endpoint('token/introspect'),
            [
                'client_id' => $this->application->client_id,
                'client_secret' => $this->application->client_secret,
                'token' => $accessToken,
            ]
        );

        if ($response->failed()) {
            throw new IdentityProviderException(
                "Keycloak token introspection failed with status {$response->status()}.",
                status: $response->status(),
                payload: $response->json()
            );
        }

        $data = $response->json();

        return isset($data['active']) && $data['active'] === true;
    }

    public function revokeToken(string $accessToken): void
    {
        $response = $this->client()->post($this->endpoint('revoke'), [
            'client_id'     => $this->application->client_id,
            'client_secret' => $this->application->client_secret,
            'token'         => $accessToken,
            'token_type_hint' => 'access_token',
        ]);

        $body = $response->json();

        if ($response->ok() && empty($body)) {
            // Token successfully revoked
            return;
        }

        if (isset($body['error']) && $body['error'] === 'invalid_token') {
            throw new InvalidUserCredentialsException('Invalid access token.');
        }

        // Throw for other unexpected responses
        if ($response->failed()) {
            throw new IdentityProviderException(
                "Keycloak revoke token failed with status {$response->status()}.",
                status: $response->status(),
                payload: $body
            );
        }
    }
}