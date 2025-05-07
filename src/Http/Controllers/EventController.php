<?php

namespace Mariojgt\Candle\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Mariojgt\Candle\Models\Site;
use Mariojgt\Candle\Models\Event;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mariojgt\Candle\Http\Requests\StoreEventRequest;

class EventController extends Controller
{
    /**
     * Store a new event.
     *
     * @param  StoreEventRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEventRequest $request)
    {
        // Get site by API key or domain
        $apiKey = $request->input('api_key');
        $domain = $request->input('domain');

        $site = null;

        if ($apiKey) {
            $apiKeyModel = \Mariojgt\Candle\Models\ApiKey::validate($apiKey);
            if ($apiKeyModel) {
                $site = $apiKeyModel->site;
            }
        } elseif ($domain) {
            $site = Site::where('domain', $domain)->first();

            // Check referer against allowed origins if available
            $referer = $request->header('referer');
            if ($site && $referer && !$site->isOriginAllowed($referer)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Origin not allowed'
                ], 403);
            }
        }

        if (!$site) {
            return response()->json([
                'success' => false,
                'message' => 'Site not found'
            ], 404);
        }

        // Get client IP and anonymize if needed
        $ip = $request->ip();
        if ($site->settings['anonymize_ips'] ?? true) {
            // Anonymize the IP by zeroing out the last part
            $ip = substr($ip, 0, strrpos($ip, '.')) . '.0';
        }

        // Get and process events data
        $events = $request->input('events', []);
        if (!is_array($events)) {
            $events = [$events];
        }

        $savedEvents = [];

        foreach ($events as $eventData) {
            // Create new event
            $event = new Event([
                'event_name' => $eventData['event_name'] ?? 'pageview',
                'site_id' => $site->id,
                'session_id' => $eventData['session_id'] ?? null,
                'user_id' => $eventData['user_id'] ?? null,
                'url' => $eventData['url'] ?? $request->header('referer'),
                'referrer' => $eventData['referrer'] ?? null,
                'ip_address' => $ip,
                'user_agent' => $request->userAgent(),
                'browser' => $this->parseBrowser($request->userAgent()),
                'browser_version' => $this->parseBrowserVersion($request->userAgent()),
                'os' => $this->parseOS($request->userAgent()),
                'os_version' => $this->parseOSVersion($request->userAgent()),
                'device_type' => $this->parseDeviceType($request->userAgent()),
                'screen_width' => $eventData['screen_width'] ?? null,
                'screen_height' => $eventData['screen_height'] ?? null,
                'language' => $eventData['language'] ?? $request->header('Accept-Language'),
                'properties' => $eventData['properties'] ?? null,
            ]);

            $event->save();
            $savedEvents[] = $event;
        }

        return response()->json([
            'success' => true,
            'message' => count($savedEvents) . ' events recorded',
            'events' => $savedEvents
        ]);
    }

    /**
     * Get events listing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $site = $request->attributes->get('site');

        $query = Event::query()->bySite($site->id);

        // Apply filters
        if ($request->has('event_name')) {
            $query->byName($request->event_name);
        }

        if ($request->has('period')) {
            $query->byPeriod($request->period);
        } else {
            // Default to last 30 days
            $query->byPeriod('30days');
        }

        if ($request->has('session_id')) {
            $query->bySession($request->session_id);
        }

        // Pagination
        $perPage = $request->input('per_page', 50);

        $events = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }

    /**
     * Get event counts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function counts(Request $request)
    {
        $site = $request->attributes->get('site');

        $query = Event::query()->bySite($site->id);

        // Apply event name filter if provided
        if ($request->has('event_name')) {
            $query->where('event_name', $request->event_name);
        }

        // Time range
        $startDate = $request->input('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->subDays(30);
        $endDate = $request->input('end_date') ? Carbon::parse($request->end_date) : Carbon::now();

        $query->whereBetween('created_at', [$startDate, $endDate]);

        // Group by date
        $counts = $query->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->groupBy(DB::raw('DATE(created_at)'))
        ->orderBy('date', 'asc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $counts->sum('count'),
                'counts' => $counts
            ]
        ]);
    }

    /**
     * Get page view statistics.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function pageviews(Request $request)
    {
        $site = $request->attributes->get('site');

        $query = Event::query()
            ->bySite($site->id)
            ->where('event_name', 'pageview');

        // Time range
        $startDate = $request->input('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->subDays(30);
        $endDate = $request->input('end_date') ? Carbon::parse($request->end_date) : Carbon::now();

        $query->whereBetween('created_at', [$startDate, $endDate]);

        // Group by date
        $pageviews = $query->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->groupBy(DB::raw('DATE(created_at)'))
        ->orderBy('date', 'asc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $pageviews->sum('count'),
                'pageviews' => $pageviews
            ]
        ]);
    }

    /**
     * Get unique visitors.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function uniqueVisitors(Request $request)
    {
        $site = $request->attributes->get('site');

        $query = Event::query()
            ->bySite($site->id)
            ->where('event_name', 'pageview');

        // Time range
        $startDate = $request->input('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->subDays(30);
        $endDate = $request->input('end_date') ? Carbon::parse($request->end_date) : Carbon::now();

        $query->whereBetween('created_at', [$startDate, $endDate]);

        // Group by date and count unique session IDs
        $visitors = $query->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(DISTINCT session_id) as count')
        )
        ->groupBy(DB::raw('DATE(created_at)'))
        ->orderBy('date', 'asc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $visitors->sum('count'),
                'visitors' => $visitors
            ]
        ]);
    }

    /**
     * Get top pages.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function topPages(Request $request)
    {
        $site = $request->attributes->get('site');

        $query = Event::query()
            ->bySite($site->id)
            ->where('event_name', 'pageview');

        // Time range
        $startDate = $request->input('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->subDays(30);
        $endDate = $request->input('end_date') ? Carbon::parse($request->end_date) : Carbon::now();

        $query->whereBetween('created_at', [$startDate, $endDate]);

        // Get limit
        $limit = $request->input('limit', 10);

        // Group by URL
        $pages = $query->select(
            'url',
            DB::raw('COUNT(*) as pageviews'),
            DB::raw('COUNT(DISTINCT session_id) as visitors')
        )
        ->groupBy('url')
        ->orderBy('pageviews', 'desc')
        ->limit($limit)
        ->get();

        return response()->json([
            'success' => true,
            'data' => $pages
        ]);
    }

    /**
     * Parse browser name from user agent string.
     *
     * @param  string  $userAgent
     * @return string|null
     */
    private function parseBrowser($userAgent)
    {
        // Simple browser detection logic
        $browsers = [
            'Chrome' => '/Chrome\/([0-9\.]+)/',
            'Firefox' => '/Firefox\/([0-9\.]+)/',
            'Safari' => '/Safari\/([0-9\.]+)/',
            'Edge' => '/Edge\/([0-9\.]+)/',
            'IE' => '/MSIE ([0-9\.]+)/',
            'Opera' => '/Opera\/([0-9\.]+)/',
        ];

        foreach ($browsers as $browser => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $browser;
            }
        }

        return null;
    }

    /**
     * Parse browser version from user agent string.
     *
     * @param  string  $userAgent
     * @return string|null
     */
    private function parseBrowserVersion($userAgent)
    {
        // Browser version detection logic
        $patterns = [
            'Chrome' => '/Chrome\/([0-9\.]+)/',
            'Firefox' => '/Firefox\/([0-9\.]+)/',
            'Safari' => '/Version\/([0-9\.]+)/',
            'Edge' => '/Edge\/([0-9\.]+)/',
            'IE' => '/MSIE ([0-9\.]+)/',
            'Opera' => '/Opera\/([0-9\.]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $userAgent, $matches)) {
                return $matches[1] ?? null;
            }
        }

        return null;
    }

    /**
     * Parse OS from user agent string.
     *
     * @param  string  $userAgent
     * @return string|null
     */
    private function parseOS($userAgent)
    {
        // OS detection logic
        $os = [
            'Windows' => '/Windows NT ([0-9\.]+)/',
            'Mac' => '/Macintosh/',
            'iOS' => '/iPhone|iPad|iPod/',
            'Android' => '/Android ([0-9\.]+)/',
            'Linux' => '/Linux/',
        ];

        foreach ($os as $name => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Parse OS version from user agent string.
     *
     * @param  string  $userAgent
     * @return string|null
     */
    private function parseOSVersion($userAgent)
    {
        // OS version detection logic
        $patterns = [
            'Windows' => '/Windows NT ([0-9\.]+)/',
            'Android' => '/Android ([0-9\.]+)/',
            'iOS' => '/OS ([0-9_]+) like Mac OS X/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $userAgent, $matches)) {
                return str_replace('_', '.', $matches[1]) ?? null;
            }
        }

        return null;
    }

    /**
     * Parse device type from user agent string.
     *
     * @param  string  $userAgent
     * @return string
     */
    private function parseDeviceType($userAgent)
    {
        if (preg_match('/Mobile|Android|iPhone|iPad|iPod/', $userAgent)) {
            if (preg_match('/iPad|tablet/i', $userAgent)) {
                return 'tablet';
            }
            return 'mobile';
        }

        return 'desktop';
    }

    public function sessions(Request $request)
    {
        $site = $request->attributes->get('site');

        $sessions = Event::query()
            ->bySite($site->id)
            ->select('session_id', DB::raw('COUNT(*) as events'))
            ->whereNotNull('session_id')
            ->groupBy('session_id')
            ->orderByDesc('events')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sessions
        ]);
    }

    public function referrers(Request $request)
    {
        $site = $request->attributes->get('site');

        $referrers = Event::query()
            ->bySite($site->id)
            ->select('referrer', DB::raw('COUNT(*) as count'))
            ->whereNotNull('referrer')
            ->groupBy('referrer')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $referrers
        ]);
    }

    public function devices(Request $request)
    {
        $site = $request->attributes->get('site');

        $devices = Event::query()
            ->bySite($site->id)
            ->select('device_type', DB::raw('COUNT(*) as count'))
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $devices
        ]);
    }

    public function showSession(Request $request, $sessionId)
    {
        $site = $request->attributes->get('site');

        $events = Event::query()
            ->bySite($site->id)
            ->where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }
}
