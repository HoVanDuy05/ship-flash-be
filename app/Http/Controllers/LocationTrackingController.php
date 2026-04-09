<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Ride;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationTrackingController extends Controller
{
    public function updateLocation(Request $request, $driverId)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0|max:100',
        ]);

        $driver = Driver::findOrFail($driverId);

        if ($driver->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Create location record
            $location = Location::create([
                'locationable_id' => $driver->id,
                'locationable_type' => 'App\\Models\\Driver',
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'accuracy' => $request->accuracy,
                'address' => $request->address ?? null,
            ]);

            // Update driver's current location
            $driver->update([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'last_location_update' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Location updated successfully',
                'data' => $location
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getLocationHistory(Request $request, $driverId)
    {
        $driver = Driver::findOrFail($driverId);

        if ($driver->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $locations = $driver->locations()
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $locations
        ]);
    }

    public function getNearbyDrivers(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.5|max:50',
            'vehicle_type_id' => 'nullable|exists:vehicle_types,id',
        ]);

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $request->radius ?? 10; // Default 10km

        try {
            $query = Driver::online()
                ->verified()
                ->whereNotNull('latitude')
                ->whereNotNull('longitude');

            if ($request->vehicle_type_id) {
                $query->where('vehicle_type_id', $request->vehicle_type_id);
            }

            // Calculate distance using Haversine formula
            $drivers = $query->selectRaw("
                *,
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(latitude)) + 
                sin(radians(longitude) - radians(?)) * 
                sin(radians(latitude))) 
                ) as distance
            ")
                ->addBinding($latitude)
                ->addBinding($longitude)
                ->havingRaw('distance <= ?', [$radius])
                ->with(['user', 'vehicleType'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $drivers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get nearby drivers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDriverLocation(Request $request, $driverId)
    {
        $driver = Driver::findOrFail($driverId);

        if ($driver->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'driver' => $driver->load(['user', 'vehicleType']),
                'locations' => $driver->locations()
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get()
            ]
        ]);
    }

    public function trackRideLocation(Request $request, $rideId)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'status' => 'required|in:accepted,arriving,picked_up,in_progress,completed',
        ]);

        $ride = Ride::findOrFail($rideId);

        if ($ride->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Create location record for ride
            $location = Location::create([
                'locationable_id' => $ride->id,
                'locationable_type' => 'App\\Models\\Ride',
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address' => $request->address ?? null,
            ]);

            // Update ride status
            $ride->update([
                'status' => $request->status,
            ]);

            // Create ride history entry
            $ride->rideHistories()->create([
                'user_id' => $ride->user_id,
                'driver_id' => $ride->driver_id,
                'status' => $request->status,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ride location tracked successfully',
                'data' => $location
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to track ride location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getRideLocations(Request $request, $rideId)
    {
        $ride = Ride::findOrFail($rideId);

        if ($ride->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $locations = $ride->locations()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $locations
        ]);
    }

    public function getSuggestedLocations(Request $request)
    {
        // Get recent destinations from user's ride history
        $suggested = Ride::where('user_id', $request->user()->id)
            ->whereNotNull('destination_address')
            ->select('destination_address as address', 'destination_latitude as latitude', 'destination_longitude as longitude')
            ->distinct()
            ->limit(5)
            ->get()
            ->map(function ($item, $index) {
                $icons = ['home', 'briefcase', 'school', 'hospital', 'shopping'];
                return [
                    'id' => $index + 1,
                    'name' => $this->extractLocationName($item->address),
                    'address' => $item->address,
                    'icon' => $icons[$index % count($icons)],
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $suggested
        ]);
    }

    public function getHotDestinations(Request $request)
    {
        // Mock hot destinations - in production this could come from analytics
        $hot = [
            ['id' => 1, 'name' => 'Vạn Hạnh Mall', 'image' => 'https://picsum.photos/200/150?random=1', 'tag' => 'Mall hot'],
            ['id' => 2, 'name' => 'AEON MALL', 'image' => 'https://picsum.photos/200/150?random=2', 'tag' => 'Mall hot'],
            ['id' => 3, 'name' => 'SC VivoCity', 'image' => 'https://picsum.photos/200/150?random=3', 'tag' => 'Mall hot'],
            ['id' => 4, 'name' => 'Landmark 81', 'image' => 'https://picsum.photos/200/150?random=4', 'tag' => 'Điểm đến'],
            ['id' => 5, 'name' => 'Chợ Bến Thành', 'image' => 'https://picsum.photos/200/150?random=5', 'tag' => 'Chợ'],
        ];

        return response()->json([
            'success' => true,
            'data' => $hot
        ]);
    }

    private function extractLocationName($address)
    {
        // Extract a simple name from address
        $parts = explode(',', $address);
        return trim($parts[0]) ?: 'Địa điểm';
    }
}
