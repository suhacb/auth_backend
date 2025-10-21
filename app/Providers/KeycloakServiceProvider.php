<?php

namespace App\Providers;

use Illuminate\Http\Request;
use App\Contracts\Auth\TokenBroker;
use Illuminate\Support\ServiceProvider;
use App\Services\Auth\KeycloakTokenBroker;

class KeycloakServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Allow package-like config publishing if you want
        $this->mergeConfigFrom(config_path('keycloak.php'), 'keycloak');

        $this->app->bind(TokenBroker::class, function ($app) {
            // return new KeycloakTokenBroker(config('keycloak'));

            // Resolve the current request from the container
            /** @var Request $request */
            $request = $app->make(Request::class);

            // Get the application set by your middleware
            /** @var Application|null $application */
            $application = $request->get('application');

            if (! $application) {
                throw new \RuntimeException('No application set in request');
            }
            
            return new KeycloakTokenBroker(config('keycloak'), $application);
        });
    }

    public function boot(): void
    {
        //
    }
}