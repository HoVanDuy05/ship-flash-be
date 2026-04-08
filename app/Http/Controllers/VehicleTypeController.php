<?php

namespace App\Http\Controllers;

use App\Models\VehicleType;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Vehicle Types",
 *     description="API Endpoints for Vehicle Types"
 * )
 */

class VehicleTypeController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/vehicle-types",
     *      operationId="getVehicleTypes",
     *      tags={"Vehicle Types"},
     *      summary="Get all vehicle types",
     *      description="Get list of all active vehicle types",
     *      @OA\Response(
     *          response=200,
     *          description="Vehicle types retrieved successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object")
     *              )
     *          )
     *      )
     * )
     */
    public function index()
    {
        $vehicleTypes = VehicleType::active()->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $vehicleTypes
        ]);
    }

    public function show($id)
    {
        $vehicleType = VehicleType::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $vehicleType
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/vehicle-types/calculate-fare",
     *      operationId="calculateFare",
     *      tags={"Vehicle Types"},
     *      summary="Calculate ride fare",
     *      description="Calculate fare for a ride based on vehicle type, distance and time",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"vehicle_type_id","distance","time"},
     *              @OA\Property(property="vehicle_type_id", type="integer", example=1),
     *              @OA\Property(property="distance", type="number", format="float", example=5.5),
     *              @OA\Property(property="time", type="number", format="float", example=15.0)
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Fare calculated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="base_fare", type="number", format="float", example=15000),
     *                  @OA\Property(property="distance_fare", type="number", format="float", example=27500),
     *                  @OA\Property(property="time_fare", type="number", format="float", example=7500),
     *                  @OA\Property(property="total_fare", type="number", format="float", example=50000),
     *                  @OA\Property(property="min_fare", type="number", format="float", example=25000)
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation errors",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validation errors"),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
     */
    public function calculateFare(Request $request)
    {
        $request->validate([
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'distance' => 'required|numeric|min:0',
            'time' => 'required|numeric|min:0',
        ]);

        $vehicleType = VehicleType::findOrFail($request->vehicle_type_id);
        $fare = $vehicleType->calculateFare($request->distance, $request->time);

        return response()->json([
            'success' => true,
            'data' => [
                'base_fare' => $vehicleType->base_fare,
                'distance_fare' => $request->distance * $vehicleType->per_km_rate,
                'time_fare' => $request->time * $vehicleType->per_minute_rate,
                'total_fare' => $fare,
                'min_fare' => $vehicleType->min_fare,
            ]
        ]);
    }
}
