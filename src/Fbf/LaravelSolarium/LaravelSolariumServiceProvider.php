<?php namespace Fbf\LaravelSolarium;

use Config;
use Illuminate\Support\ServiceProvider;

class LaravelSolariumServiceProvider extends ServiceProvider {

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
		$this->package('fbf/laravel-solarium');

		$models = Config::get('laravel-solarium::models');

        if ( empty($models) || ! is_array($models) )
        {
            $models = array();
        }

        foreach ( $models as $model )
        {
            $class = 'Fbf\LaravelSolarium\LaravelSolarium' . $model . 'Observer';

            if ( class_exists($class) )
            {
                $model::observe(new $class);
            }
        }
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
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