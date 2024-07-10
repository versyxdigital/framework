<?php

namespace Versyx\Service;

use Versyx\Service\Container;

interface ServiceProviderInterface
{
    /**
     * Register services on the given container.
     *
     * This method should only be used to configure services and parameters.
     */
    public function register(Container $container);
}