<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Controller\ModuleCreationController;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ApiTools\ContentNegotiation\ParameterDataContainer;
use Laminas\Http\Request;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Mvc\MvcEvent;
use PHPUnit_Framework_TestCase as TestCase;

class ModuleCreationControllerTest extends TestCase
{
    public function setUp()
    {
        $this->moduleManager  = new ModuleManager([]);
        $this->moduleResource = new ModuleModel(
            $this->moduleManager,
            [],
            []
        );
        $this->controller     = new ModuleCreationController($this->moduleResource);
    }

    public function invalidRequestMethods()
    {
        return [
            ['get'],
            ['patch'],
            ['post'],
            ['delete'],
        ];
    }

    /**
     * @dataProvider invalidRequestMethods
     */
    public function testProcessWithInvalidRequestMethodReturnsApiProblemResponse($method)
    {
        $request = new Request();
        $request->setMethod($method);
        $this->controller->setRequest($request);
        $result = $this->controller->apiEnableAction();
        $this->assertInstanceOf('Laminas\ApiTools\ApiProblem\ApiProblemResponse', $result);
        $apiProblem = $result->getApiProblem();
        $this->assertEquals(405, $apiProblem->status);
    }

    public function testProcessPutRequest()
    {
        $currentDir = getcwd();
        $tmpDir     = sys_get_temp_dir() . "/" . uniqid(__NAMESPACE__ . '_');

        mkdir($tmpDir);
        mkdir("$tmpDir/module/Foo", 0775, true);
        mkdir("$tmpDir/config");
        file_put_contents(
            "$tmpDir/config/application.config.php",
            '<' . '?php return array(\'modules\'=>array(\'Foo\'));'
        );
        file_put_contents("$tmpDir/module/Foo/Module.php", "<" . "?php\n\nnamespace Foo;\n\nclass Module\n{\n}");
        chdir($tmpDir);

        require 'module/Foo/Module.php';

        $moduleManager  = $this->getMockBuilder('Laminas\ModuleManager\ModuleManager')
                               ->disableOriginalConstructor()
                               ->getMock();
        $moduleManager->expects($this->any())
                      ->method('getLoadedModules')
                      ->will($this->returnValue(['Foo' => new \Foo\Module]));

        $moduleResource = new ModuleModel(
            $moduleManager,
            [],
            []
        );
        $controller     = new ModuleCreationController($moduleResource);

        $request = new Request();
        $request->setMethod('put');
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');

        $parameters = new ParameterDataContainer();
        $parameters->setBodyParam('module', 'Foo');
        $event = new MvcEvent();
        $event->setParam('LaminasContentNegotiationParameterData', $parameters);

        $plugins = new PluginManager($this->prophesize(ContainerInterface::class)->reveal());
        $plugins->setInvokableClass('bodyParam', 'Laminas\ApiTools\ContentNegotiation\ControllerPlugin\BodyParam');

        $controller->setRequest($request);
        $controller->setEvent($event);
        $controller->setPluginManager($plugins);

        $result = $controller->apiEnableAction();

        $this->assertInstanceOf('Laminas\ApiTools\ContentNegotiation\ViewModel', $result);
        $payload = $result->getVariable('payload');
        $this->assertInstanceOf('Laminas\ApiTools\Hal\Entity', $payload);
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\ModuleEntity', $payload->getEntity());

        $metadata = $payload->getEntity();
        $this->assertEquals('Foo', $metadata->getName());

        $this->removeDir($tmpDir);
        chdir($currentDir);
    }

    protected function removeDir($dir)
    {
        $files = array_diff(scandir($dir), ['.','..']);
        foreach ($files as $file) {
            is_dir("$dir/$file") ? $this->removeDir("$dir/$file") : unlink("$dir/$file");
        }
        return @rmdir($dir);
    }
}
