<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Listener;

use Laminas\Mvc\MvcEvent;

use function strpos;

class EnableHalRenderCollectionsListener
{
    /**
     * Ensure the render_collections flag of the HAL view helper is enabled
     * regardless of the configuration setting if we match an admin service.
     *
     * @return void
     */
    public function __invoke(MvcEvent $e)
    {
        $matches = $e->getRouteMatch();
        if (
            ! $matches
            || 0 !== strpos($matches->getParam('controller') ?? '', 'Laminas\ApiTools\Admin\\')
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
