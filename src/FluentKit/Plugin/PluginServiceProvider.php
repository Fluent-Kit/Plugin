<?php
namespace FluentKit\Plugin;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ClassLoader;

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
            return new Finder($app, new \Illuminate\Support\Collection);
        });

        $this->app['fluentkit.plugin']->activated()->each(function($plugin){

        	$plugin->register();
        });

        //register facades
    	$loader = AliasLoader::getInstance();

		//fluent aliases
        $loader->alias('Plugin', 'FluentKit\Plugin\Facade');
        
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        \Route::any('plugins', function(){
            \Plugin::activate('clients');
            echo 'hello';
            //print_r(\Plugin::activated());
        });
    }

    public function provides(){
    	return array('fluentkit.plugin', 'fluentkit.plugin.finder');
    }

}