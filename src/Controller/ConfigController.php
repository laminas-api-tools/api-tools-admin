<?php

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\Configuration\ConfigResource;

class ConfigController extends AbstractConfigController
{
    protected $config;

    public function __construct(ConfigResource $config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }
}
