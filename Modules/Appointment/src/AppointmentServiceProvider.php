<?php

namespace Appointment;


use Appointment\Builder\AppointmentBuilder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class AppointmentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AppointmentBuilder::class, function (Application $app) {
            return new AppointmentBuilder();
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

}
