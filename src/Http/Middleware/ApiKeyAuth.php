<?php

namespace Mariojgt\Candle\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Mariojgt\Candle\Models\ApiKey;
use Mariojgt\Candle\Models\Site;

class ApiKeyAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission = null)
    {
        // If the request is from an authenticated user viewing the dashboard
        // Allow them to bypass API key authentication

        if (auth()->check() && $request->header('X-Dashboard-Request') === 'true') {
            $siteId = $request->query('site_id');

            if ($siteId) {
                $site = Site::find($siteId);

                // Verify the site belongs to the authenticated user
                if ($site && $site->user_id == auth()->id()) {
                    $request->attributes->add([
                        'site' => $site,
                    ]);

                    return $next($request);
                }
            }
        }

        // Check for API key in header
        $apiKeyValue = $request->header('X-API-Key');

        // If not in header, check query string
        if (!$apiKeyValue) {
            $apiKeyValue = $request->query('api_key');
        }

        // If still not found, check request body
        if (!$apiKeyValue && $request->isMethod('post')) {
            $apiKeyValue = $request->input('api_key');
        }

        if (!$apiKeyValue) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required'
            ], 401);
        }

        // Find and validate the API key
        $apiKey = ApiKey::where('key', $apiKeyValue)
                        ->where('active', true)
                        ->first();

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key'
            ], 401);
        }

        // Check for specific permission if required
        if ($permission && !isset($apiKey->permissions[$permission]) || !$apiKey->permissions[$permission]) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions'
            ], 403);
        }

        // Update last used timestamp
        $apiKey->last_used_at = now();
        $apiKey->save();

        // Add the API key and site to the request
        $request->attributes->add([
            'api_key' => $apiKey,
            'site' => $apiKey->site,
        ]);

        return $next($request);
    }
}
