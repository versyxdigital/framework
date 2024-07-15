<?php

namespace Versyx\Http;

use Psr\Log\LoggerInterface;
use Versyx\View\ViewEngineInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;

/**
 * Abstract base controller class.
 * 
 * Provides basic functionality for controllers, including setting data, rendering
 * views or fetching data, and returning PSR-7 compliant HTTP responses.
 */
abstract class AbstractController
{
    /** @var LoggerInterface $log */
    protected $log;

    /** @var ViewEngineInterface $view */
    protected $view;

    /** @var array $data */
    public array $data = [];

    /**
     * Abstract base controller constructor.
     *
     * @param LoggerInterface $logger
     * @param ViewEngineInterface $view
     */
    public function __construct(LoggerInterface $logger, ViewEngineInterface $view)
    {
        $this->log = $logger;
        $this->view = $view;

        $this->data['title'] = env("APP_NAME");
    }

    /**
     * Set data to pass to the view.
     *
     * @param array $data
     * @return self
     */
    protected function setViewData(array $data = []) : self
    {
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if ($key === 'title' && $val !== '') {
                    $val = $val . ' | ' . $this->data['title'];
                }
                $this->data[$key] = $val;
            }
        }

        return $this;
    }

    /**
     * Return a PSR-7 compliant HTML response
     * 
     * @param string $template
     * @param array $data
     * @return HtmlResponse
     */
    protected function view(string $template, array $data = []): HtmlResponse
    {
        if (! empty($data)) {
            $this->setViewData($data);
        }

        return new HtmlResponse($this->render($template));
    }

    /**
     * Return a PSR-7 compliant JSON response
     * 
     * @param array $data
     * @return JsonResponse
     */
    protected function json(array $data): JsonResponse
    {
        return new JsonResponse($data);
    }

    /**
     * Renders templates with view data.
     *
     * @param string $template
     * @return mixed
     */
    protected function render(string $template)
    {
        return $this->view->render($template, $this->data);
    }
}
