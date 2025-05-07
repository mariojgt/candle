<?php

namespace Mariojgt\Candle\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Mariojgt\Candle\Models\Event|null trackEvent(string $eventName, array $properties = [], string|null $siteId = null, string|null $apiKey = null)
 * @method static \Illuminate\Database\Eloquent\Collection getSites(string $userId)
 * @method static \Illuminate\Database\Eloquent\Collection getApiKeys(string $userId)
 * @method static array getPageviews(int $siteId, string $period = '30days')
 * @method static array getUniqueVisitors(int $siteId, string $period = '30days')
 * @method static \Illuminate\Database\Eloquent\Collection getTopPages(int $siteId, string $period = '30days', int $limit = 10)
 * @method static \Illuminate\Database\Eloquent\Collection getDevices(int $siteId, string $period = '30days')
 * @method static string getTrackingScript(int $siteId, string|null $apiKey = null)
 *
 * @see \Mariojgt\Candle\Candle
 */
class Candle extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'candle';
    }
}
