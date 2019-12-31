<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\ModuleEntity;
use PHPUnit\Framework\TestCase;

class ModuleEntityTest extends TestCase
{
    public function testCanSetAndRetrieveModuleDefaultVersion()
    {
        $moduleEntity = new ModuleEntity('Test\Foo');
        $this->assertSame(1, $moduleEntity->getDefaultVersion()); // initial state

        $moduleEntity->exchangeArray(['default_version' => 123]);
        $this->assertSame(123, $moduleEntity->getDefaultVersion());
    }
}
