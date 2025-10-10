<?php

namespace App\Providers;

use App\Contracts\Auth\TokenBroker;
use App\Services\Auth\KeycloakTokenBroker;
use Illuminate\Support\ServiceProvider;

class KeycloakServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Allow package-like config publishing if you want
        $this->mergeConfigFrom(config_path('keycloak.php'), 'keycloak');

        $this->app->bind(TokenBroker::class, function ($app) {
            return new KeycloakTokenBroker(config('keycloak'));
        });
    }

    public function boot(): void
    {
        //
    }
}