<?php

namespace Versyx\Providers;

use Versyx\Service\Container;
use Versyx\Service\ServiceProviderInterface;
use Versyx\View\TwigEngine;
use Versyx\View\ViewEngineInterface;

/**
 * Provides a view service.
 * 
 * This service provider creates an application view service and registers it to
 * the service container.
 * 
 * Versyx uses twig/twig by default, however you are free to switch the implementation
 * with your own custom ViewServiceProvider.
 */
class ViewServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the view service provider.
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