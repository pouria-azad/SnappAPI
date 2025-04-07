<?php

namespace App\Services;


use App\Events\RideRequestConfirmed;
use App\Events\RideRequestConfirmedByOthers;
use App\Interfaces\Services\RideAcceptationServiceInterface;
use App\Models\CurrentRide;
use App\Models\Driver;
use App\Models\RequestDriver;
use App\Models\RideRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RideAcceptationService implements RideAcceptationServiceInterface
{


    public function handle(int $driverId, int $requestId): void
    {
        try {
            $validatedData = [
                'driver_id'  => $driverId,
                'request_id' => $requestId,
                'user_id'    => RideRequest::findOrFail($requestId)->user_id ?? null,
            ];

            // Check if the driver is already assigned to another ride
            if (CurrentRide::where('driver_id', $validatedData['driver_id'])->exists()) {
                return;
            }

            DB::transaction(function () use ($validatedData) {
                // Update the ride request status
                RideRequest::findOrFail($validatedData['request_id'])->update(['isPending' => false]);

                // Update driver id status
                Driver::findOrFail($validatedData['driver_id'])->update(['is_active' => false]);

                // Create a new current ride
                CurrentRide::create($validatedData);

                // Trigger event (will triggered after transaction commit)
                event(new RideRequestConfirmed($validatedData['driver_id']));
            });


            // tell other drivers that this ride request is taken.
            $otherDriverIds = RequestDriver::where('request_id', $validatedData['request_id'])
                ->where('driver_id', '!=', $validatedData['driver_id'])
                ->pluck('driver_id')
                ->toArray();
            event(new RideRequestConfirmedByOthers($otherDriverIds));

            Log::info('Ride Request Accepted :', [
                'requestId' => $requestId,
                'driverId'  => $driverId,
            ]);

            return;
        } catch (\Exception $e) {
            Log::error('Failed to accept ride request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data'  => [
                    'requestId' => $requestId,
                    'driverId'  => $driverId,
                ],
            ]);
        }
    }
}
