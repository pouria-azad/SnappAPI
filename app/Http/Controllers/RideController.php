<?php

namespace App\Http\Controllers;


use App\Http\Requests\CompleteRideRequest;
use App\Interfaces\Controllers\TripInterface;
use App\Interfaces\Services\RideCompletionServiceInterface;
use App\Services\RideCompletionService;

use Illuminate\Http\JsonResponse;

class RideController extends Controller implements TripInterface
{

    private RideCompletionService $rideCompletionService;

    public function __construct(RideCompletionServiceInterface $rideCompletionService)
    {
        $this->rideCompletionService = $rideCompletionService;
    }

    /**
     * Completes a ride and logs the total time.
     *
     * @OA\Post(
     *     path="/api/ride/complete",
     *     summary="Complete a ride",
     *     description="Completes a ride based on the provided trip ID and returns the total time taken.",
     *     tags={"Ride"},
     *     security={{"Bearer": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"trip_id"},
     *             @OA\Property(property="trip_id", type="integer", example=1, description="The ID of the trip to complete")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Ride completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ride completed successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_time", type="string", example="00:45:30", description="Total time of the ride in HH:MM:SS format")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request due to invalid or already completed trip",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid trip ID or ride already completed")
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
     *             @OA\Property(property="message", type="string", example="An error occurred while completing the ride"),
     *             @OA\Property(property="error", type="string", example="Detailed error message", nullable=true)
     *         )
     *     )
     * )
     */
    public function complete(CompleteRideRequest $request): JsonResponse
    {
        try {
            $total_time = $this->rideCompletionService->completeRide($request->trip_id);

            return response()->json([
                'message' => 'Ride completed successfully',
                'data' => [
                    'total_time' => $total_time
                ],
            ], 201);
        } catch (\Exception $e) {
            \Log::error("An error occurred while completing the ride: ", [
                'error' => $e->getMessage(),
                'trip_id' => $request->trip_id ?? null,
            ]);

            return response()->json([
                'message' => "An error occurred while completing the ride",
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

}
