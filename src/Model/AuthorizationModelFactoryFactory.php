<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Configuration\ConfigResourceFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

use function sprintf;

class AuthorizationModelFactoryFactory
{
    /**
     * @return AuthorizationModelFactory
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (
            ! $container->has(ModulePathSpec::class)
            || ! $container->has(ConfigResourceFactory::class)
            || ! $container->has(ModuleModel::class)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is missing one or more dependencies from Laminas\ApiTools\Configuration',
                AuthorizationModelFactory::class
            ));
        }

        return new AuthorizationModelFactory(
            $container->get(ModulePathSpec::class),
            $container->get(ConfigResourceFactory::class),
            $container->get(ModuleModel::class)
        );
    }
}
