<?php 
namespace FluentKit\Plugin;

use RuntimeException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ClassLoader;

class Manifest
{
    /**
     * Application instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Theme configuration.
     *
     * @var Object
     */
    protected $items;
    
    protected $app;

    /**
     * Load the theme.
     *
     * @param  \Illuminate\Filesystem\Filesystem    $files
     * @param  string                               $path
     * @throws \RuntimeException
     */
    public function __construct(Filesystem $files, $app, $path)
    {
        $path        = rtrim($path, '/');
        $this->files = $files;
        $this->app = $app;

        if ($files->exists($manifest = "{$path}/plugin.json")) {
            $this->items = json_decode($files->get($manifest));

            if (is_null($this->items)) {
                // json_decode couldn't parse, throw an exception.
                throw new RuntimeException(
                    "Plugin [{$path}]: cannot decode plugin.json file"
                );
            }

            $this->items->uid  = $this->parsePluginNameFromPath($path);
            $this->items->path = $path;
            $this->items->active = false;
            $this->items->folder_version = $this->items->version;
            
            $data = $this->app['db']->table('plugins')->where('plugin', $this->items->uid)->get();
            foreach($data as $value){
                $this->items->{$value->key} = $value->value;   
            }
            
        }
    }
    
    public function register(){
        //setup autoloader to save plugins having to do it every time
        ClassLoader::register();
        ClassLoader::addDirectories(array($this->items->path . '/src', $this->items->path . '/src/migrations'));

        //require autoload files - not advised really, plugins should make full use of service providers to add functionality
        if(isset($this->items->autoload->files)){
            foreach( (array) $this->items->autoload->files as $file){
                $this->app['files']->requireOnce($this->items->path . '/' . $file);
            }
        }

        //register providers
        foreach( (array) $this->items->providers as $provider){
            $this->app->register($provider);
        }
    }

    /**
     * Get theme name from path.
     *
     * @param  string   $path
     * @return string
     */
    protected function parsePluginNameFromPath($path)
    {
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
        $path = explode(DIRECTORY_SEPARATOR, $path);

        return array_pop($path);
    }

    /**
     * Magic method to get items by key.
     *
     * @param  string   $key
     * @return mixed
     */
    public function __get($key)
    {
        if (! isset($this->items->{$key})) {
            return null;
        }

        return $this->items->{$key};
    }

    /**
     * Magic Method to check isset by key.
     *
     * @param  string   $key
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($this->items->{$key});
    }

	/**
     * Magic Method to check isset by key.
     *
     * @param  string   $key
     * @return boolean
     */
    public function __set($key, $value)
    {	
        $this->items->{$key} = $value;
    }

}