<?php namespace Ejimba\LaravelAtSms;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class LaravelAtSmsServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('ejimba/laravel-at-sms');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		AliasLoader::getInstance()->alias('LaravelAtSms', 'Ejimba\LaravelAtSms\Facades\LaravelAtSms');
		$this->app->bind('laravel-at-sms', function ()
        {
        	return new LaravelAtSms;
        });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
