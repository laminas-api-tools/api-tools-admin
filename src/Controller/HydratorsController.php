<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

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
