<?php

namespace Laminas\ApiTools\Admin\Listener;

use Interop\Container\ContainerInterface;

class InjectModuleResourceLinksListenerFactory
{
    /**
     * @param ContainerInterface $container
     * @return InjectModuleResourceLinksListener
     */
    public function __invoke(ContainerInterface $container)
    {
        return new InjectModuleResourceLinksListener(
            $container->get('ViewHelperManager')
        );
    }
}
