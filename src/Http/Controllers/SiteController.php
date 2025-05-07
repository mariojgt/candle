<?php

namespace Mariojgt\Candle\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mariojgt\Candle\Models\Site;

class SiteController extends Controller
{
    /**
     * Display a listing of the sites.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        $sites = Site::where('user_id', $userId)->latest()->paginate(10);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $sites
            ]);
        }

        return view('candle::sites.index', [
            'sites' => $sites
        ]);
    }

    /**
     * Show the form for creating a new site.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Make sure we're not passing any $site variable to create view
        return view('candle::sites.create');
    }

    /**
     * Store a newly created site in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255',
            'allowed_origins' => 'nullable|string', // Changed from array to string for textarea
            'settings' => 'nullable|array',
        ]);

        // Process allowed origins from textarea to array
        $allowedOrigins = null;
        if ($request->has('allowed_origins') && !empty($request->allowed_origins)) {
            $allowedOrigins = array_filter(
                array_map('trim', explode("\n", $request->allowed_origins)),
                function ($value) { return !empty($value); }
            );
        }

        $site = new Site([
            'name' => $request->name,
            'domain' => $request->domain,
            'user_id' => auth()->id(),
            'allowed_origins' => $allowedOrigins,
            'settings' => $request->input('settings', [
                'exclude_bots' => true,
                'anonymize_ips' => true,
                'track_clicks' => true,
                'track_forms' => true,
                'cookie_timeout' => 30
            ]),
        ]);

        $site->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Site created successfully',
                'data' => $site
            ], 201);
        }

        return redirect()->route('candle.sites.show', $site)
            ->with('success', 'Site created successfully');
    }

    /**
     * Display the specified site.
     *
     * @param  \Mariojgt\Candle\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function show(Site $site)
    {
        $userId = auth()->id();

        // Check if the site belongs to the user
        if ($site->user_id != $userId) {
            abort(404);
        }

        // Get API keys for this site
        $apiKeys = $site->apiKeys;

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'site' => $site,
                    'api_keys' => $apiKeys,
                ]
            ]);
        }

        // Get user's other sites for the site selector
        $userSites = Site::where('user_id', $userId)->get();

        return view('candle::sites.show', [
            'site' => $site,
            'apiKeys' => $apiKeys,
            'sites' => $userSites
        ]);
    }

    /**
     * Show the form for editing the specified site.
     *
     * @param  \Mariojgt\Candle\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function edit(Site $site)
    {
        $userId = auth()->id();

        // Check if the site belongs to the user
        if ($site->user_id != $userId) {
            abort(404);
        }

        // Convert allowed_origins array to string for the textarea
        $allowedOriginsText = '';
        if (!empty($site->allowed_origins)) {
            $allowedOriginsText = implode("\n", $site->allowed_origins);
        }

        return view('candle::sites.edit', [
            'site' => $site,
            'allowed_origins_text' => $allowedOriginsText
        ]);
    }

    /**
     * Update the specified site in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Mariojgt\Candle\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Site $site)
    {
        $userId = auth()->id();

        // Check if the site belongs to the user
        if ($site->user_id != $userId) {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255',
            'allowed_origins' => 'nullable|string', // Changed from array to string for textarea
            'settings' => 'nullable|array',
        ]);

        $site->name = $request->name;
        $site->domain = $request->domain;

        // Process allowed origins from textarea to array
        if ($request->has('allowed_origins')) {
            $allowedOrigins = null;
            if (!empty($request->allowed_origins)) {
                $allowedOrigins = array_filter(
                    array_map('trim', explode("\n", $request->allowed_origins)),
                    function ($value) { return !empty($value); }
                );
            }
            $site->allowed_origins = $allowedOrigins;
        }

        if ($request->has('settings')) {
            $site->settings = array_merge($site->settings ?? [], $request->settings);
        }

        $site->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Site updated successfully',
                'data' => $site
            ]);
        }

        return redirect()->route('candle.sites.show', $site)
            ->with('success', 'Site updated successfully');
    }

    /**
     * Remove the specified site from storage.
     *
     * @param  \Mariojgt\Candle\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function destroy(Site $site)
    {
        $userId = auth()->id();

        // Check if the site belongs to the user
        if ($site->user_id != $userId) {
            abort(404);
        }

        // Warning: This will also delete all events and API keys associated with this site
        // due to the foreign key constraints with onDelete('cascade')
        $site->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Site deleted successfully'
            ]);
        }

        return redirect()->route('candle.sites.index')
            ->with('success', 'Site deleted successfully');
    }

    /**
     * Get the JavaScript tracking code for the site.
     *
     * @param  \Mariojgt\Candle\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function getTrackingCode(Site $site)
    {
        $userId = auth()->id();

        // Check if the site belongs to the user
        if ($site->user_id != $userId) {
            abort(404);
        }

        // Get the base tracker code
        $trackerCode = file_get_contents(__DIR__ . '/../../Resources/js/tracker.js');

        // Replace placeholders with actual values
        $trackerCode = str_replace('{{SITE_ID}}', $site->id, $trackerCode);
        $trackerCode = str_replace('{{API_KEY}}', "''", $trackerCode); // Empty by default for security
        $trackerCode = str_replace('{{TRACK_CLICKS}}', $site->settings['track_clicks'] ? 'true' : 'false', $trackerCode);
        $trackerCode = str_replace('{{TRACK_FORMS}}', $site->settings['track_forms'] ? 'true' : 'false', $trackerCode);
        $trackerCode = str_replace('{{TRACK_ROUTE_CHANGES}}', ($site->settings['track_route_changes'] ?? true) ? 'true' : 'false', $trackerCode);
        $trackerCode = str_replace('{{COOKIE_TIMEOUT}}', $site->settings['cookie_timeout'] ?? 30, $trackerCode);

        $trackingCode = '<script>' . $trackerCode . '</script>';

        return response()->json([
            'success' => true,
            'data' => [
                'script_tag' => $trackingCode,
                'script_url' => route('candle.tracker') . '?site_id=' . $site->id
            ]
        ]);
    }
}
