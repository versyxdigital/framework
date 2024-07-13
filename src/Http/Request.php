<?php

namespace Versyx\Http;

use Laminas\Diactoros\ServerRequest;

/**
 * HTTP Server Request.
 *
 * This class provides convenient methods to retrieve different parts of HTTP
 * requests, such as the body, headers and attributes.
 * 
 * A server request represents an HTTP request as recevied by the server, which
 * includes server-specific data such as server params and environment variables,
 * server requests extend the normal HTTP request by including things such as session
 * attributes, uploaded files, cookie params and other attributes added by server-side
 * middleware.
 */
class Request extends ServerRequest
{
    /**
    * Get body, headers and query parameters from request
    * 
    * @param array $options
    * @return array $request
    */
    public function all(): array
    {
        $request = [
            'body' => $this->getParsedBody(),
            'query' => $this->getQueryParams(),
            'headers' => $this->getHeaders()
        ];

        return $request;
    }

    /**
     * Get the request body.
     * 
     * This is a convenience wrapper for the standard PSR-7 compliant
     * getBody() method.
     * 
     * @return null|array|object
     */
    public function body(): null|array|object
    {
        return $this->getParsedBody();
    }

    /**
     * Get the request headers.
     * 
     * This is a convenience wrapper for the standard PSR-7 compliant
     * getHeaders() method
     * 
     * @return array
     */
    public function headers(): array
    {
        return $this->getHeaders();
    }

    /**
     * Get a request header specified by header name.
     * 
     * This is a convenience wrapper for the standard PSR-7 compliant
     * getHeader() method
     * 
     * @param string $name
     * @return array
     */
    public function header(string $name): array
    {
        return $this->getHeader($name);
    }

    /**
     * Get request attributes.
     * 
     * This is a convenience wrapper for the standard PSR-7 compliant
     * getAttributes() method
     * 
     * @return array
     */
    public function attributes(): array
    {
        return $this->getAttributes();
    }

    /**
     * Get a an attribute specified by key name or default if it doesn't exist
     * 
     * This is a convenience wrapper for the standard PSR-7 compliant
     * getAttribute() method
     * 
     * @param string $key
     * @param string $default
     * @return array
     */
    public function attribute(string $key, string $default): array
    {
        return $this->getAttribute($key, $default);
    }
}