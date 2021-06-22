<?php

declare(strict_types=1);

namespace BazConf;

use Laminas\Loader\StandardAutoloader;

class Module
{
    public function getAutoloaderConfig(): array
    {
        return [
            StandardAutoloader::class => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src',
                ],
            ],
        ];
    }

    public function getConfig(): array
    {
        return include __DIR__ . '/config/module.config.php';
    }
}
