<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ValidatorsModel;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ValidatorsControllerFactory implements FactoryInterface
{
    /**
     * Create and return a ValidatorsController instance.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ValidatorsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ValidatorsController($container->get(ValidatorsModel::class));
    }

    /**
     * Create and return a ValidatorsController instance (v2).
     *
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return ValidatorsController
     */
    public function createService(ServiceLocatorInterface $container)
    {
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator() ?: $container;
        }
        return $this($container, ValidatorsController::class);
    }
}
