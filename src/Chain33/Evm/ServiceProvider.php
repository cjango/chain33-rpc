<?php

namespace Jason\Chain33\Evm;

use Jason\Chain33\Application;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple): void
    {
        $pimple['evm'] = static function (Application $app) {
            return new Client($app);
        };
    }
}
