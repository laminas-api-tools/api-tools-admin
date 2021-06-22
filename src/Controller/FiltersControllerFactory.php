<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\FiltersModel;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class FiltersControllerFactory implements FactoryInterface
{
    /**
     * Create and return a FiltersController instance.
     *
     * @param string $requestedName
     * @param null|array $options
     * @return FiltersController
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new FiltersController($container->get(FiltersModel::class));
    }

    /**
     * Create and return a FiltersController instance (v2).
     *
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @return FiltersController
     */
    public function createService(ServiceLocatorInterface $container)
    {
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator() ?: $container;
        }
        return $this($container, FiltersController::class);
    }
}
