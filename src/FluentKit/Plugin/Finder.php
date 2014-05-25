<?php 
namespace FluentKit\Plugin;

use Illuminate\Container\Container as Application;

use Illuminate\Support\Collection;

class Finder
{
    /**
     * Application instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $app;

    protected $collection;

    /**
     * Construct a new finder.
     *
     * @param  \Illuminate\Container\Container  $app
     */
    public function __construct(Application $app, Collection $collection)
    {
        $this->app = $app;

        $this->collection = $collection;
    }

    /**
     * Detect available themes.
     *
     * @return array
     * @throws \RuntimeException
     */
    public function collection()
    {
        $file   = $this->app['files'];
        $path   = rtrim($this->app['path.public'], '/').'/content/plugins/';

        $folders = $file->directories($path);

        foreach ($folders as $folder) {
            $name = $this->parsePluginNameFromPath($folder);
            $this->collection->put($name, new Manifest($file, $this->app, rtrim($folder, '/').'/'));
        }

        return $this->collection;
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