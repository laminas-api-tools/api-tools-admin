<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

class DashboardControllerFactory
{
    public function __invoke($controllers)
    {
        $services = $controllers->getServiceLocator();
        return new DashboardController(
            $services->get('Laminas\ApiTools\Admin\Model\AuthenticationModel'),
            $services->get('Laminas\ApiTools\Admin\Model\ContentNegotiationModel'),
            $services->get('Laminas\ApiTools\Admin\Model\DbAdapterModel'),
            $services->get('Laminas\ApiTools\Admin\Model\ModuleModel'),
            $services->get('Laminas\ApiTools\Admin\Model\RestServiceModelFactory'),
            $services->get('Laminas\ApiTools\Admin\Model\RpcServiceModelFactory')
        );
    }
}
