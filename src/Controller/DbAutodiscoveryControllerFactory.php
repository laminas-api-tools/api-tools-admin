<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class AutodiscoveryControllerFactory
 *
 * @package Laminas\ApiTools\Admin\Controller
 */
class DbAutodiscoveryControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $controllers
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $controllers)
    {
        $services = $controllers->getServiceLocator();
        /** @var \Laminas\ApiTools\Admin\Model\DbAutodiscoveryModel $model */
        $model = $services->get('Laminas\ApiTools\Admin\Model\DbAutodiscoveryModel');
        return new DbAutodiscoveryController($model);
    }
}
