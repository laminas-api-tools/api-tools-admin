<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Configuration\ConfigResourceFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

use function sprintf;

class ModuleVersioningModelFactoryFactory
{
    /**
     * @return ModuleVersioningModelFactory
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (
            ! $container->has(ConfigResourceFactory::class)
            || ! $container->has(ModulePathSpec::class)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is missing one or more dependencies from Laminas\ApiTools\Configuration',
                ModuleVersioningModelFactory::class
            ));
        }

        return new ModuleVersioningModelFactory(
            $container->get(ConfigResourceFactory::class),
            $container->get(ModulePathSpec::class)
        );
    }
}
