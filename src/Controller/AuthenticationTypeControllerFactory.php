<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

class AuthenticationTypeControllerFactory
{
    public function __invoke($controllers)
    {
        $services = $controllers->getServiceLocator();
        return new AuthenticationTypeController(
            $services->get('Laminas\ApiTools\MvcAuth\Authentication\DefaultAuthenticationListener')
        );
    }
}
