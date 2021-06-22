<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\InputFilterModel;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class InputFilterControllerFactory implements FactoryInterface
{
    /**
     * Create and return an InputFilterController instance.
     *
     * @param string $requestedName
     * @param null|array $options
     * @return InputFilterController
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new InputFilterController($container->get(InputFilterModel::class));
    }

    /**
     * Create and return an InputFilterController instance (v2).
     *
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @return InputFilterController
     */
    public function createService(ServiceLocatorInterface $container)
    {
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator() ?: $container;
        }
        return $this($container, InputFilterController::class);
    }
}
