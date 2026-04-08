<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'driver_id',
        'vehicle_type_id',
        'status',
        'pickup_address',
        'pickup_latitude',
        'pickup_longitude',
        'pickup_note',
        'destination_address',
        'destination_latitude',
        'destination_longitude',
        'destination_note',
        'estimated_distance',
        'actual_distance',
        'estimated_time',
        'actual_time',
        'base_fare',
        'distance_fare',
        'time_fare',
        'waiting_fee',
        'discount_amount',
        'final_price',
        'payment_method',
        'payment_status',
        'requested_at',
        'accepted_at',
        'arrived_at',
        'picked_up_at',
        'completed_at',
        'cancelled_at',
        'user_note',
        'driver_note',
        'cancellation_reason',
        'cancellation_by',
    ];

    protected $casts = [
        'pickup_latitude' => 'decimal:8',
        'pickup_longitude' => 'decimal:8',
        'destination_latitude' => 'decimal:8',
        'destination_longitude' => 'decimal:8',
        'estimated_distance' => 'decimal:2',
        'actual_distance' => 'decimal:2',
        'estimated_time' => 'decimal:2',
        'actual_time' => 'decimal:2',
        'base_fare' => 'decimal:2',
        'distance_fare' => 'decimal:2',
        'time_fare' => 'decimal:2',
        'waiting_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_price' => 'decimal:2',
        'requested_at' => 'datetime',
        'accepted_at' => 'datetime',
        'arrived_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function rideHistories()
    {
        return $this->hasMany(RideHistory::class);
    }

    public function locations()
    {
        return $this->morphMany(Location::class, 'locationable');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['accepted', 'arriving', 'picked_up', 'in_progress']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isActive()
    {
        return in_array($this->status, ['accepted', 'arriving', 'picked_up', 'in_progress']);
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }
}
