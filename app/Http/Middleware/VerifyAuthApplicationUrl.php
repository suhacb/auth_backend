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
        // 1. Extract application name from header, query, or body
        $appName = $request->header('X-Application-Name') ?? $request->query('application');

        if (!$appName) {
            return response()->json(['error' => 'Application name is required'], 400);
        }

        // 2. Lookup AUTH application in DB
        $application = Application::where('name', 'auth-frontend')->first();

        if (!$application) {
            return response()->json(['error' => 'Invalid application'], 403);
        }

        // 3. Check if request URL matches allowed URL
        // You can choose to compare request URL or host depending on your needs
        // $requestUrl = $request->fullUrl(); // full URL
        $requestHost = $request->header('X-Client-Url'); // just the host

        // Example: check if host matches
        // $allowedHost = parse_url($application->url, PHP_URL_HOST);

        if ($requestHost !== $application->url) {
            return response()->json(['error' => 'Unauthorized URL'], 403);
        }

        // // Optionally, you can attach the application to the request for downstream use
        // $request->attributes->set('application', $application);

        return $next($request);
    }
}
