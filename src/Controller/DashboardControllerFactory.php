<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class DashboardControllerFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @param null|array $options
     * @return DashboardController
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new DashboardController(
            $container->get(Model\AuthenticationModel::class),
            $container->get(Model\ContentNegotiationModel::class),
            $container->get(Model\DbAdapterModel::class),
            $container->get(Model\ModuleModel::class),
            $container->get(Model\RestServiceModelFactory::class),
            $container->get(Model\RpcServiceModelFactory::class)
        );
    }

    /**
     * @return DashboardController
     */
    public function createService(ServiceLocatorInterface $container)
    {
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator() ?: $container;
        }

        return $this($container, DashboardController::class);
    }
}
