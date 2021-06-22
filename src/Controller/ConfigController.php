<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\Configuration\ConfigResource;

class ConfigController extends AbstractConfigController
{
    /** @var ConfigResource */
    protected $config;

    public function __construct(ConfigResource $config)
    {
        $this->config = $config;
    }

    /** @return ConfigResource */
    public function getConfig()
    {
        return $this->config;
    }
}
