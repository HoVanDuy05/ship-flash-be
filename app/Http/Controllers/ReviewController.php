<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = $request->user()->reviews()
            ->with(['ride.driver.user', 'ride.vehicleType'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    public function store(Request $request, $rideId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'is_anonymous' => 'boolean',
        ]);

        $ride = Ride::findOrFail($rideId);

        if ($ride->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($ride->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Ride must be completed to review'
            ], 400);
        }

        // Check if user already reviewed this ride
        $existingReview = Review::where('user_id', $request->user()->id)
            ->where('ride_id', $rideId)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this ride'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $review = Review::create([
                'ride_id' => $rideId,
                'user_id' => $request->user()->id,
                'driver_id' => $ride->driver_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'is_anonymous' => $request->is_anonymous ?? false,
            ]);

            // Update driver rating
            if ($ride->driver_id) {
                $driver = $ride->driver;
                $allReviews = Review::where('driver_id', $driver->id)->get();
                $averageRating = $allReviews->avg('rating');
                $totalReviews = $allReviews->count();

                $driver->update([
                    'rating' => round($averageRating, 2),
                    'total_reviews' => $totalReviews,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully',
                'data' => $review->load(['ride.driver'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $reviewId)
    {
        $review = Review::with(['ride.driver.user', 'ride.vehicleType', 'user'])
            ->whereHas('ride', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->findOrFail($reviewId);

        return response()->json([
            'success' => true,
            'data' => $review
        ]);
    }

    public function update(Request $request, $reviewId)
    {
        $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'sometimes|string|max:1000',
            'is_anonymous' => 'sometimes|boolean',
        ]);

        $review = Review::with(['ride'])
            ->whereHas('ride', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->findOrFail($reviewId);

        try {
            DB::beginTransaction();

            $review->update([
                'rating' => $request->rating ?? $review->rating,
                'comment' => $request->comment ?? $review->comment,
                'is_anonymous' => $request->is_anonymous ?? $review->is_anonymous,
            ]);

            // Update driver rating if rating changed
            if ($request->has('rating') && $review->ride->driver_id) {
                $driver = $review->ride->driver;
                $allReviews = Review::where('driver_id', $driver->id)->get();
                $averageRating = $allReviews->avg('rating');
                $totalReviews = $allReviews->count();

                $driver->update([
                    'rating' => round($averageRating, 2),
                    'total_reviews' => $totalReviews,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully',
                'data' => $review->load(['ride.driver'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $reviewId)
    {
        $review = Review::with(['ride'])
            ->whereHas('ride', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->findOrFail($reviewId);

        try {
            DB::beginTransaction();

            $review->delete();

            // Update driver rating
            if ($review->ride->driver_id) {
                $driver = $review->ride->driver;
                $remainingReviews = Review::where('driver_id', $driver->id)->get();
                $averageRating = $remainingReviews->count() > 0 ? $remainingReviews->avg('rating') : 0;
                $totalReviews = $remainingReviews->count();

                $driver->update([
                    'rating' => round($averageRating, 2),
                    'total_reviews' => $totalReviews,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDriverReviews(Request $request, $driverId)
    {
        $reviews = Review::with(['user', 'ride'])
            ->where('driver_id', $driverId)
            ->where('is_anonymous', false)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    public function getRideReviews(Request $request, $rideId)
    {
        $reviews = Review::with(['user', 'ride'])
            ->where('ride_id', $rideId)
            ->where('is_anonymous', false)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }
}
