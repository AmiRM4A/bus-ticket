<?php

namespace Modules\Drivers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Drivers\Database\Factories\DriverFactory;
use Modules\Users\Models\User;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trips_completed',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): DriverFactory
    {
        return DriverFactory::new();
    }
}
