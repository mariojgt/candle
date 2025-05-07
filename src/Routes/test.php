<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Mariojgt\Candle\Models\Site;
use Mariojgt\Candle\Models\ApiKey;

/*
|--------------------------------------------------------------------------
| Testing Routes
|--------------------------------------------------------------------------
|
| These routes are only available in the testing and local environments.
| They make it easy to quickly set up test data for development purposes.
|
*/

Route::prefix('candle-test')->group(function () {
    // Create a test user
    Route::get('/create-test-user', function () {
        // Default test user parameters
        $name = request('name', 'Test User');
        $email = request('email', 'test@example.com');
        $password = request('password', 'password');

        // Check if user already exists
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Create the user
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            $message = "Test user created successfully!";
        } else {
            $message = "User with email {$email} already exists!";
        }

        // Return user details
        return response()->json([
            'message' => $message,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'password' => $password,
            ]
        ]);
    });

    // Create a test site with an API key
    Route::get('/create-test-site', function () {
        if (!request('user_id')) {
            return response()->json([
                'error' => 'Please provide a user_id parameter'
            ], 400);
        }

        $userId = request('user_id');
        $siteName = request('name', 'Test Site');
        $domain = request('domain', 'example.com');

        // Create a test site
        $site = Site::create([
            'name' => $siteName,
            'domain' => $domain,
            'user_id' => $userId,
            'settings' => [
                'exclude_bots' => true,
                'anonymize_ips' => true,
                'track_clicks' => true,
                'track_forms' => true,
                'track_route_changes' => true,
                'cookie_timeout' => 30
            ]
        ]);

        // Create an API key for the site
        $apiKey = ApiKey::create([
            'name' => 'Test API Key',
            'key' => \Str::random(32),
            'site_id' => $site->id,
            'user_id' => $userId,
            'active' => true,
            'permissions' => [
                'read' => true,
                'write' => true,
                'admin' => true,
            ]
        ]);

        return response()->json([
            'message' => 'Test site and API key created successfully!',
            'site' => $site,
            'api_key' => [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'key' => $apiKey->key,
            ]
        ]);
    });

    // Create complete test setup (user, site, and API key)
    Route::get('/setup-test-environment', function () {
        // Create test user
        $name = request('name', 'Test User');
        $email = request('email', 'test@example.com');
        $password = request('password', 'password');

        // Check if user already exists
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Create the user
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);
        }

        // Create a test site
        $siteName = request('site_name', 'Test Site');
        $domain = request('domain', 'example.com');

        $site = Site::create([
            'name' => $siteName,
            'domain' => $domain,
            'user_id' => $user->id,
            'settings' => [
                'exclude_bots' => true,
                'anonymize_ips' => true,
                'track_clicks' => true,
                'track_forms' => true,
                'track_route_changes' => true,
                'cookie_timeout' => 30
            ]
        ]);

        // Create an API key for the site
        $apiKey = ApiKey::create([
            'name' => 'Test API Key',
            'key' => \Str::random(32),
            'site_id' => $site->id,
            'user_id' => $user->id,
            'active' => true,
            'permissions' => [
                'read' => true,
                'write' => true,
                'admin' => true,
            ]
        ]);

        return response()->json([
            'message' => 'Test environment setup successfully!',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'password' => $password,
            ],
            'site' => $site,
            'api_key' => [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'key' => $apiKey->key,
            ]
        ]);
    });

    // Delete all test data (DANGEROUS - use with caution!)
    Route::get('/cleanup-test-environment', function () {
        if (!request('confirm') || request('confirm') !== 'yes-i-am-sure') {
            return response()->json([
                'error' => "This will delete all test data! Add '?confirm=yes-i-am-sure' to confirm."
            ], 400);
        }

        // Find test users by email pattern
        $testEmails = ['test@example.com'];

        if (request('email')) {
            $testEmails[] = request('email');
        }

        // Delete user's sites and API keys first (cascade delete will handle related data)
        $users = User::whereIn('email', $testEmails)->get();

        $deletedCount = 0;
        foreach ($users as $user) {
            // Get sites for this user
            $sites = Site::where('user_id', $user->id)->get();

            foreach ($sites as $site) {
                // API keys will be deleted by cascade
                $site->delete();
            }

            // Finally delete the user
            $user->delete();
            $deletedCount++;
        }

        return response()->json([
            'message' => 'Test environment cleaned up successfully!',
            'deleted_users' => $deletedCount,
        ]);
    });

    Route::get('/login', function () {
        // find a random test user
        $user = User::whereLike('email', '%test%')->inRandomOrder()->first();
        if (!$user) {
            return response()->json([
                'error' => 'No test user found!'
            ], 404);
        }
        // Log in the user
        auth()->login($user);

        return response()->json([
            'message' => 'Logged in as test user!',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    });
});
