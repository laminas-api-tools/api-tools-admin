<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class FiltersModelFactory implements FactoryInterface
{
    /**
     * Return a filter plugin manager model instances
     *
     * @param ServiceLocatorInterface $services
     * @return FiltersModel
     */
    public function createService(ServiceLocatorInterface $services)
    {
        if (! $services->has('FilterManager')) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the FilterManager service be present; service not found',
                get_class($this)
            ));
        }

        $metadata = array();
        if ($services->has('Config')) {
            $config = $services->get('Config');
            if (isset($config['filter_metadata'])
                && is_array($config['filter_metadata'])
            ) {
                $metadata = $config['filter_metadata'];
            }
        }

        return new FiltersModel($services->get('FilterManager'), $metadata);
    }
}
