<?php

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\Admin\Model\ValidatorsModel;

class ValidatorsController extends AbstractPluginManagerController
{
    protected $property = 'validators';

    public function __construct(ValidatorsModel $model)
    {
        $this->model = $model;
    }

    public function validatorsAction()
    {
        return $this->handleRequest();
    }
}
