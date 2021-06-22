<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\Admin\Model\FiltersModel;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\ApiTools\ContentNegotiation\JsonModel;

class FiltersController extends AbstractPluginManagerController
{
    /** @var string */
    protected $property = 'filters';

    public function __construct(FiltersModel $model)
    {
        $this->model = $model;
    }

    /** @return ApiProblemResponse|JsonModel */
    public function filtersAction()
    {
        return $this->handleRequest();
    }
}
