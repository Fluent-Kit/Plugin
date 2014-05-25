<?php 
namespace FluentKit\Plugin;

use Illuminate\Container\Container as Application;

class Container
{
    /**
     * Application instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $app;

    /**
     * Filesystem path of theme.
     *
     * @var string
     */
    protected $path;

    /**
     * URL path of theme.
     *
     * @var string
     */
    protected $absoluteUrl;

    /**
     * Relative URL path of theme.
     *
     * @var string
     */
    protected $relativeUrl;

    /**
     * Start theme engine, this should be called from application booted
     * or whenever we need to overwrite current active theme per request.
     *
     * @param  \Illuminate\Container\Container  $app
     * @param  string                           $name
     */
    public function __construct(Application $app)
    {
        $this->app  = $app;
        $baseUrl    = $app['request']->root();
        $this->path = $app['path.public'].'/content/plugins';

        // Register relative and absolute URL for theme usage.
        $this->absoluteUrl = rtrim($baseUrl, '/').'/content/plugins';
        $this->relativeUrl = trim(str_replace($baseUrl, '/', $this->absoluteUrl), '/');
    }


    /**
     * Detect available themes.
     *
     * @return array
     */
    public function all()
    {
        return $this->app['fluentkit.plugin.finder']->collection();
    }

    public function activated(){
    	return $this->app['fluentkit.plugin.finder']->collection();
    }

    /**
     * Detect available themes.
     *
     * @return array
     */
    public function get($key)
    {

		$plugins = $this->app['fluentkit.plugin.finder']->collection()->filter(function($plugin) use ($key){
			return ($plugin->uid == $key) ? true : false;
		});
		return $plugins->first();
    }
    
    public function migrate($key){
        //php artisan migrate --path=../content/plugins/$key/migrations
    }
    
    public function install($key){}
    
    public function upgrade($key){}
    
    public function uninstall($key){}
    
    public function delete($key){}

}