<?php

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\DocumentationModel;
use Laminas\ApiTools\Configuration\ResourceFactory;
use Laminas\Config\Writer\WriterInterface;
use PHPUnit\Framework\TestCase;

class DocumentationModelTest extends TestCase
{
    protected $actualDocData;

    protected $docModel = null;

    public function setup()
    {
        $this->actualDocData = include __DIR__ . '/TestAsset/module/Doc/config/documentation.config.php';

        $mockModuleUtils = $this->getMockBuilder('Laminas\ApiTools\Configuration\ModuleUtils')
            ->disableOriginalConstructor()
            ->getMock();
        $mockModuleUtils
            ->expects($this->any())
            ->method('getModuleConfigPath')
            ->will($this->returnValue(__DIR__ . '/TestAsset/module/Doc/config/module.config.php'));

        $configResourceFactory = new ResourceFactory(
            $mockModuleUtils,
            $this->prophesize(WriterInterface::class)->reveal()
        );
        $this->docModel = new DocumentationModel($configResourceFactory, $mockModuleUtils);
    }

    public function testFetchRestDocumentation()
    {
        $this->assertEquals(
            $this->actualDocData['Doc\\V1\\Rest\\FooBar\\Controller'],
            $this->docModel->fetchDocumentation('Doc', 'Doc\\V1\\Rest\\FooBar\\Controller')
        );
    }
}
