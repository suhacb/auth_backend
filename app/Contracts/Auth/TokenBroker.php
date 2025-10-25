<?php

namespace App\Contracts\Auth;

use App\Classes\Auth\AccessToken;

interface TokenBroker
{
    /**
     * Exchange username/password for an access token with the IdP.
     *
     * @throws InvalidCredentialsException if credentials are wrong.
     * @throws IdentityProviderException   for any other IdP / transport errors.
     */
    public function requestToken(string $username, string $password): AccessToken;

    /**
     * Optionally support refresh token.
     *
     * @throws IdentityProviderException
     */
    public function refreshToken(string $refreshToken): AccessToken;

    /**
     * Validate an access token via Keycloak introspection.
     *
     * @param string $accessToken The raw access token (without "Bearer ")
     * @return bool True if token is active, false if invalid
     * @throws IdentityProviderException on HTTP errors
     */
    public function validateAccessToken(string $accessToken): bool;

    /**
     * Revoke a given access token via Keycloak.
     *
     * @param string $accessToken The raw access token (without "Bearer ")
     * @return void
     * @throws IdentityProviderException on HTTP errors
     * @throws InvalidUserCredentialsException if the token is invalid
     */
    public function revokeToken(string $accessToken): void;
}