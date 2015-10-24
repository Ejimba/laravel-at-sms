<?php namespace Ejimba\LaravelAtSms\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelAtSms extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'laravel-at-sms'; }

}