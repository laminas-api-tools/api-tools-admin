<?php

declare(strict_types=1);

namespace AuthConfWithConfig;

use Laminas\Loader\StandardAutoloader;

class Module
{
    /** @return array<string, mixed> */
    public function getAutoloaderConfig(): array
    {
        return [
            StandardAutoloader::class => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
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
