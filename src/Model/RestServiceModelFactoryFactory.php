<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Configuration\ConfigResourceFactory;
use Laminas\ApiTools\Doctrine\Admin\Model\DoctrineRestServiceModel;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

class RestServiceModelFactoryFactory
{
    /**
     * @param ContainerInterface $container
     * @return RestServiceModelFactory
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(ModulePathSpec::class)
            || ! $container->has(ConfigResourceFactory::class)
            || ! $container->has(ModuleModel::class)
            || ! $container->has('SharedEventManager')
        ) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is missing one or more dependencies from Laminas\ApiTools\Configuration',
                RestServiceModelFactory::class
            ));
        }

        $sharedEvents = $container->get('SharedEventManager');
        $this->attachSharedListeners($sharedEvents, $container);

        return new RestServiceModelFactory(
            $container->get(ModulePathSpec::class),
            $container->get(ConfigResourceFactory::class),
            $sharedEvents,
            $container->get(ModuleModel::class)
        );
    }

    /**
     * Attach shared listeners to the RestServiceModel.
     *
     * @param SharedEventManagerInterface $sharedEvents
     * @param ContainerInterface $container
     * @return void
     */
    private function attachSharedListeners(SharedEventManagerInterface $sharedEvents, ContainerInterface $container)
    {
        $sharedEvents->attach(
            RestServiceModel::class,
            'fetch',
            [DbConnectedRestServiceModel::class, 'onFetch']
        );

        $modules = $container->get('ModuleManager');
        $loaded = $modules->getLoadedModules(false);
        if (! isset($loaded['Laminas\ApiTools\Doctrine\Admin'])) {
            return;
        }

        // Wire Doctrine-Connected fetch listener
        $sharedEvents->attach(
            RestServiceModel::class,
            'fetch',
            [DoctrineRestServiceModel::class, 'onFetch']
        );
    }
}
