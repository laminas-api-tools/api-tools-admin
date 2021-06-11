<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\DocumentationModel;
use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ApiTools\Configuration\ResourceFactory;
use Laminas\Config\Writer\WriterInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class DocumentationModelTest extends TestCase
{
    use ProphecyTrait;

    /** @var array<string, mixed> */
    protected $actualDocData;

    /** @var DocumentationModel */
    protected $docModel;

    public function setup(): void
    {
        $this->actualDocData = include __DIR__ . '/TestAsset/module/Doc/config/documentation.config.php';

        $mockModuleUtils = $this->getMockBuilder(ModuleUtils::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $mockModuleUtils
            ->expects($this->any())
            ->method('getModuleConfigPath')
            ->will($this->returnValue(__DIR__ . '/TestAsset/module/Doc/config/module.config.php'));

        /** @var ObjectProphecy|WriterInterface $writer */
        $writer                = $this->prophesize(WriterInterface::class)->reveal();
        $configResourceFactory = new ResourceFactory($mockModuleUtils, $writer);
        $this->docModel        = new DocumentationModel($configResourceFactory, $mockModuleUtils);
    }

    public function testFetchRestDocumentation(): void
    {
        self::assertEquals(
            $this->actualDocData['Doc\\V1\\Rest\\FooBar\\Controller'],
            $this->docModel->fetchDocumentation('Doc', 'Doc\\V1\\Rest\\FooBar\\Controller')
        );
    }
}
