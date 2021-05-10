<?php

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Configuration\ConfigResourceFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class InputFilterModelFactory implements FactoryInterface
{
    /**
     * Create and return an InputFilterModel instance.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return InputFilterModel
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (! $container->has(ConfigResourceFactory::class)
            && ! $container->has(\ZF\Configuration\ConfigResourceFactory::class)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the %s service be present; service not found',
                InputFilterModel::class,
                ConfigResourceFactory::class
            ));
        }
        return new InputFilterModel(
            $container->has(ConfigResourceFactory::class)
                ? $container->get(ConfigResourceFactory::class)
                : $container->get(\ZF\Configuration\ConfigResourceFactory::class)
        );
    }

    /**
     * Create and return an InputFilterModel instance (v2).
     *
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return InputFilterModel
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, InputFilterModel::class);
    }
}
