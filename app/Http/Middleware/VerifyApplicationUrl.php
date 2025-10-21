<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Application;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApplicationUrl
{
    /**
     * This middleware is a route guard to protect the routes
     * which are only allowed to be accessible by the
     * registered applications, typically backends.
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
         * Deny access if $appName is not a registered application
         */
        $application = Application::where(['name' => $appName])->first();
        if (!$application) {
            return response()->json(['error' => 'Unauthorized application'], 403);
        }

        /**
         * Deny access if $appUrl does not match the registered application's URL
         */
        if ($appUrl !== $application->url) {
            return response()->json(['error' => 'Unauthorized application'], 403);
        }

        return $next($request);
    }
}
