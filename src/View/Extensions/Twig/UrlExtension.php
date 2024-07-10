<?php

namespace Versyx\View\Extensions\Twig;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

/**
 * Class AssetExtension
 */
class UrlExtension extends AbstractExtension
{
    /**
     * Define env accessor function.
     *
     * @return array
     */
    public function getFunctions()
    {
        $function = new TwigFunction('url', [$this, 'url'], []);

        $function->setArguments([]);

        return [$function];
    }

    /**
     * Generate assets url.
     *
     * @return string
     */
    public function url(): string
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }
}