<?php 
namespace FluentKit\Plugin;

use Illuminate\Support\Manager;

class PluginManager extends Manager
{
    /**
     * Create an instance of the orchestra theme driver.
     *
     * @return Container
     */
    protected function createFluentKitDriver()
    {
        return new Container($this->app);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultDriver()
    {
        return 'fluentkit';
    }
}