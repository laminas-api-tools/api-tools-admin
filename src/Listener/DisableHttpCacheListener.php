<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Listener;

use Laminas\Http\Header\GenericHeader;
use Laminas\Http\Header\GenericMultiHeader;
use Laminas\Http\Headers;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Router\RouteMatch as V2RouteMatch;
use Laminas\Router\RouteMatch;

class DisableHttpCacheListener
{
    /**
     * @return void
     */
    public function __invoke(MvcEvent $e)
    {
        $matches = $e->getRouteMatch();
        if (! ($matches instanceof RouteMatch || $matches instanceof V2RouteMatch)) {
            // In 404's, we do not have a route match... nor do we need to do
            // anything
            return;
        }

        if (! $matches->getParam('is_api-tools_admin_api', false)) {
            // Not part of the Laminas API Tools Admin API; nothing to do
            return;
        }

        $request = $e->getRequest();
        if (! ($request->isGet() || $request->isHead())) {
            return;
        }

        $this->disableHttpCache($e->getResponse()->getHeaders());
    }

    /**
     * Prepare cache-busting headers for GET requests
     *
     * Invoked from the onFinish() method for GET requests to disable client-side HTTP caching.
     */
    protected function disableHttpCache(Headers $headers): void
    {
        $headers->addHeader(new GenericHeader('Expires', '0'));
        $headers->addHeader(new GenericMultiHeader('Cache-Control', 'no-store, no-cache, must-revalidate'));
        $headers->addHeader(new GenericMultiHeader('Cache-Control', 'post-check=0, pre-check=0'));
        $headers->addHeaderLine('Pragma', 'no-cache');
    }
}
