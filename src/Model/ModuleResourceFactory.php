<?php

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
