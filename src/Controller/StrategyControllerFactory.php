<?php

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class StrategyControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return StrategyController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new StrategyController($container);
    }

    /**
     * @param ServiceLocatorInterface $container
     * @return StrategyController
     */
    public function createService(ServiceLocatorInterface $container)
    {
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator() ?: $container;
        }

        return $this($container, StrategyController::class);
    }
}
