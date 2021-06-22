<?php

declare(strict_types=1);

namespace BarConf;

use Laminas\ApiTools\Provider\ApiToolsProviderInterface;
use Laminas\Loader\StandardAutoloader;

class Module implements ApiToolsProviderInterface
{
    /** @return array */
    public function getAutoloaderConfig()
    {
        return [
            StandardAutoloader::class => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__,
                ],
            ],
        ];
    }

    /** @return array */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }
}
