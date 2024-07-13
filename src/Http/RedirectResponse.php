<?php

namespace Versyx\Http;

use Laminas\Diactoros\Response;

class RedirectResponse extends Response
{
    public function __construct(string $uri, int $status = 302)
    {
        parent::__construct('php://memory', $status, ['Location' => $uri]);
    }
}