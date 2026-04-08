<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RideController extends Controller
{
    public function index(Request $request)
    {
        $rides = $request->user()->rides()
            ->with(['driver.user', 'vehicleType', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $rides
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'pickup_address' => 'required|string',
            'pickup_latitude' => 'required|numeric',
            'pickup_longitude' => 'required|numeric',
            'destination_address' => 'required|string',
            'destination_latitude' => 'required|numeric',
            'destination_longitude' => 'required|numeric',
            'pickup_note' => 'nullable|string',
            'destination_note' => 'nullable|string',
            'payment_method' => 'required|in:cash,momo,zalopay,vnpay,card',
        ]);

        try {
            DB::beginTransaction();

            $vehicleType = VehicleType::findOrFail($request->vehicle_type_id);

            // Calculate distance and time (simplified - in real app use Google Maps API)
            $distance = $this->calculateDistance(
                $request->pickup_latitude,
                $request->pickup_longitude,
                $request->destination_latitude,
                $request->destination_longitude
            );

            $estimatedTime = $distance * 3; // Rough estimate: 3 minutes per km

            $fare = $vehicleType->calculateFare($distance, $estimatedTime);

            $ride = Ride::create([
                'user_id' => $request->user()->id,
                'vehicle_type_id' => $request->vehicle_type_id,
                'pickup_address' => $request->pickup_address,
                'pickup_latitude' => $request->pickup_latitude,
                'pickup_longitude' => $request->pickup_longitude,
                'pickup_note' => $request->pickup_note,
                'destination_address' => $request->destination_address,
                'destination_latitude' => $request->destination_latitude,
                'destination_longitude' => $request->destination_longitude,
                'destination_note' => $request->destination_note,
                'estimated_distance' => $distance,
                'estimated_time' => $estimatedTime,
                'base_fare' => $vehicleType->base_fare,
                'distance_fare' => $distance * $vehicleType->per_km_rate,
                'time_fare' => $estimatedTime * $vehicleType->per_minute_rate,
                'final_price' => $fare,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
            ]);

            // Create ride history
            $ride->rideHistories()->create([
                'user_id' => $request->user()->id,
                'status' => 'pending',
                'latitude' => $request->pickup_latitude,
                'longitude' => $request->pickup_longitude,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ride created successfully',
                'data' => $ride->load(['vehicleType'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create ride',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $ride = Ride::with(['driver.user', 'vehicleType', 'payment', 'reviews'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $ride
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $ride = Ride::findOrFail($id);

        if ($ride->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if (!$ride->isPending() && !$ride->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel this ride'
            ], 400);
        }

        $ride->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->reason,
            'cancellation_by' => 'user',
        ]);

        // Create ride history
        $ride->rideHistories()->create([
            'user_id' => $request->user()->id,
            'status' => 'cancelled',
            'notes' => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ride cancelled successfully',
            'data' => $ride
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $latDiff = deg2rad($lat2 - $lat1);
        $lonDiff = deg2rad($lon2 - $lon1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDiff / 2) * sin($lonDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
