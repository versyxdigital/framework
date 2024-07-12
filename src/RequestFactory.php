<?php

namespace Versyx;

use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\ServerRequestFilter\FilterServerRequestInterface;
use Laminas\Diactoros\ServerRequestFilter\FilterUsingXForwardedHeaders;
use Laminas\Diactoros\UriFactory;
use Psr\Http\Message\ServerRequestInterface;

use function Laminas\Diactoros\marshalHeadersFromSapi;
use function Laminas\Diactoros\marshalMethodFromSapi;
use function Laminas\Diactoros\marshalProtocolVersionFromSapi;
use function Laminas\Diactoros\normalizeServer;
use function Laminas\Diactoros\normalizeUploadedFiles;
use function Laminas\Diactoros\parseCookieHeader;

/**
 * Class for marshaling a request object from the current PHP environment.
 */
class RequestFactory extends ServerRequestFactory
{
    /** @var callable */
    private static $apacheRequestHeaders = 'apache_request_headers';

    /**
     * Create a request from the supplied superglobal values.
     * 
     * @param array $server
     * @param array $query
     * @param array $body
     * @param array $cookies
     * @param array $files
     * @param null|FilterServerRequestInterface
     */
    public static function fromGlobals(
        ?array $server = null,
        ?array $query = null,
        ?array $body = null,
        ?array $cookies = null,
        ?array $files = null,
        ?FilterServerRequestInterface $requestFilter = null
    ): ServerRequestInterface {
        $requestFilter = $requestFilter ?? FilterUsingXForwardedHeaders::trustReservedSubnets();

        $server  = normalizeServer(
            $server ?? $_SERVER,
            is_callable(self::$apacheRequestHeaders) ? self::$apacheRequestHeaders : null
        );
        $files   = normalizeUploadedFiles($files ?? $_FILES);
        $headers = marshalHeadersFromSapi($server);

        if (null === $cookies && array_key_exists('cookie', $headers)) {
            $cookies = parseCookieHeader($headers['cookie']);
        }

        return $requestFilter(new Request(
            $server,
            $files,
            UriFactory::createFromSapi($server, $headers),
            marshalMethodFromSapi($server),
            'php://input',
            $headers,
            $cookies ?? $_COOKIE,
            $query ?? $_GET,
            $body ?? $_POST,
            marshalProtocolVersionFromSapi($server)
        ));
    }
}
