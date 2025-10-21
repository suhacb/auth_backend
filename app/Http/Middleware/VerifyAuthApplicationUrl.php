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
        // Extract application name from header
        $appName = $request->header('X-Application-Name');

        if (!$appName) {
            return response()->json(['error' => 'Application name is required'], 400);
        }

        // Extract application url from header
        $appUrl = $request->header('X-Client-Url');

        if (!$appUrl) {
            return response()->json(['error' => 'Application URL is required'], 400);
        }

        // Check if provided application name is auth-frontend
        if ($appName !== 'auth-frontend') {
            return response()->json(['error' => 'Unauthorized application'], 403);
        }

        // 3. Lookup AUTH application in DB
        $application = Application::where('name', 'auth-frontend')->first();

        // 3. Check if request URL matches allowed URL
        // You can choose to compare request URL or host depending on your needs
        // $requestUrl = $request->fullUrl(); // full URL

        // Example: check if host matches
        // $allowedHost = parse_url($application->url, PHP_URL_HOST);

        if ($appUrl !== $application->url) {
            return response()->json(['error' => 'Unauthorized application'], 403);
        }

        // // Optionally, you can attach the application to the request for downstream use
        // $request->attributes->set('application', $application);

        return $next($request);
    }
}
