<?php

namespace Versyx\View\Extensions\Twig;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

/**
 * Class AssetExtension
 */
class SessionExtension extends AbstractExtension
{
    /**
     * Define env accessor function.
     *
     * @return array
     */
    public function getFunctions()
    {
        $function = new TwigFunction('destroy_session', [$this, 'destroy_session'], []);

        $function->setArguments([]);

        return [$function];
    }

    /**
     * Generate assets url.
     *
     * @return void
     */
    public function destroy_session()
    {
        session_destroy();
    }
}