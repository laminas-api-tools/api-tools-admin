<?php

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
