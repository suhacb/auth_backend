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
}