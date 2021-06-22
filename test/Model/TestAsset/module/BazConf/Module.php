<?php

declare(strict_types=1);

namespace BazConf;

use Laminas\Loader\StandardAutoloader;

class Module
{
    /** @return array<string, mixed> */
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

    /** @return array<string, mixed> */
    public function getConfig(): array
    {
        return include __DIR__ . '/config/module.config.php';
    }
}
