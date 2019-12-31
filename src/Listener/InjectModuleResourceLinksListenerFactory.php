<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

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
