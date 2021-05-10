<?php

namespace Laminas\ApiTools\Admin\Listener;

use Laminas\Mvc\MvcEvent;

class NormalizeMatchedControllerServiceNameListener
{
    /**
     * @param MvcEvent $e
     * @return void
     */
    public function __invoke(MvcEvent $e)
    {
        $matches = $e->getRouteMatch();
        if (! $matches || ! $matches->getParam('controller_service_name')) {
            return;
        }

        // Replace '-' with namespace separator
        $controller = $matches->getParam('controller_service_name');
        $matches->setParam('controller_service_name', str_replace('-', '\\', $controller));
    }
}
