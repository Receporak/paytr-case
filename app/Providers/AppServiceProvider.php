<?php

namespace App\Providers;

use App\Services\POS\Strategies\Contracts\POSSelectionStrategyInterface;
use App\Services\POS\Strategies\LowestCostStrategy;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(POSSelectionStrategyInterface::class,LowestCostStrategy::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
