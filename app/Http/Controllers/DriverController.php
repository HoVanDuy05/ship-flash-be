<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'nullable|numeric|min:1|max:50',
            'vehicle_type_id' => 'nullable|exists:vehicle_types,id',
        ]);

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $request->radius ?? 10; // Default 10km radius

        $query = Driver::online()
            ->verified()
            ->nearby($latitude, $longitude, $radius);

        if ($request->vehicle_type_id) {
            $query->where('vehicle_type_id', $request->vehicle_type_id);
        }

        $drivers = $query->with(['user', 'vehicleType'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $drivers
        ]);
    }

    public function show($id)
    {
        $driver = Driver::with(['user', 'vehicleType'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $driver
        ]);
    }

    public function updateLocation(Request $request, $id)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $driver = Driver::findOrFail($id);

        // Only the driver themselves can update their location
        if ($driver->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $driver->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'last_location_update' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully',
            'data' => $driver
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:offline,online,busy,break',
        ]);

        $driver = Driver::findOrFail($id);

        // Only the driver themselves can update their status
        if ($driver->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $driver->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $driver
        ]);
    }

    public function getEarnings($id)
    {
        $driver = Driver::findOrFail($id);

        // Only the driver themselves can view their earnings
        if ($driver->user_id !== request()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $totalEarnings = $driver->total_earnings;
        $completedRides = $driver->completed_rides;
        $averageRating = $driver->rating;

        return response()->json([
            'success' => true,
            'data' => [
                'total_earnings' => $totalEarnings,
                'completed_rides' => $completedRides,
                'average_rating' => $averageRating,
            ]
        ]);
    }
}
