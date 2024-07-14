<?php

namespace Versyx\Providers;

use Versyx\Config;
use Versyx\Service\Container;

class ConfigServiceProvider
{
    /** @var string */
    protected string $configPath;

    /**
     * 
     */
    public function __construct($configPath)
    {
        $this->configPath = $configPath;
    }

    /**
     * 
     */
    public function register(Container $container)
    {
        $config = [];

        foreach (glob($this->configPath . '/*.php') as $file) {
            $key = basename($file, '.php');
            $config[$key] = require $file;
        }

        $container['config'] = new Config($config);
    }
}
