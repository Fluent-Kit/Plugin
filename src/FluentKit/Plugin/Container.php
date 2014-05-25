<?php 
namespace FluentKit\Plugin;

use Illuminate\Container\Container as Application;

use Artisan;

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
    
    public $buffer;

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
    	return $this->app['fluentkit.plugin.finder']->collection()->filter(function($plugin){
            return $plugin->active;
        });
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
        $path = '../content/plugins/'.$key.'/src/migrations';
        $this->buffer = new \Symfony\Component\Console\Output\BufferedOutput;
        $this->buffer->writeln('Running '.$key.' Plugin Migrations');
        Artisan::call('migrate', array('--path' => $path), $this->buffer);
    }
    
    public function activate($key){
        $plugin = $this->get($key);
        
        if($plugin->active)
            return false;
        
        $this->migrate($key);
        $plugin = $this->get($key);
        $this->app['db']->table('plugins')->insert(array(
            array('plugin' => $key, 'key' => 'active', 'value' => true),
            array('plugin' => $key, 'key' => 'version', 'value' => $plugin->version)
        ));
        $this->app['events']->fire('plugin.'.$key.'.activated');
    }
    
    public function upgrade($key){
        $plugin = $this->get($key);
        
        if($plugin->version == $plugin->folder_version || !$plugin->active)
            return false;
        
        $this->migrate($key);
        $this->app['db']->table('plugins')->where('plugin', $key)->where('key', 'version')->update(array('value' => $plugin->folder_version));
        $this->app['events']->fire('plugin.'.$key.'.upgraded');
        
        return true;
    }
    
    public function deactivate($key){
        if(!$plugin->active)
            return false;
        
        $this->app['db']->table('plugins')->where('plugin', $key)->delete();
        $this->app['events']->fire('plugin.'.$key.'.deactivated');
    }
    
    public function uninstall($key){
        $this->deactivate($key);
        $this->app['events']->fire('plugin.'.$key.'.uninstalled');
        return true;
    }
    
    public function delete($key){
        $this->uninstall($key);
        try{
            $path = '../content/plugins/'.$key;
            $this->app['files']->remove($path);
        }catch(\Symfony\Component\Filesystem\Exception\IOException $e){
            return false;
        }
        $this->app['events']->fire('plugin.'.$key.'.deleted');
        return true;
    }

}