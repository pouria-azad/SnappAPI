<?php

namespace App\Interfaces\Controllers;
use App\Http\Requests\AcceptRideRequestRequest;
use App\Http\Requests\NewRideRequestRequest;
use Illuminate\Http\JsonResponse;

interface RideRequestInterface
{
    public function store(NewRideRequestRequest $request): JsonResponse;
}
