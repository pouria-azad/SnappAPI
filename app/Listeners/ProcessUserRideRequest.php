<?php

namespace App\Listeners;

use App\Events\RideRequestBroadcast;
use App\Events\RideRequestCreated;
use App\Models\RequestDriver;
use App\Services\RidePreparationService;

class ProcessUserRideRequest
{

    public RidePreparationService $RidePreparationService;
    /**
     * Create the event listener.
     */
    public function __construct(RidePreparationService $RidePreparationService)
    {
        $this->RidePreparationService = $RidePreparationService;
    }

    /**
     * Handle the event.
     */
    public function handle(RideRequestCreated $event): void
    {
        $driverIds = collect([]);
        $nearbyDrivers = $this->RidePreparationService->findNearbyDrivers($event->rideRequest->pickup_latitude, $event->rideRequest->pickup_longitude, 99999999);

        foreach( $nearbyDrivers as $nearbyDriver )
        {
            $driverIds->push($nearbyDriver->id);
            RequestDriver::create(['request_id' => $event->rideRequest->id, 'driver_id' => $nearbyDriver->id, ]);
        }

        $tripCost = $event->rideRequest->cost;

        event( new RideRequestBroadcast($event->rideRequest->id ,$event->rideRequest->pickup_latitude, $event->rideRequest->pickup_longitude, $driverIds->all(),  $tripCost));

    }
}
