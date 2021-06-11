<?php

declare(strict_types=1);

namespace BarConf;

use Laminas\ApiTools\Provider\ApiToolsProviderInterface;
use Laminas\Loader\StandardAutoloader;

class Module implements ApiToolsProviderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getAutoloaderConfig(): array
    {
        return [
            StandardAutoloader::class => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return include __DIR__ . '/../../config/module.config.php';
    }
}
