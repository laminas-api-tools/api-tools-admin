<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\ApiTools\Configuration\ResourceFactory;

class ModuleConfigController extends AbstractConfigController
{
    protected $configFactory;

    public function __construct(ResourceFactory $factory)
    {
        $this->configFactory = $factory;
    }

    public function getConfig()
    {
        $module = $this->params()->fromQuery('module', false);
        if (! $module) {
            return new ApiProblemResponse(
                new ApiProblem(400, 'Missing module parameter')
            );
        }
        $config = $this->configFactory->factory($module);
        return $config;
    }
}
