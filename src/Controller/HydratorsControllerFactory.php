<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\HydratorsModel;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class HydratorsControllerFactory implements FactoryInterface
{
    /**
     * Create and return HydratorsController instance.
     *
     * @param string $requestedName
     * @param null|array $options
     * @return HydratorsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new HydratorsController($container->get(HydratorsModel::class));
    }

    /**
     * Create and return HydratorsController instance (v2).
     *
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @return HydratorsController
     */
    public function createService(ServiceLocatorInterface $container)
    {
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator() ?: $container;
        }
        return $this($container, HydratorsController::class);
    }
}
