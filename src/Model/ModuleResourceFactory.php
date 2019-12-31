<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;

class ModuleResourceFactory
{
    /**
     * @param ContainerInterface $container
     * @return ModuleResource
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ModuleResource(
            $container->get(ModuleModel::class),
            $container->get(ModulePathSpec::class)
        );
    }
}
