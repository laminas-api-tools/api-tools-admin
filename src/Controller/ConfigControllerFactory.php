<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ConfigControllerFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @param null|array $options
     * @return ConfigController
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new ConfigController($container->get(ConfigResource::class));
    }

    /**
     * @return ConfigController
     */
    public function createService(ServiceLocatorInterface $container)
    {
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator() ?: $container;
        }
        return $this($container, ConfigController::class);
    }
}
