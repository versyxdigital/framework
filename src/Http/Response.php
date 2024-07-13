<?php

namespace Versyx\Http;

class Response
{
    /** @var array */
    private $data = [];

    /**
     * Redirect.
     * 
     * @param string $uri
     * @param int $status
     */
    public function redirect(string $uri, int $status = 302)
    {
        return new RedirectResponse($uri, $status);
    }

    /**
     * Flash data to request session.
     * 
     * @param array $data
     * @return void
     */
    public function with(array $data = []): void
    {

    }

    /**
     * Set flashed session data
     */
    public function setData(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Get flashed session data
     */
    public function getData()
    {
        return $this->data;
    }
}