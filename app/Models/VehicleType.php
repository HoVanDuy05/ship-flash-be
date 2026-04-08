<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'icon',
        'base_fare',
        'per_km_rate',
        'per_minute_rate',
        'min_fare',
        'max_passengers',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'base_fare' => 'decimal:2',
        'per_km_rate' => 'decimal:2',
        'per_minute_rate' => 'decimal:2',
        'min_fare' => 'decimal:2',
        'max_passengers' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }

    public function rides()
    {
        return $this->hasMany(Ride::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function calculateFare($distance, $time)
    {
        $distanceFare = $distance * $this->per_km_rate;
        $timeFare = $time * $this->per_minute_rate;
        $totalFare = $this->base_fare + $distanceFare + $timeFare;

        return max($totalFare, $this->min_fare);
    }
}
