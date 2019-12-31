<?php

namespace Test\Bar;

use Laminas\ApiTools\Provider\ApiToolsProviderInterface;

class Module implements ApiToolsProviderInterface
{
    public function getConfig()
    {
        return array();
    }
}
