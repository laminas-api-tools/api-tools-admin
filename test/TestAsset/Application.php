<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\TestAsset;

use Laminas\ServiceManager\ServiceManager;

class Application
{
    /** @var ?ServiceManager */
    protected $services;

    /** @param ServiceManager $services */
    public function setServiceManager(object $services): void
    {
        $this->services = $services;
    }

    public function getServiceManager(): ?ServiceManager
    {
        return $this->services;
    }
}
