<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiDocumentationController extends Controller
{
    public function index()
    {
        $apiRoutes = [
            'Authentication' => [
                'POST /api/register' => 'Register new user',
                'POST /api/login' => 'User login',
                'POST /api/logout' => 'User logout',
                'GET /api/profile' => 'Get user profile',
                'PUT /api/profile' => 'Update user profile',
            ],
            'Vehicle Types' => [
                'GET /api/vehicle-types' => 'Get all vehicle types',
                'GET /api/vehicle-types/{id}' => 'Get specific vehicle type',
                'POST /api/vehicle-types/calculate-fare' => 'Calculate ride fare',
            ],
            'Rides' => [
                'GET /api/rides' => 'Get user rides',
                'POST /api/rides' => 'Create new ride',
                'GET /api/rides/{id}' => 'Get specific ride',
                'POST /api/rides/{id}/cancel' => 'Cancel ride',
            ],
            'Drivers' => [
                'GET /api/drivers' => 'Get nearby drivers',
                'GET /api/drivers/{id}' => 'Get driver details',
                'PUT /api/drivers/{id}/location' => 'Update driver location',
                'PUT /api/drivers/{id}/status' => 'Update driver status',
                'GET /api/drivers/{id}/earnings' => 'Get driver earnings',
            ],
        ];

        return view('api-docs', compact('apiRoutes'));
    }
}
