<?php

namespace Test\Foo;

use Laminas\ApiTools\Provider\ApiToolsProviderInterface;

class Module implements ApiToolsProviderInterface
{
    public function getConfig()
    {
        return array(
            'api-tools-versioning' => array(
                'default_version' => 123,
            ),
        );
    }
}
