<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\RideController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\VehicleTypeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\LocationTrackingController;
use App\Http\Controllers\ApiDocumentationController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Vehicle types (public)
Route::get('/vehicle-types', [VehicleTypeController::class, 'index']);
Route::get('/vehicle-types/{id}', [VehicleTypeController::class, 'show']);
Route::post('/vehicle-types/calculate-fare', [VehicleTypeController::class, 'calculateFare']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Ride routes
    Route::get('/rides', [RideController::class, 'index']);
    Route::post('/rides', [RideController::class, 'store']);
    Route::get('/rides/{id}', [RideController::class, 'show']);
    Route::post('/rides/{id}/cancel', [RideController::class, 'cancel']);

    // Driver routes
    Route::get('/drivers', [DriverController::class, 'index']);
    Route::get('/drivers/{id}', [DriverController::class, 'show']);
    Route::put('/drivers/{id}/location', [DriverController::class, 'updateLocation']);
    Route::put('/drivers/{id}/status', [DriverController::class, 'updateStatus']);
    Route::get('/drivers/{id}/earnings', [DriverController::class, 'getEarnings']);

    // Payment routes
    Route::post('/rides/{rideId}/payment', [PaymentController::class, 'processPayment']);
    Route::get('/payments', [PaymentController::class, 'getPaymentHistory']);
    Route::get('/payments/{paymentId}', [PaymentController::class, 'getPaymentDetails']);
    Route::post('/payments/{paymentId}/refund', [PaymentController::class, 'refundPayment']);

    // API Documentation route
    Route::get('/docs', [ApiDocumentationController::class, 'index']);

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
    Route::post('/notifications/send', [NotificationController::class, 'sendNotification']);
    Route::delete('/notifications/{notificationId}', [NotificationController::class, 'deleteNotification']);
    Route::post('/notifications/push', [NotificationController::class, 'pushNotification']);

    // Review routes
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/rides/{rideId}/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/{reviewId}', [ReviewController::class, 'show']);
    Route::put('/reviews/{reviewId}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{reviewId}', [ReviewController::class, 'destroy']);
    Route::get('/drivers/{driverId}/reviews', [ReviewController::class, 'getDriverReviews']);
    Route::get('/rides/{rideId}/reviews', [ReviewController::class, 'getRideReviews']);

    // Location tracking routes
    Route::put('/drivers/{driverId}/location', [LocationTrackingController::class, 'updateLocation']);
    Route::get('/drivers/{driverId}/location-history', [LocationTrackingController::class, 'getLocationHistory']);
    Route::get('/drivers/nearby', [LocationTrackingController::class, 'getNearbyDrivers']);
    Route::get('/drivers/{driverId}/current-location', [LocationTrackingController::class, 'getDriverLocation']);
    Route::post('/rides/{rideId}/track-location', [LocationTrackingController::class, 'trackRideLocation']);
    Route::get('/rides/{rideId}/locations', [LocationTrackingController::class, 'getRideLocations']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
