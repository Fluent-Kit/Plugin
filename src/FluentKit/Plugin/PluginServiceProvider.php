<?php
namespace FluentKit\Plugin;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class PluginServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

    public function register()
    {

        $this->app->bindShared('fluentkit.plugin', function ($app) {
            return new PluginManager($app);
        });
        $this->app->bindShared('fluentkit.plugin.finder', function ($app) {
            return new PluginFinder($app);
        });

    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {

    	//register facades
    	$loader = AliasLoader::getInstance();

		//fluent aliases
        $loader->alias('Plugin', 'FluentKit\Plugin\Facade');

    }

    public function provides(){
    	return array('fluentkit.plugin', 'fluentkit.plugin.finder');
    }

}