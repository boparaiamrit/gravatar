<?php namespace Boparaiamrit\Gravatar;


use Illuminate\Support\ServiceProvider;

class GravatarServiceProvider extends ServiceProvider
{
	
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
		$this->mergeConfigFrom(__DIR__ . '/../config/gravatar-profile.php', 'gravatar-profile');
		
		$this->publishes([
			__DIR__ . '/../config/gravatar-profile.php' => config_path('gravatar-profile.php'),
		]);
	}
	
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton('gravatar-profile', function ($app) {
			return new GravatarClient($app['config'], $app['bugsnag']);
		});
	}
	
	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['gravatar-profile'];
	}
	
}