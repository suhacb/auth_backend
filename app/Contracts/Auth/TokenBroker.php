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
    public function validateAccessToken(string $accessToken): bool | AccessToken;

    /**
     * Revoke a given access token via Keycloak.
     *
     * @param string $accessToken The raw access token (without "Bearer ")
     * @return void
     * @throws IdentityProviderException on HTTP errors
     * @throws InvalidUserCredentialsException if the token is invalid
     */
    public function revokeToken(string $accessToken): void;

    /**
     * Validate an access token via Keycloak introspection or attempt to refresh it.
     *
     * This method first validates the provided access token using Keycloak introspection.
     * If the token is invalid or expired, and a refresh token is provided, it will attempt
     * to refresh the access token. Returns either:
     * - `true` if the access token is valid
     * - a new access token string if the refresh succeeds
     * - `false` if both validation and refresh fail
     *
     * @param string $accessToken  The raw access token (without "Bearer ")
     * @param string|null $refreshToken  Optional refresh token for renewal
     * @return bool|string  True if token is valid, new access token if refreshed, or false on failure
     * @throws IdentityProviderException on HTTP errors
     */
    public function validateOrRefresh(string $accessToken, ?string $refreshToken = null): bool|AccessToken;
}