<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

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
