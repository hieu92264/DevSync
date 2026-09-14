<?php

namespace App\Providers;

use App\Modules\Identity\Interfaces\IdentityServiceInterface;
use App\Modules\Identity\Services\IdentityService;
use App\Modules\Organization\Interfaces\OrganizationServiceInterface;
use App\Modules\Organization\Services\OrganizationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerService();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    private function registerService(): void
    {
        $this->app->singleton(OrganizationServiceInterface::class, OrganizationService::class);
        $this->app->singleton(IdentityServiceInterface::class, IdentityService::class);
    }
}
