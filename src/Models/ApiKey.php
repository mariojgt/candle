<?php

namespace Mariojgt\Candle\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'key',
        'site_id',
        'user_id',
        'active',
        'permissions',
        'last_used_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'key',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
        'permissions' => 'json',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the site that owns the API key.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Generate a new API key.
     *
     * @return string
     */
    public static function generateApiKey()
    {
        return Str::random(32);
    }

    /**
     * Check if the API key has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        if (!$this->active) {
            return false;
        }

        if (isset($this->permissions[$permission])) {
            return (bool) $this->permissions[$permission];
        }

        return false;
    }

    /**
     * Mark the API key as used.
     *
     * @return bool
     */
    public function markAsUsed()
    {
        $this->last_used_at = now();
        return $this->save();
    }

    /**
     * Find an API key by its key value.
     *
     * @param string $key
     * @return \Mariojgt\Candle\Models\ApiKey|null
     */
    public static function findByKey($key)
    {
        return static::where('key', $key)->where('active', true)->first();
    }

    /**
     * Find and validate an API key.
     *
     * @param string $key
     * @return \Mariojgt\Candle\Models\ApiKey|null
     */
    public static function validate($key)
    {
        $apiKey = static::findByKey($key);

        if ($apiKey) {
            $apiKey->markAsUsed();
        }

        return $apiKey;
    }
}
