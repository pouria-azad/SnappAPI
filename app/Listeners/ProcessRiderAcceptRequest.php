<?php

namespace App\Listeners;


use App\Interfaces\Services\RideAcceptationServiceInterface;
use Laravel\Reverb\Events\MessageReceived;

class ProcessRiderAcceptRequest
{
    /**
     * Create the event listener.
     */
    public function __construct(public RideAcceptationServiceInterface $ride_acceptation_service)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MessageReceived $event): void
    {
        $message = json_decode($event->message);
        $data    = $message->data;

        // check if rider accepts a ride request
        if ($message->event !== 'riderAcceptRequest' || $message->channel !== 'private-driver.' . $data->driverId) {
            return;
        }

        // Accept Ride Request Logic
        $this->ride_acceptation_service->handle($data->driverId, $data->requestId);
    }
}
