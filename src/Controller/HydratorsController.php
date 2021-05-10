<?php

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\Admin\Model\HydratorsModel;

class HydratorsController extends AbstractPluginManagerController
{
    protected $property = 'hydrators';

    public function __construct(HydratorsModel $model)
    {
        $this->model = $model;
    }

    public function hydratorsAction()
    {
        return $this->handleRequest();
    }
}
