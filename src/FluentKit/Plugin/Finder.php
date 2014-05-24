<?php 
namespace FluentKit\Plugin;

use Illuminate\Container\Container as Application;

class Finder
{
    /**
     * Application instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $app;

    /**
     * Construct a new finder.
     *
     * @param  \Illuminate\Container\Container  $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Detect available themes.
     *
     * @return array
     * @throws \RuntimeException
     */
    public function detect()
    {
        $plugins = array();
        $file   = $this->app['files'];
        $path   = rtrim($this->app['path.public'], '/').'/content/plugins/';

        $folders = $file->directories($path);

        foreach ($folders as $folder) {
            $name = $this->parsePluginNameFromPath($folder);
            $plugins[$name] = new Manifest($file, rtrim($folder, '/').'/');
        }

        return $plugins;
    }

    /**
     * Get folder name from full path.
     *
     * @param  string   $path
     * @return string
     */
    protected function parsePluginNameFromPath($path)
    {
        $path = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $path);
        $path = explode(DIRECTORY_SEPARATOR, $path);

        return array_pop($path);
    }
}