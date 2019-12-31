<?php

namespace Test\Bar;

use Laminas\ApiTools\ApiToolsModuleInterface;

class Module implements ApiToolsModuleInterface
{
    public function getConfig()
    {
        return array();
    }
}
