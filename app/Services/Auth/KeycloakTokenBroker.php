<?php

namespace App\Services\Auth;

use App\Contracts\Auth\TokenBroker;
use App\Classes\Auth\AccessToken;
use App\Exceptions\Auth\IdentityProviderException;
use App\Exceptions\Auth\InvalidCredentialsException;
use Illuminate\Support\Facades\Http;

class KeycloakTokenBroker implements TokenBroker
{
    public function __construct(private readonly array $config) {}

    protected function endpoint(string $path = 'token'): string
    {
        $base = rtrim($this->config['base_url'], '/');
        $realm = $this->config['realm'];
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
            'grant_type'    => $this->config['grant_type'] ?? 'password',
            'client_id'     => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'username'      => $username,
            'password'      => $password,
        ];

        if (!empty($this->config['scope'])) {
            $payload['scope'] = $this->config['scope'];
        }

        $resp = $this->client()->post($this->endpoint('token'), $payload);

        // Handle most common invalid creds signaling
        if ($resp->status() === 400 && in_array($resp->json('error'), ['invalid_grant', 'invalid_request'], true)) {
            throw new InvalidCredentialsException($resp->json('error_description') ?? 'Invalid credentials.');
        }

        if ($resp->failed()) {
            throw new IdentityProviderException(
                "Keycloak token endpoint failed with status {$resp->status()}.",
                status: $resp->status(),
                payload: $resp->json()
            );
        }

        $data = $resp->json();

        // Keycloak sometimes returns 200 with an error body
        if (is_array($data) && array_key_exists('error', $data)) {
            $error = $data['error'];
            $desc  = $data['error_description'] ?? $error;

            if ($error === 'invalid_grant') {
                throw new InvalidCredentialsException($desc);
            }

            throw new IdentityProviderException($desc, status: 502, payload: $data);
        }

        return AccessToken::fromArray($data);
    }

    public function refreshToken(string $refreshToken): AccessToken
    {
        $payload = [
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'refresh_token' => $refreshToken,
        ];

        $resp = $this->client()->post($this->endpoint('token'), $payload);

        if ($resp->failed()) {
            throw new IdentityProviderException(
                "Keycloak refresh failed with status {$resp->status()}.",
                status: $resp->status(),
                payload: $resp->json()
            );
        }

        $data = $resp->json();
        if (isset($data['error'])) {
            throw new IdentityProviderException($data['error_description'] ?? $data['error'], 502, $data);
        }

        return AccessToken::fromArray($data);
    }
}