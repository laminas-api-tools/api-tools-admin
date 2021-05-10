<?php

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\DbAutodiscoveryModel;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class DbAutodiscoveryControllerFactory implements FactoryInterface
{
    /**
     * Create and return DbAutodiscoveryController instance.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return DbAutodiscoveryController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new DbAutodiscoveryController($container->get(DbAutodiscoveryModel::class));
    }

    /**
     * Create and return DbAutodiscoveryController instance (v2).
     *
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return DbAutodiscoveryController
     */
    public function createService(ServiceLocatorInterface $container)
    {
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator() ?: $container;
        }
        return $this($container, DbAutodiscoveryController::class);
    }
}
