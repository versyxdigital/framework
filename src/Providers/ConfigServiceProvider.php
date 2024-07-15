<?php

namespace Versyx\Providers;

use Versyx\Config;
use Versyx\Service\Container;
use Versyx\Service\ServiceProviderInterface;

class ConfigServiceProvider implements ServiceProviderInterface
{
    /**
     * 
     */
    public function register(Container $container)
    {
        $config = [];

        $rootDir = appRootDir();

        foreach (glob($rootDir . '/config/*.php') as $file) {
            $key = basename($file, '.php');
            $config[$key] = require $file;
        }

        $container['config'] = new Config($config);
    }
}
