<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ApiTools\Configuration\ResourceFactory;

class ModuleConfigController extends AbstractConfigController
{
    /** @var ResourceFactory */
    protected $configFactory;

    public function __construct(ResourceFactory $factory)
    {
        $this->configFactory = $factory;
    }

    /** @return ApiProblemResponse|ConfigResource */
    public function getConfig()
    {
        $module = $this->params()->fromQuery('module', false);
        if (! $module) {
            return new ApiProblemResponse(
                new ApiProblem(400, 'Missing module parameter')
            );
        }
        return $this->configFactory->factory($module);
    }
}
