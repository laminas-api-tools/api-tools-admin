<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;

class ModuleResourceFactory
{
    /**
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
