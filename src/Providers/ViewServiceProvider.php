<?php

namespace Versyx\Providers;

use Versyx\Service\Container;
use Versyx\Service\ServiceProviderInterface;
use Versyx\View\TwigEngine;
use Versyx\View\ViewEngineInterface;

/**
 * Class ViewServiceProvider
 * 
 * Versyx uses twig by default.
 */
class ViewServiceProvider implements ServiceProviderInterface
{
    /**
     * Register view service provider.
     *
     * @param Container $container
     * @return Container
     */
    public function register(Container $container): Container
    {
        $container[ViewEngineInterface::class] = new TwigEngine();
        return $container;
    }
}