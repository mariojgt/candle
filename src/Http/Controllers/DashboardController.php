<?php

namespace Mariojgt\Candle\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mariojgt\Candle\Models\Site;

class DashboardController extends Controller
{
    /**
     * Display the analytics dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $userId = auth()->id();

        // Get all user's sites
        $sites = Site::where('user_id', $userId)->get();

        if ($sites->isEmpty()) {
            // If user has no sites, redirect to create one
            return redirect()->route('candle.sites.create')
                ->with('info', 'You need to create a site first to view analytics.');
        }

        // Get the requested site or default to the first one
        $siteId = $request->query('site_id');

        if ($siteId) {
            $site = $sites->firstWhere('id', $siteId);

            // If site not found or doesn't belong to user, default to first site
            if (!$site) {
                $site = $sites->first();
            }
        } else {
            $site = $sites->first();
        }

        return view('candle::dashboard', [
            'site' => $site,
            'sites' => $sites
        ]);
    }
}
