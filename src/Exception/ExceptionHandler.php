<?php

namespace Versyx\Exception;

use Throwable;
use Psr\Log\LoggerInterface;
use Versyx\Service\Container;
use Versyx\View\ViewEngineInterface;

/**
 * Class ExceptionHandler
 */
class ExceptionHandler
{
    /** @var LoggerInterface $log */
    private LoggerInterface $log;

    /** @var mixed $view */
    private ViewEngineInterface $view;

    /**
     * ExceptionHandler constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->log = $container[LoggerInterface::class];
        $this->view = $container[ViewEngineInterface::class];

        set_exception_handler([$this, 'handle']);
    }

    /**
     * Handle exception.
     *
     * @param Throwable $exception
     * @return void
     */
    public function handle(Throwable $exception): void
    {
        $this->log->error($exception->getMessage(), [
            'exception' => $exception
        ]);

        // Prepare the stack trace for debugging
        $trace = '';
        if (env('APP_DEBUG')) {
            $previousException = $exception->getPrevious();
            if ($previousException) {
                $trace = ltrim($previousException->getTraceAsString());
            } else {
                $trace = $exception->getTraceAsString();
            }
        }

        // Clear any existing output buffer
        if (ob_get_length()) {
            ob_clean();
        }

        http_response_code(500);

        echo $this->view->render('error/500.twig', [
            'debug' => env('APP_DEBUG'),
            'error' => env('APP_DEBUG') ? $exception->getMessage() : 'An unexpected error has occurred.',
            'trace' => $trace
        ]);
    }
}