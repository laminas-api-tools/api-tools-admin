<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

abstract class AbstractPluginManagerModelFactory implements FactoryInterface
{
    /**
     * @var string
     */
    protected $pluginManagerService;

    /**
     * @var string
     */
    protected $pluginManagerModel;

    /**
     * Return one of the plugin manager-backed model instance.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return mixed A model instance that composes a plugin manager.
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (null === $this->pluginManagerService
            || null === $this->pluginManagerModel
            || ! class_exists($this->pluginManagerModel)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is an invalid factory; please check the $pluginManagerService and/or $pluginManagerModel values',
                get_class($this)
            ));
        }

        if (! $container->has($this->pluginManagerService)) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the %s service be present; service not found',
                get_class($this),
                $this->pluginManagerService
            ));
        }

        $class = $this->pluginManagerModel;
        return new $class($container->get($this->pluginManagerService));
    }

    /**
     * Return one of the plugin manager-backed model instance (v2).
     *
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return mixed A model instance that composes a plugin manager.
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, $this->pluginManagerModel);
    }
}
