<?php

namespace Mariojgt\Candle\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mariojgt\Candle\Models\ApiKey;
use Mariojgt\Candle\Models\Site;

class ApiKeyController extends Controller
{
    /**
     * Display a listing of the API keys.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $userId = auth()->id();

        $siteId = $request->query('site_id');

        $query = ApiKey::query()->with('site');

        if ($siteId) {
            $query->where('site_id', $siteId);
        }

        $query->whereHas('site', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        });

        $apiKeys = $query->latest()->paginate(10);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $apiKeys
            ]);
        }

        return view('candle::api-keys.index', [
            'apiKeys' => $apiKeys
        ]);
    }

    /**
     * Show the form for creating a new API key.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $userId = auth()->id();

        $sites = Site::where('user_id', $userId)->get();

        return view('candle::api-keys.create', [
            'sites' => $sites
        ]);
    }

    /**
     * Store a newly created API key in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $userId = auth()->id();

        $request->validate([
            'name' => 'required|string|max:255',
            'site_id' => 'required|exists:sites,id',
            'permissions' => 'nullable|array',
        ]);

        // Verify site belongs to user
        $site = Site::findOrFail($request->site_id);
        if ($site->user_id != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Site not found'
            ], 404);
        }

        // Create new API key
        $apiKey = new ApiKey([
            'name' => $request->name,
            'site_id' => $request->site_id,
            'user_id' => $userId,
            'key' => ApiKey::generateApiKey(),
            'permissions' => $request->input('permissions', [
                'read' => true,
                'write' => true,
                'admin' => false,
            ]),
            'active' => true,
        ]);

        $apiKey->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'API key created successfully',
                'data' => $apiKey,
                'key' => $apiKey->key, // Send the key once
            ]);
        }

        return redirect()->route('candle.api-keys.show', $apiKey)
            ->with('success', 'API key created successfully')
            ->with('api_key', $apiKey->key); // Flash the key for one-time display
    }

    /**
     * Display the specified API key.
     *
     * @param  \Mariojgt\Candle\Models\ApiKey  $apiKey
     * @return \Illuminate\Http\Response
     */
    public function show(ApiKey $apiKey)
    {
        $userId = auth()->id();

        // Check if the API key belongs to the user
        if ($apiKey->site->user_id != $userId) {
            abort(404);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $apiKey->load('site')
            ]);
        }

        return view('candle::api-keys.show', [
            'apiKey' => $apiKey
        ]);
    }

    /**
     * Update the specified API key in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Mariojgt\Candle\Models\ApiKey  $apiKey
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ApiKey $apiKey)
    {
        $userId = auth()->id();

        // Check if the API key belongs to the user
        if ($apiKey->site->user_id != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'API key not found'
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
        ]);

        $apiKey->name = $request->name;

        if ($request->has('permissions')) {
            $apiKey->permissions = $request->permissions;
        }

        $apiKey->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'API key updated successfully',
                'data' => $apiKey
            ]);
        }

        return redirect()->route('candle.api-keys.show', $apiKey)
            ->with('success', 'API key updated successfully');
    }

    /**
     * Remove the specified API key from storage.
     *
     * @param  \Mariojgt\Candle\Models\ApiKey  $apiKey
     * @return \Illuminate\Http\Response
     */
    public function destroy(ApiKey $apiKey)
    {
        $userId = auth()->id();

        // Check if the API key belongs to the user
        if ($apiKey->site->user_id != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'API key not found'
            ], 404);
        }

        $apiKey->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'API key deleted successfully'
            ]);
        }

        return redirect()->route('candle.api-keys.index')
            ->with('success', 'API key deleted successfully');
    }

    /**
     * Revoke the specified API key.
     *
     * @param  \Mariojgt\Candle\Models\ApiKey  $apiKey
     * @return \Illuminate\Http\Response
     */
    public function revoke(ApiKey $apiKey)
    {
        $userId = auth()->id();

        // Check if the API key belongs to the user
        if ($apiKey->site->user_id != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'API key not found'
            ], 404);
        }

        $apiKey->active = false;
        $apiKey->save();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'API key revoked successfully',
                'data' => $apiKey
            ]);
        }

        return redirect()->route('candle.api-keys.show', $apiKey)
            ->with('success', 'API key revoked successfully');
    }

    /**
     * Activate the specified API key.
     *
     * @param  \Mariojgt\Candle\Models\ApiKey  $apiKey
     * @return \Illuminate\Http\Response
     */
    public function activate(ApiKey $apiKey)
    {
        $userId = auth()->id();

        // Check if the API key belongs to the user
        if ($apiKey->site->user_id != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'API key not found'
            ], 404);
        }

        $apiKey->active = true;
        $apiKey->save();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'API key activated successfully',
                'data' => $apiKey
            ]);
        }

        return redirect()->route('candle.api-keys.show', $apiKey)
            ->with('success', 'API key activated successfully');
    }
}
