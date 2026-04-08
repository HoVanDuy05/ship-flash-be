<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'ride_id',
        'user_id',
        'driver_id',
        'rating',
        'comment',
        'type',
        'is_anonymous',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'is_anonymous' => 'boolean',
    ];

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function scopeUserToDriver($query)
    {
        return $query->where('type', 'user_to_driver');
    }

    public function scopeDriverToUser($query)
    {
        return $query->where('type', 'driver_to_user');
    }

    public function scopeAnonymous($query)
    {
        return $query->where('is_anonymous', true);
    }
}
