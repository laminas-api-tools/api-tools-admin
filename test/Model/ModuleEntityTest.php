<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\ModuleEntity;
use PHPUnit\Framework\TestCase;

class ModuleEntityTest extends TestCase
{
    public function testCanSetAndRetrieveModuleDefaultVersion(): void
    {
        $moduleEntity = new ModuleEntity('Test\Foo');
        self::assertSame(1, $moduleEntity->getDefaultVersion()); // initial state

        $moduleEntity->exchangeArray(['default_version' => 123]);
        self::assertSame(123, $moduleEntity->getDefaultVersion());
    }
}
