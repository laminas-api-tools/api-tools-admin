<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\ModulePathSpec;
use Laminas\ApiTools\Configuration\ModuleUtils;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ModulePathSpecTest extends TestCase
{
    /**
     * @return MockObject|ModuleUtils
     */
    protected function getModuleUtils(): MockObject
    {
        $utils = $this->getMockBuilder(ModuleUtils::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $utils
            ->expects($this->any())
            ->method('getModulePath')
            ->will(
                $this->returnValue('/app/ModuleName')
            );

        return $utils;
    }

    /**
     * @group pathspec
     */
    public function testDefaultValuesArePSR0(): void
    {
        $pathSpec = new ModulePathSpec($this->getModuleUtils());

        self::assertEquals('/app/ModuleName', $pathSpec->getModulePath('ModuleName'));
        self::assertEquals('/app/ModuleName/config', $pathSpec->getModuleConfigPath('ModuleName'));
        self::assertEquals(
            '/app/ModuleName/config/module.config.php',
            $pathSpec->getModuleConfigFilePath('ModuleName')
        );
        self::assertEquals('.', $pathSpec->getApplicationPath());
        self::assertEquals('/app/ModuleName/src/ModuleName', $pathSpec->getModuleSourcePath('ModuleName'));
        self::assertEquals('/app/ModuleName/src/ModuleName', $pathSpec->getModuleSourcePath('ModuleName'));
        self::assertEquals('psr-0', $pathSpec->getPathSpec());
        self::assertEquals('/app/ModuleName/view', $pathSpec->getModuleViewPath('ModuleName'));
        self::assertEquals('/app/ModuleName/src/ModuleName/V1/Rest/', $pathSpec->getRestPath('ModuleName'));
        self::assertEquals('/app/ModuleName/src/ModuleName/V1/Rpc/', $pathSpec->getRpcPath('ModuleName'));
    }

    /**
     * @group pathspec
     */
    public function testApiPathsPsr0(): void
    {
        $basePath = '/app/ModuleName/src/ModuleName/V2/';
        $pathSpec = new ModulePathSpec($this->getModuleUtils());

        self::assertEquals($basePath . 'Rest/', $pathSpec->getRestPath('ModuleName', 2));
        self::assertEquals($basePath . 'Rest/ServiceName', $pathSpec->getRestPath('ModuleName', 2, 'ServiceName'));

        self::assertEquals($basePath . 'Rpc/', $pathSpec->getRpcPath('ModuleName', 2));
        self::assertEquals($basePath . 'Rpc/ServiceName', $pathSpec->getRpcPath('ModuleName', 2, 'ServiceName'));
    }

    /**
     * @group pathspec
     */
    public function testApiPathsPsr4(): void
    {
        $pathSpec = new ModulePathSpec($this->getModuleUtils(), 'psr-4');
        $basePath = '/app/ModuleName/src/V2/';

        self::assertEquals($basePath . 'Rest/', $pathSpec->getRestPath('ModuleName', 2));
        self::assertEquals($basePath . 'Rest/ServiceName', $pathSpec->getRestPath('ModuleName', 2, 'ServiceName'));

        self::assertEquals($basePath . 'Rpc/', $pathSpec->getRpcPath('ModuleName', 2));
        self::assertEquals($basePath . 'Rpc/ServiceName', $pathSpec->getRpcPath('ModuleName', 2, 'ServiceName'));
    }
}
