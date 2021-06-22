<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Listener;

use Laminas\Mvc\MvcEvent;

use function str_replace;

class NormalizeMatchedInputFilterNameListener
{
    public function __invoke(MvcEvent $e)
    {
        $matches = $e->getRouteMatch();
        if (! $matches || ! $matches->getParam('input_filter_name')) {
            return;
        }

        // Replace '-' with namespace separator
        $controller = $matches->getParam('input_filter_name');
        $matches->setParam('input_filter_name', str_replace('-', '\\', $controller));
    }
}
