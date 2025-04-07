<?php

namespace App\Interfaces\Services;

interface RideAcceptationServiceInterface
{
    public function handle(int $driverId, int $requestId): void;
}
