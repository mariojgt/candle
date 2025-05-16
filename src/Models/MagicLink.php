<?php

namespace Mariojgt\Candle\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class MagicLink extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns the magic link.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the magic link is valid.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->expires_at->isFuture();
    }
}
