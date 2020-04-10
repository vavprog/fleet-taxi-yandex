<?php

namespace Vavprog\FleetTaxiYandex\Facades;

class FleetTaxiYandex extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'fleat_taxi_yandex';
    }
}