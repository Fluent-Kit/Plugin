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
     * Booted indicator.
     *
     * @var boolean
     */
    protected $booted = false;

    /**
     * Theme name.
     *
     * @var string
     */
    protected $plugin = null;

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
     * Get the theme.
     *
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Boot the theme by autoloading all the relevant files.
     *
     * @return boolean
     */
    public function boot()
    {
        if ($this->booted) {
            return false;
        }

        $this->booted = true;

        $themePath = $this->getThemePath();

        // There might be situation where Orchestra Platform was unable
        // to get theme information, we should only assume there a valid
        // theme when manifest is actually an instance of
        // Orchestra\View\Theme\Manifest.
        if (! $this->app['files']->isDirectory($themePath)) {
            return false;
        }

        $autoload = $this->getThemeAutoloadFiles($themePath);

        foreach ($autoload as $file) {
            $file = ltrim($file, '/');
            $this->app['files']->requireOnce("{$themePath}/{$file}");
        }

        $this->app['events']->fire("fluentkit.theme.boot: {$this->theme}");

        return true;
    }

    /**
     * Get theme path.
     *
     * @return string
     */
    public function getThemePath()
    {
        return "{$this->path}/{$this->theme}";
    }

    /**
     * URL helper for the theme.
     *
     * @param  string   $url
     * @return string
     */
    public function to($url = '')
    {
        return "{$this->absoluteUrl}/{$this->theme}/{$url}";
    }

    /**
     * Relative URL helper for theme.
     *
     * @param  string   $url
     * @return string
     */
    public function asset($url = '')
    {
        return "/{$this->relativeUrl}/{$this->theme}/{$url}";
    }

    /**
     * Get theme autoload files from manifest.
     *
     * @param  string $themePath
     * @return array
     */
    protected function getThemeAutoloadFiles($themePath)
    {
        $autoload = array();
        $manifest = new Manifest($this->app['files'], $themePath);

        if (isset($manifest->autoload) && is_array($manifest->autoload)) {
            $autoload = $manifest->autoload;
        }

        return $autoload;
    }
}