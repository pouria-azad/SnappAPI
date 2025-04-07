<?php

namespace App\Http\Controllers;

use App\Events\RideRequestConfirmed;
use App\Events\RideRequestCreated;
use App\Http\Requests\AcceptRideRequestRequest;
use App\Http\Requests\CancelRideRequestRequest;
use App\Http\Requests\NewRideRequestRequest;
use App\Interfaces\Controllers\RideRequestInterface;
use App\Models\CurrentRide;
use App\Models\Driver;
use App\Models\RideRequest;
use App\Services\RidePreparationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RideRequestController extends Controller implements RideRequestInterface
{

    public RidePreparationService $ridePreparationService;

    public function __construct(RidePreparationService $ridePreparationService){
        $this->ridePreparationService = $ridePreparationService;
    }


/**
 * @OA\Post(
 *     path="/api/ride-requests/store",
 *     summary="Create a new ride request",
 *     tags={"Ride Requests"},
 *     security={{"Bearer": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"pickup_latitude", "pickup_longitude", "dest_latitude", "dest_longitude"},
 *             @OA\Property(property="pickup_latitude", type="number", format="float", example=35.6892, description="Latitude of the pickup location"),
 *             @OA\Property(property="pickup_longitude", type="number", format="float", example=51.3890, description="Longitude of the pickup location"),
 *             @OA\Property(property="dest_latitude", type="number", format="float", example=35.7000, description="Latitude of the destination"),
 *             @OA\Property(property="dest_longitude", type="number", format="float", example=51.4000, description="Longitude of the destination")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Ride request created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Ride request created successfully"),
 *             @OA\Property(property="request_id", type="integer", example=1),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1, description="Ride request ID"),
 *                 @OA\Property(property="user_id", type="integer", example=1, description="User ID"),
 *                 @OA\Property(property="cost", type="number", format="float", example=15000.50, description="Cost of the ride"),
 *                 @OA\Property(property="distance_km", type="number", format="float", example=5.2, description="Distance in kilometers")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request due to existing pending request or current ride",
 *         @OA\JsonContent(
 *             oneOf={
 *                 @OA\Schema(
 *                     @OA\Property(property="message", type="string", example="You already have a pending request!")
 *                 ),
 *                 @OA\Schema(
 *                     @OA\Property(property="message", type="string", example="You are already assigned to another ride")
 *                 )
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="User not authenticated",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthorized. Please log in.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="An error occurred while creating the ride request"),
 *             @OA\Property(property="error", type="string", example="Detailed error message", nullable=true)
 *         )
 *     )
 * )
 */
    public function store(NewRideRequestRequest $request): JsonResponse
    {
        try {

            $trip = $this->ridePreparationService->calculateTripCost($request['pickup_latitude'], $request['pickup_longitude'], $request['dest_latitude'], $request['dest_longitude']);

            $validatedData = collect($request->validated())
            ->put('user_id', $request->user('user')->id)
            ->put('cost', $trip['cost'])
            ->put('distance_km', $trip['distance_km'])
            ->all();


            if (RideRequest::where('user_id', $validatedData['user_id'])->where('isPending', true)->exists()) {
                return response()->json([
                    'message' => "You already have a pending request!",
                ], 400);
            }

            if (CurrentRide::where('user_id', $validatedData['user_id'])->exists()) {
                return response()->json([
                    'message' => 'you are already assigned to another ride',
                ], 400);
            }

            $rideRequest = RideRequest::create($validatedData);

            \Log::info('Ride request created successfully', [
                'request_id' => $rideRequest->id,
                'user_id' => $request->user('user')->id ?? null,
                'data' => $request->validated(),
            ]);

            event(new RideRequestCreated($rideRequest));

            return response()->json([
                'message' => 'Ride request created successfully',
                'request_id' => $rideRequest->id,
                'data' => $rideRequest->only(['id', 'user_id', 'cost', 'distance_km']),
            ], 201);
        } catch(\Exception $e){

            \Log::error('Failed to create ride request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'An error occurred while creating the ride request',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }

    }

    public function cancel(Request $request): JsonResponse
    {
        $userId = $request->user('user')->id;

        $pendingRideRequest = RideRequest::where('user_id', $userId)
            ->where('isPending', true)
            ->first();

        if ($pendingRideRequest) {
            $pendingRideRequest->delete();
            return response()->json(status:204);
        }
        return response()->json([
            "message" => "No Pending Request!",
        ], 404);
    }
}
