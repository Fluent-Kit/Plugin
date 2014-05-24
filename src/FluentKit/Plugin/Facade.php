<?php

namespace FluentKit\Plugin;

use Illuminate\Support\Facades\Facade as LaravelFacade;

class Facade extends LaravelFacade{

	protected static function getFacadeAccessor() { return 'fluentkit.plugin'; }

}