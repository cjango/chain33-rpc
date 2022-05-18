<?php

namespace Jason\Chain33\Unfreeze;

use Jason\Chain33\Application;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple): void
    {
        $pimple['unfreeze'] = static function (Application $app) {
            return new Client($app);
        };
    }
}
