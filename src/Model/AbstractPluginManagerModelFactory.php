<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

use function class_exists;
use function sprintf;

abstract class AbstractPluginManagerModelFactory implements FactoryInterface
{
    /** @var string */
    protected $pluginManagerService;

    /** @var string */
    protected $pluginManagerModel;

    /**
     * Return one of the plugin manager-backed model instance.
     *
     * @param string $requestedName
     * @param null|array $options
     * @return mixed A model instance that composes a plugin manager.
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        if (
            null === $this->pluginManagerService
            || null === $this->pluginManagerModel
            || ! class_exists($this->pluginManagerModel)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is an invalid factory; please check the $pluginManagerService and/or $pluginManagerModel values',
                static::class
            ));
        }

        if (! $container->has($this->pluginManagerService)) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the %s service be present; service not found',
                static::class,
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
     * @return mixed A model instance that composes a plugin manager.
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, $this->pluginManagerModel);
    }
}
