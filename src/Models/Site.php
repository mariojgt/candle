<?php

namespace Mariojgt\Candle\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'domain',
        'user_id',
        'settings',
        'allowed_origins',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'settings' => 'json',
        'allowed_origins' => 'json',
    ];

    /**
     * Get the events for this site.
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get the API keys for this site.
     */
    public function apiKeys()
    {
        return $this->hasMany(ApiKey::class);
    }

    /**
     * Check if a given origin is allowed for this site.
     *
     * @param string $origin
     * @return bool
     */
    public function isOriginAllowed($origin)
    {
        // If no specific origins are set, allow the main domain
        if (empty($this->allowed_origins)) {
            return $this->domainMatches($origin, $this->domain);
        }

        // Check against the list of allowed origins
        foreach ($this->allowed_origins as $allowedOrigin) {
            if ($this->domainMatches($origin, $allowedOrigin)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a domain matches another domain (including wildcard support)
     *
     * @param string $testDomain
     * @param string $allowedDomain
     * @return bool
     */
    private function domainMatches($testDomain, $allowedDomain)
    {
        // Extract domain from origin URL if needed
        if (preg_match('/^https?:\/\/([^\/]+)/', $testDomain, $matches)) {
            $testDomain = $matches[1];
        }

        // Exact match
        if ($testDomain === $allowedDomain) {
            return true;
        }

        // Wildcard match (*.example.com)
        if (substr($allowedDomain, 0, 2) === '*.') {
            $suffixToMatch = substr($allowedDomain, 1); // *.example.com -> .example.com
            return substr($testDomain, -strlen($suffixToMatch)) === $suffixToMatch;
        }

        return false;
    }
}
