<?php

namespace Mariojgt\Candle\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_name',
        'site_id',
        'session_id',
        'user_id',
        'url',
        'referrer',
        'ip_address',
        'user_agent',
        'browser',
        'browser_version',
        'os',
        'os_version',
        'device_type',
        'screen_width',
        'screen_height',
        'language',
        'country',
        'region',
        'city',
        'properties',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'properties' => 'json',
        'screen_width' => 'integer',
        'screen_height' => 'integer',
    ];

    /**
     * Get the site that owns the event.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Scope a query to only include events from a given site.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $siteId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySite($query, $siteId)
    {
        return $query->where('site_id', $siteId);
    }

    /**
     * Scope a query to only include events with a given name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $eventName
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByName($query, $eventName)
    {
        return $query->where('event_name', $eventName);
    }

    /**
     * Scope a query to only include events from a given session.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $sessionId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope a query to only include events from a given time period.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $period
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPeriod($query, $period)
    {
        $now = now();

        switch ($period) {
            case 'today':
                return $query->whereDate('created_at', $now->format('Y-m-d'));
            case 'yesterday':
                return $query->whereDate('created_at', $now->subDay()->format('Y-m-d'));
            case 'week':
                return $query->whereDate('created_at', '>=', $now->startOfWeek()->format('Y-m-d'));
            case 'month':
                return $query->whereDate('created_at', '>=', $now->startOfMonth()->format('Y-m-d'));
            case '30days':
                return $query->whereDate('created_at', '>=', $now->subDays(30)->format('Y-m-d'));
            case '90days':
                return $query->whereDate('created_at', '>=', $now->subDays(90)->format('Y-m-d'));
            default:
                return $query;
        }
    }
}
