<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ValidatorsControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllers)
    {
        $services = $controllers->getServiceLocator();
        return new ValidatorsController($services->get('Laminas\ApiTools\Admin\Model\ValidatorsModel'));
    }
}
