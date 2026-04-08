<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_type_id',
        'license_number',
        'license_expiry',
        'license_front',
        'license_back',
        'license_plate',
        'vehicle_brand',
        'vehicle_model',
        'vehicle_color',
        'vehicle_year',
        'vehicle_registration',
        'registration_expiry',
        'vehicle_front',
        'vehicle_back',
        'latitude',
        'longitude',
        'last_location_update',
        'status',
        'is_verified',
        'verified_at',
        'verification_notes',
        'rating',
        'total_rides',
        'total_earnings',
        'completed_rides',
        'cancelled_rides',
        'accepts_cash',
        'accepts_electronic',
        'preferred_areas',
        'max_distance',
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'registration_expiry' => 'date',
        'last_location_update' => 'datetime',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
        'rating' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'accepts_cash' => 'boolean',
        'accepts_electronic' => 'boolean',
        'preferred_areas' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function rides()
    {
        return $this->hasMany(Ride::class);
    }

    public function locations()
    {
        return $this->morphMany(Location::class, 'locationable');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function rideHistories()
    {
        return $this->hasMany(RideHistory::class);
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeNearby($query, $latitude, $longitude, $radius = 10)
    {
        return $query->selectRaw('*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance', [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radius)
            ->orderBy('distance');
    }
}
