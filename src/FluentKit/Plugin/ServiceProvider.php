<?php
namespace FluentKit\Plugin;

use Illuminate\Support\ServiceProvider as LaravelProvider;

use Illuminate\Foundation\AliasLoader;

class ServiceProvider extends LaravelProvider {

    public function registerPlugin($plugin)
	{
		$path = rtrim($this->app['path.public'], '/') . '/content/plugins/' . $plugin;
		return $this->package($plugin, null, $path);
	}
    
    public function registerFacade($facade, $namespace)
	{
		$loader = AliasLoader::getInstance();
        $loader->alias($facade, $namespace);
	}

}