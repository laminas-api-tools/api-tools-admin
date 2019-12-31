<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Configuration\ConfigResourceFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

/**
 * @deprecated since 1.5; use \Laminas\ApiTools\Admin\Model\ModuleVersioningModelFactoryFactory instead
 */
class VersioningModelFactoryFactory
{
    /**
     * @deprecated since 1.5.0; use the ModuleVersioningModelFactory instead
     * @param ContainerInterface $container
     * @return VersioningModelFactory
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(ConfigResourceFactory::class)
            || ! $container->has(ModulePathSpec::class)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is missing one or more dependencies from Laminas\ApiTools\Configuration',
                VersioningModelFactory::class
            ));
        }

        return new VersioningModelFactory(
            $container->get(ConfigResourceFactory::class),
            $container->get(ModulePathSpec::class)
        );
    }
}
