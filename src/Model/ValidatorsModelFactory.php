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

class ValidatorsModelFactory implements FactoryInterface
{
    /**
     * Return one of the plugin manager model instances
     *
     * @param ServiceLocatorInterface $services
     * @return object
     */
    public function createService(ServiceLocatorInterface $services)
    {
        if (! $services->has('ValidatorManager')) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the ValidatorManager service be present; service not found',
                get_class($this)
            ));
        }

        if (! $services->has('Laminas\ApiTools\Admin\Model\ValidatorMetadataModel')) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the %s\ValidatorMetadataModel service be present; service not found',
                get_class($this),
                __NAMESPACE__
            ));
        }

        return new ValidatorsModel(
            $services->get('ValidatorManager'),
            $services->get('Laminas\ApiTools\Admin\Model\ValidatorMetadataModel')
        );
    }
}
