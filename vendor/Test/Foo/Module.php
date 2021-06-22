<?php

namespace Test\Foo;

use Laminas\ApiTools\Provider\ApiToolsProviderInterface;

class Module implements ApiToolsProviderInterface
{
    public function getConfig()
    {
        return [
            'api-tools-versioning' => [
                'default_version' => 123,
            ],
        ];
    }
}
