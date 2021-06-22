<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

use function is_array;
use function sprintf;

class FiltersModelFactory implements FactoryInterface
{
    /**
     * Return a filter plugin manager model instance.
     *
     * @param string $requestedName
     * @param null|array $options
     * @return FiltersModel
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        if (! $container->has('FilterManager')) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the FilterManager service be present; service not found',
                static::class
            ));
        }

        $metadata = [];
        if ($container->has('config')) {
            $config = $container->get('config');
            if (
                isset($config['filter_metadata'])
                && is_array($config['filter_metadata'])
            ) {
                $metadata = $config['filter_metadata'];
            }
        }

        return new FiltersModel($container->get('FilterManager'), $metadata);
    }

    /**
     * Return a filter plugin manager model instance (v2).
     *
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @return FiltersModel
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, FiltersModel::class);
    }
}
