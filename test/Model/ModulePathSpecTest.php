<?php

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\ModulePathSpec;
use PHPUnit\Framework\TestCase;

class ModulePathSpecTest extends TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getModuleUtils()
    {
        $utils = $this->getMockBuilder('Laminas\ApiTools\Configuration\ModuleUtils')
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
    public function testDefaultValuesArePSR0()
    {
        $pathSpec = new ModulePathSpec($this->getModuleUtils());

        $this->assertEquals('/app/ModuleName', $pathSpec->getModulePath('ModuleName'));
        $this->assertEquals('/app/ModuleName/config', $pathSpec->getModuleConfigPath('ModuleName'));
        $this->assertEquals(
            '/app/ModuleName/config/module.config.php',
            $pathSpec->getModuleConfigFilePath('ModuleName')
        );
        $this->assertEquals('.', $pathSpec->getApplicationPath());
        $this->assertEquals('/app/ModuleName/src/ModuleName', $pathSpec->getModuleSourcePath('ModuleName'));
        $this->assertEquals('/app/ModuleName/src/ModuleName', $pathSpec->getModuleSourcePath('ModuleName'));
        $this->assertEquals('psr-0', $pathSpec->getPathSpec());
        $this->assertEquals('/app/ModuleName/view', $pathSpec->getModuleViewPath('ModuleName'));
        $this->assertEquals('/app/ModuleName/src/ModuleName/V1/Rest/', $pathSpec->getRestPath('ModuleName'));
        $this->assertEquals('/app/ModuleName/src/ModuleName/V1/Rpc/', $pathSpec->getRpcPath('ModuleName'));
    }

    /**
     * @group pathspec
     */
    public function testApiPathsPsr0()
    {
        $basePath = '/app/ModuleName/src/ModuleName/V2/';
        $pathSpec = new ModulePathSpec($this->getModuleUtils());

        $this->assertEquals($basePath . 'Rest/', $pathSpec->getRestPath('ModuleName', 2));
        $this->assertEquals($basePath . 'Rest/ServiceName', $pathSpec->getRestPath('ModuleName', 2, 'ServiceName'));

        $this->assertEquals($basePath . 'Rpc/', $pathSpec->getRpcPath('ModuleName', 2));
        $this->assertEquals($basePath . 'Rpc/ServiceName', $pathSpec->getRpcPath('ModuleName', 2, 'ServiceName'));
    }

    /**
     * @group pathspec
     */
    public function testApiPathsPsr4()
    {
        $pathSpec = new ModulePathSpec($this->getModuleUtils(), 'psr-4');
        $basePath = '/app/ModuleName/src/V2/';

        $this->assertEquals($basePath . 'Rest/', $pathSpec->getRestPath('ModuleName', 2));
        $this->assertEquals($basePath . 'Rest/ServiceName', $pathSpec->getRestPath('ModuleName', 2, 'ServiceName'));

        $this->assertEquals($basePath . 'Rpc/', $pathSpec->getRpcPath('ModuleName', 2));
        $this->assertEquals($basePath . 'Rpc/ServiceName', $pathSpec->getRpcPath('ModuleName', 2, 'ServiceName'));
    }
}
