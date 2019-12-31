<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\TestAsset;

class Application
{
    protected $services;

    public function setServiceManager($services)
    {
        $this->services = $services;
    }

    public function getServiceManager()
    {
        return $this->services;
    }
}
