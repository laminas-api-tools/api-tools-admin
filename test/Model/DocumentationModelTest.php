<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\DocumentationModel;
use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ApiTools\Configuration\ResourceFactory;
use Laminas\Config\Writer\WriterInterface;

class DocumentationModelTest extends \PHPUnit_Framework_TestCase
{
    protected $actualDocData;

    protected $docModel = null;

    public function setup()
    {
        $this->actualDocData = include __DIR__ . '/TestAsset/module/Doc/config/documentation.config.php';

        $mockModuleUtils = $this->getMock(
            'Laminas\ApiTools\Configuration\ModuleUtils',
            ['getModuleConfigPath'],
            [],
            '',
            false
        );
        $mockModuleUtils->expects($this->any())
            ->method('getModuleConfigPath')
            ->will($this->returnValue(__DIR__ . '/TestAsset/module/Doc/config/module.config.php'));

        $mockWriter = $this->getMockBuilder(WriterInterface::class)->getMock();

        $configResourceFactory = new ResourceFactory(
            $mockModuleUtils,
            $mockWriter
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
