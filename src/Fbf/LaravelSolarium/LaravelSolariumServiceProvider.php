<?php namespace Fbf\LaravelSolarium;

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

        if (\Config::get('laravel-solarium::use_package_routes', true))
		{
		    include __DIR__.'/../../routes.php';
        }

		$models = \Config::get('laravel-solarium::models');

        if ( empty($models) || ! is_array($models) )
        {
            $models = array();
        }

        $indexer = new LaravelSolariumIndexer;

        foreach ( $models as $namespace_model => $config )
        {
            if ( class_exists($namespace_model) )
            {
                $namespace_model::observe( new LaravelSolariumModelObserver($indexer) );
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