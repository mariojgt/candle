<?php

namespace Mariojgt\Candle;

use Mariojgt\Candle\Models\ApiKey;
use Mariojgt\Candle\Models\Event;
use Mariojgt\Candle\Models\Site;
use Carbon\Carbon;

class Candle
{
    /**
     * Track an event
     *
     * @param string $eventName
     * @param array $properties
     * @param string|null $siteId
     * @param string|null $apiKey
     * @return \Mariojgt\Candle\Models\Event|null
     */
    public function trackEvent($eventName, array $properties = [], $siteId = null, $apiKey = null)
    {
        // Validate site and API key
        $site = null;

        if ($apiKey) {
            $apiKeyModel = ApiKey::validate($apiKey);
            if ($apiKeyModel && $apiKeyModel->hasPermission('write')) {
                $site = $apiKeyModel->site;
            }
        } elseif ($siteId) {
            $site = Site::find($siteId);
        }

        if (!$site) {
            return null;
        }

        // Create the event
        $event = new Event([
            'event_name' => $eventName,
            'site_id' => $site->id,
            'properties' => $properties,
        ]);

        $event->save();

        return $event;
    }

    /**
     * Get sites for a user
     *
     * @param string $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSites($userId)
    {
        return Site::where('user_id', $userId)->get();
    }

    /**
     * Get API keys for a user
     *
     * @param string $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getApiKeys($userId)
    {
        return ApiKey::whereHas('site', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();
    }

    /**
     * Get pageviews for a site
     *
     * @param int $siteId
     * @param string $period
     * @return array
     */
    public function getPageviews($siteId, $period = '30days')
    {
        $query = Event::query()
            ->where('site_id', $siteId)
            ->where('event_name', 'pageview');

        // Apply date filter
        $this->applyDateFilter($query, $period);

        // Get daily pageviews
        $dailyPageviews = $query->select(
            \DB::raw('DATE(created_at) as date'),
            \DB::raw('COUNT(*) as count')
        )
        ->groupBy(\DB::raw('DATE(created_at)'))
        ->orderBy('date', 'asc')
        ->get();

        return [
            'total' => $dailyPageviews->sum('count'),
            'pageviews' => $dailyPageviews
        ];
    }

    /**
     * Get unique visitors for a site
     *
     * @param int $siteId
     * @param string $period
     * @return array
     */
    public function getUniqueVisitors($siteId, $period = '30days')
    {
        $query = Event::query()
            ->where('site_id', $siteId)
            ->where('event_name', 'pageview');

        // Apply date filter
        $this->applyDateFilter($query, $period);

        // Get daily unique visitors
        $dailyVisitors = $query->select(
            \DB::raw('DATE(created_at) as date'),
            \DB::raw('COUNT(DISTINCT session_id) as count')
        )
        ->groupBy(\DB::raw('DATE(created_at)'))
        ->orderBy('date', 'asc')
        ->get();

        return [
            'total' => $dailyVisitors->sum('count'),
            'visitors' => $dailyVisitors
        ];
    }

    /**
     * Get top pages for a site
     *
     * @param int $siteId
     * @param string $period
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTopPages($siteId, $period = '30days', $limit = 10)
    {
        $query = Event::query()
            ->where('site_id', $siteId)
            ->where('event_name', 'pageview');

        // Apply date filter
        $this->applyDateFilter($query, $period);

        // Get top pages
        return $query->select('url', \DB::raw('COUNT(*) as pageviews'))
            ->groupBy('url')
            ->orderBy('pageviews', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get device breakdown for a site
     *
     * @param int $siteId
     * @param string $period
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDevices($siteId, $period = '30days')
    {
        $query = Event::query()
            ->where('site_id', $siteId)
            ->where('event_name', 'pageview');

        // Apply date filter
        $this->applyDateFilter($query, $period);

        // Get devices breakdown
        return $query->select('device_type', \DB::raw('COUNT(*) as count'))
            ->groupBy('device_type')
            ->orderBy('count', 'desc')
            ->get();
    }

    /**
     * Apply date filter to a query based on period
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $period
     * @return void
     */
    protected function applyDateFilter($query, $period)
    {
        $now = Carbon::now();

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', $now->format('Y-m-d'));
                break;
            case 'yesterday':
                $query->whereDate('created_at', $now->subDay()->format('Y-m-d'));
                break;
            case 'week':
                $query->whereDate('created_at', '>=', $now->startOfWeek()->format('Y-m-d'));
                break;
            case 'month':
                $query->whereDate('created_at', '>=', $now->startOfMonth()->format('Y-m-d'));
                break;
            case '30days':
                $query->whereDate('created_at', '>=', $now->subDays(30)->format('Y-m-d'));
                break;
            case '90days':
                $query->whereDate('created_at', '>=', $now->subDays(90)->format('Y-m-d'));
                break;
            default:
                // If dates are provided as custom range
                if (strpos($period, ':') !== false) {
                    list($startDate, $endDate) = explode(':', $period);
                    $query->whereDate('created_at', '>=', $startDate)
                          ->whereDate('created_at', '<=', $endDate);
                }
                break;
        }
    }

    /**
     * Generate a tracking script for a site
     *
     * @param int $siteId
     * @param string|null $apiKey
     * @return string
     */
    public function getTrackingScript($siteId, $apiKey = null)
    {
        $site = Site::find($siteId);

        if (!$site) {
            return '// Site not found';
        }

        // Get the base tracker code
        $trackerCode = file_get_contents(__DIR__ . '/Resources/js/tracker.js');

        // Replace placeholders with actual values
        $trackerCode = str_replace('{{SITE_ID}}', $site->id, $trackerCode);
        $trackerCode = str_replace('{{API_KEY}}', $apiKey ?? "''", $trackerCode);
        $trackerCode = str_replace('{{TRACK_CLICKS}}', $site->settings['track_clicks'] ? 'true' : 'false', $trackerCode);
        $trackerCode = str_replace('{{TRACK_FORMS}}', $site->settings['track_forms'] ? 'true' : 'false', $trackerCode);
        $trackerCode = str_replace('{{TRACK_ROUTE_CHANGES}}', ($site->settings['track_route_changes'] ?? true) ? 'true' : 'false', $trackerCode);
        $trackerCode = str_replace('{{COOKIE_TIMEOUT}}', $site->settings['cookie_timeout'] ?? 30, $trackerCode);

        return $trackerCode;
    }
}
