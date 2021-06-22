<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class SourceControllerFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @param null|array $options
     * @return SourceController
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new SourceController($container->get(ModuleModel::class));
    }

    /**
     * @return SourceController
     */
    public function createService(ServiceLocatorInterface $container)
    {
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator() ?: $container;
        }
        return $this($container, SourceController::class);
    }
}
