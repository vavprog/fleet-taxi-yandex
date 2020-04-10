<?php

namespace Vavprog\FleetTaxiYandex;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class FleetTaxiYandexServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/fleet-taxi-yandex.php' => config_path('fleet-taxi-yandex.php'),
        ]);
    }

    public function register()
    {
        $this->app->bind('fleat_taxi_yandex', function(){
            return new FleetTaxiYandexClient();
        });

        $this->mergeConfigFrom(__DIR__.'/../config/fleet-taxi-yandex.php', 'fleet-taxi-yandex');
    }
}