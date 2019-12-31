<?php

namespace Test\Foo;

use Laminas\ApiTools\ApiToolsModuleInterface;

class Module implements ApiToolsModuleInterface
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
