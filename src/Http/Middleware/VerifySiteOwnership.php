<?php

namespace Mariojgt\Candle\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Mariojgt\Candle\Models\Site;

class VerifySiteOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $siteId = $request->route('site') ? $request->route('site')->id : $request->input('site_id');

        if (!$siteId) {
            return $next($request);
        }

        $site = Site::findOrFail($siteId);

        if ($site->user_id !== auth()->id()) {
            return abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
