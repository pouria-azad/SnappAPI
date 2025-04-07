<?php

namespace App\Providers;

use App\Interfaces\Services\RideAcceptationServiceInterface;
use App\Interfaces\Services\RideCompletionServiceInterface;
use App\Interfaces\Services\DistanceCalculatorServiceInterface;
use App\Interfaces\Repositories\RideRepositoryInterface;
use App\Listeners\ProcessRiderAcceptRequest;
use App\Repositories\DriverRepository;
use App\Interfaces\Repositories\DriverRepositoryInterface;
use App\Repositories\RideRepository;
use App\Services\HaversineDistanceCalculatorService;
use App\Services\RideAcceptationService;
use App\Services\RideCompletionService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */

    public $bindings = [
        DriverRepositoryInterface::class          => DriverRepository::class,
        DistanceCalculatorServiceInterface::class => HaversineDistanceCalculatorService::class,
        RideCompletionServiceInterface::class     => RideCompletionService::class,
        RideAcceptationServiceInterface::class    => RideAcceptationService::class,
    ];

    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
