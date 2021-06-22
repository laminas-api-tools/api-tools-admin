<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\Admin\Model\HydratorsModel;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\View\Model\JsonModel;

class HydratorsController extends AbstractPluginManagerController
{
    /** @var string */
    protected $property = 'hydrators';

    public function __construct(HydratorsModel $model)
    {
        $this->model = $model;
    }

    /** @return JsonModel|ApiProblemResponse */
    public function hydratorsAction()
    {
        return $this->handleRequest();
    }
}
