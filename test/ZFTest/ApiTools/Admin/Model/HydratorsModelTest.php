<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\HydratorsModel;
use Laminas\Stdlib\Hydrator\HydratorPluginManager;

class HydratorsModelTest extends AbstractPluginManagerModelTest
{
    public function setUp()
    {
        $this->plugins = new HydratorPluginManager();
        $this->model = new HydratorsModel($this->plugins);
    }
}
