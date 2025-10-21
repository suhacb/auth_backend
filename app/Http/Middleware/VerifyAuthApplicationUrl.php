<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Application;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyAuthApplicationUrl
{
    /**
     * This middleware is a route guard to protect the routes
     * which are only allowed to be accessible by the
     * dedicated Auth frontend.
     */

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /**
         * Extract application name and URL from header
         */
        $appName = $request->header('X-Application-Name') ?? null;
        $appUrl = $request->header('X-Client-Url') ?? null;

        /**
         * Deny access when $appName or $appUrl is null
         */
        if (!$appName) {
            return response()->json(['error' => 'Application name is required'], 400);
        }

        if (!$appUrl) {
            return response()->json(['error' => 'Application URL is required'], 400);
        }

        /**
         * Deny access if $appName is not auth-frontend
         */
        if ($appName !== 'auth-frontend') {
            return response()->json(['error' => 'Unauthorized application'], 403);
        }

        // Lookup auth-frontend application in DB
        $auth_frontend_application = Application::where('name', 'auth-frontend')->first();

        /**
         * Deny access if $appUrl does not match auth-frontend application URL
         */
        if ($appUrl !== $auth_frontend_application->url) {
            return response()->json(['error' => 'Unauthorized application'], 403);
        }

        // Store application in request for later processing in KeycloakTokenBroker
        $request->attributes->set('application', $auth_frontend_application);

        return $next($request);
    }
}
