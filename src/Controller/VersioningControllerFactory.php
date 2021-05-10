<?php

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ModuleVersioningModelFactory;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class VersioningControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return VersioningController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new VersioningController($container->get(ModuleVersioningModelFactory::class));
    }

    /**
     * @param ServiceLocatorInterface $container
     * @return VersioningController
     */
    public function createService(ServiceLocatorInterface $container)
    {
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator() ?: $container;
        }
        return $this($container, VersioningController::class);
    }
}
