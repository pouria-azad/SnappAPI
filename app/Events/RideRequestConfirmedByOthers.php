<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RideRequestConfirmedByOthers implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $driverIds;

    /**
     * Create a new event instance.
     */
    public function __construct($driverIds)
    {
        $this->driverIds = $driverIds;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return collect($this->driverIds)
            ->map(fn($driverId) => new PrivateChannel("drivers.{$driverId}"))
            ->all();
    }

    public function broadcastAs(): string
    {
        return 'rideRequest.confirmedByOthers';
    }
}
