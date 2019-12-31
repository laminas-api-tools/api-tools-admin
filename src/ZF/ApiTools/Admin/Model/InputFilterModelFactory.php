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

class InputFilterModelFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $services)
    {
        if (! $services->has('Laminas\ApiTools\Configuration\ConfigResourceFactory')) {
            throw new ServiceNotCreatedException(sprintf(
                '%s\\InputFilterModel requires that the Laminas\ApiTools\Configuration\ConfigResourceFactory service be present; service not found',
                __NAMESPACE__
            ));
        }
        return new InputFilterModel($services->get('Laminas\ApiTools\Configuration\ConfigResourceFactory'));
    }
}
