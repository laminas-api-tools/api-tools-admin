<?php

namespace Laminas\ApiTools\Admin\Listener;

use Laminas\Mvc\MvcEvent;

class EnableHalRenderCollectionsListener
{
    /**
     * Ensure the render_collections flag of the HAL view helper is enabled
     * regardless of the configuration setting if we match an admin service.
     *
     * @param MvcEvent $e
     */
    public function __invoke(MvcEvent $e)
    {
        $matches = $e->getRouteMatch();
        if (! $matches
            || 0 !== strpos($matches->getParam('controller'), 'Laminas\ApiTools\Admin\\')
        ) {
            return;
        }

        $app      = $e->getTarget();
        $services = $app->getServiceManager();
        $helpers  = $services->get('ViewHelperManager');
        $hal      = $helpers->get('Hal');
        $hal->setRenderCollections(true);
    }
}
