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

class FiltersModelFactory implements FactoryInterface
{
    /**
     * Return a filter plugin manager model instance.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return FiltersModel
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (! $container->has('FilterManager')) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the FilterManager service be present; service not found',
                get_class($this)
            ));
        }

        $metadata = [];
        if ($container->has('config')) {
            $config = $container->get('config');
            if (isset($config['filter_metadata'])
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
     * @param ServiceLocatorInterface $container
     * @return FiltersModel
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, FiltersModel::class);
    }
}
