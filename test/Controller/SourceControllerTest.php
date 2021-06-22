<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Controller;

use Laminas\ApiTools\Admin\Controller\SourceController;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ApiTools\ApiProblem\View\ApiProblemModel;
use Laminas\Http\Request;
use Laminas\ModuleManager\ModuleManager;
use LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Module as BarModule;
use PHPUnit\Framework\TestCase;

class SourceControllerTest extends TestCase
{
    /** @var SourceController */
    private $controller;

    public function setUp(): void
    {
        $moduleManager    = new ModuleManager([]);
        $moduleResource   = new ModuleModel(
            $moduleManager,
            [],
            []
        );
        $this->controller = new SourceController($moduleResource);
    }

    /** @psalm-return array<array-key, array{0: string}> */
    public function invalidRequestMethods(): array
    {
        return [
            ['put'],
            ['patch'],
            ['post'],
            ['delete'],
        ];
    }

    /**
     * @dataProvider invalidRequestMethods
     */
    public function testProcessWithInvalidRequestMethodReturnsApiProblemModel(string $method): void
    {
        $request = new Request();
        $request->setMethod($method);
        $this->controller->setRequest($request);
        $result = $this->controller->sourceAction();
        self::assertInstanceOf(ApiProblemModel::class, $result);
        $apiProblem = $result->getApiProblem();
        self::assertEquals(405, $apiProblem->status);
    }

    public function testProcessGetRequest(): void
    {
        $moduleManager = $this->getMockBuilder(ModuleManager::class)
                               ->disableOriginalConstructor()
                               ->getMock();
        $moduleManager->expects($this->any())
                      ->method('getLoadedModules')
                      ->will($this->returnValue(['LaminasTest\ApiTools\Admin\Model\TestAsset\Bar' => new BarModule()]));

        $moduleResource = new ModuleModel($moduleManager, [], []);
        $controller     = new SourceController($moduleResource);

        $request = new Request();
        $request->setMethod('get');
        $request->getQuery()->module = 'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar';
        $request->getQuery()->class  = BarModule::class;

        $controller->setRequest($request);
        $result = $controller->sourceAction();

        self::assertTrue($result->getVariable('source') !== '');
        self::assertTrue($result->getVariable('file') !== '');
        self::assertEquals($result->getVariable('module'), $request->getQuery()->module);
        self::assertEquals($result->getVariable('class'), $request->getQuery()->class);
    }
}
