<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Listener;

use Laminas\Mvc\MvcEvent;

use function str_replace;

class NormalizeMatchedControllerServiceNameListener
{
    /**
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
