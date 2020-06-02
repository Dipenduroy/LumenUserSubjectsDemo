<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Trace\ZipkinTrace;
use App\Services\Request\Helper;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ZipkinTrace::class, function ($app) {
            return new ZipkinTrace();
        });
        $this->app->singleton(Helper::class, function ($app) {
            return new Helper($app->make(ZipkinTrace::class));
        });
    }
}
