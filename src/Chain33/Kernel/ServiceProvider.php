<?php

namespace Jason\Chain33\Kernel;

use Jason\Chain33\Application;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple): void
    {
        $pimple['request'] = static function (Application $app) {
            return new Request($app);
        };
    }
}
