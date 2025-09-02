<?php

namespace Modules\Locations\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Locations\Database\Factories\ProvinceFactory;

class Province extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ProvinceFactory
    {
        return ProvinceFactory::new();
    }
}
