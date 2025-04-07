<?php

namespace App\Services;


use App\Interfaces\Services\RideCompletionServiceInterface;
use App\Models\CompleteRide;
use App\Models\CurrentRide;
use App\Models\Driver;
use Carbon\Carbon;

class RideCompletionService implements RideCompletionServiceInterface
{

    public function completeRide(int $tripId): float
    {
        $currentRide = CurrentRide::findOrFail($tripId);
        $totalTimeInHours = Carbon::now()->diffInHours($currentRide->created_at);

        $rideData = collect($currentRide->getAttributes())
            ->put('total_time', $totalTimeInHours)
            ->except(['updated_at', 'created_at', 'isArrived'])
            ->all();

        Driver::findOrFail($currentRide['driver_id'])->update(['is_active' => true]);

        CompleteRide::create($rideData);
        $currentRide->delete();

        \Log::info('Ride completed successfully', [
            'trip_id' => $tripId,
            'total_time' => $totalTimeInHours,
        ]);

        return $totalTimeInHours;
    }
}
