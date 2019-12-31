<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin;

use Laminas\ApiTools\Admin\Module;
use Laminas\ApiTools\Hal\Plugin\Hal;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Router\RouteMatch;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\HelperPluginManager;
use PHPUnit_Framework_TestCase as TestCase;

class ModuleTest extends TestCase
{
    public function setUp()
    {
        $this->services = new ServiceManager();
        $this->module = new Module();
    }

    public function setupServiceChain()
    {
        $this->hal = new Hal();
        $this->helpers = new HelperPluginManager();
        $this->helpers->setService('Hal', $this->hal);
        $this->helpers->setServiceLocator($this->services);
        $this->services->setService('ViewHelperManager', $this->helpers);
        $this->application = new TestAsset\Application();
        $this->application->setServiceManager($this->services);
    }

    public function testRouteListenerDoesNothingIfNoRouteMatches()
    {
        $event = new MvcEvent();
        $this->assertNull($this->module->onRoute($event));
    }

    public function testRouteListenerDoesNothingIfRouteMatchesDoNotContainController()
    {
        $matches = new RouteMatch([]);
        $event = new MvcEvent();
        $event->setRouteMatch($matches);
        $this->assertNull($this->module->onRoute($event));
    }

    public function testRouteListenerDoesNothingIfRouteMatchControllerIsNotRelevant()
    {
        $matches = new RouteMatch([
            'controller' => 'Foo\Bar',
        ]);
        $event = new MvcEvent();
        $event->setRouteMatch($matches);
        $this->assertNull($this->module->onRoute($event));
    }

    public function testRouteListenerModifiesHalPluginToRenderCollectionsIfControllerIsRelevant()
    {
        $this->setupServiceChain();
        $this->hal->setRenderCollections(false);

        $matches = new RouteMatch([
            'controller' => 'Laminas\ApiTools\Admin\Foo\Controller',
        ]);
        $event = new MvcEvent();
        $event->setRouteMatch($matches);
        $event->setTarget($this->application);

        $this->assertNull($this->module->onRoute($event));
        $this->assertTrue($this->hal->getRenderCollections());
    }
}
