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

        	//setup autoloader to save plugins having to do it every time
        	ClassLoader::register();
        	ClassLoader::addDirectories(array($plugin->path . '/src', $plugin->path . '/migrations'));

        	//require autoload files - not advised really, plugins should make full use of service providers to add functionality
        	if(isset($plugin->autoload->files)){
	        	foreach( (array) $plugin->autoload->files as $file){
	        		$this->app['files']->requireOnce($plugin->path . '/' . $file);
	        	}
	        }

        	//register providers
        	foreach( (array) $plugin->providers as $provider){
        		$this->app->register($provider);
        	}
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

    }

    public function provides(){
    	return array('fluentkit.plugin', 'fluentkit.plugin.finder');
    }

}