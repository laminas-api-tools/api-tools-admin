<?php

namespace LaminasTest\ApiTools\Admin\Controller;

use Laminas\ApiTools\Admin\Controller\SourceController;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\Http\Request;
use Laminas\ModuleManager\ModuleManager;
use LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Module as BarModule;
use PHPUnit\Framework\TestCase;

class SourceControllerTest extends TestCase
{
    public function setUp()
    {
        $this->moduleManager  = new ModuleManager([]);
        $this->moduleResource = new ModuleModel(
            $this->moduleManager,
            [],
            []
        );
        $this->controller     = new SourceController($this->moduleResource);
    }

    public function invalidRequestMethods()
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
    public function testProcessWithInvalidRequestMethodReturnsApiProblemModel($method)
    {
        $request = new Request();
        $request->setMethod($method);
        $this->controller->setRequest($request);
        $result = $this->controller->sourceAction();
        $this->assertInstanceOf('Laminas\ApiTools\ApiProblem\View\ApiProblemModel', $result);
        $apiProblem = $result->getApiProblem();
        $this->assertEquals(405, $apiProblem->status);
    }

    public function testProcessGetRequest()
    {
        $moduleManager  = $this->getMockBuilder('Laminas\ModuleManager\ModuleManager')
                               ->disableOriginalConstructor()
                               ->getMock();
        $moduleManager->expects($this->any())
                      ->method('getLoadedModules')
                      ->will($this->returnValue(['LaminasTest\ApiTools\Admin\Model\TestAsset\Bar' => new BarModule]));

        $moduleResource = new ModuleModel($moduleManager, [], []);
        $controller     = new SourceController($moduleResource);

        $request = new Request();
        $request->setMethod('get');
        $request->getQuery()->module = 'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar';
        $request->getQuery()->class = 'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Module';

        $controller->setRequest($request);
        $result = $controller->sourceAction();

        $this->assertTrue($result->getVariable('source') != '');
        $this->assertTrue($result->getVariable('file') != '');
        $this->assertEquals($result->getVariable('module'), $request->getQuery()->module);
        $this->assertEquals($result->getVariable('class'), $request->getQuery()->class);
    }
}
