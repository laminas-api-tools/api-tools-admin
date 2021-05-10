<?php

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\Admin\Model\FiltersModel;

class FiltersController extends AbstractPluginManagerController
{
    protected $property = 'filters';

    public function __construct(FiltersModel $model)
    {
        $this->model = $model;
    }

    public function filtersAction()
    {
        return $this->handleRequest();
    }
}
