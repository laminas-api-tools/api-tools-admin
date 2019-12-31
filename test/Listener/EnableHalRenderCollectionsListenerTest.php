<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Listener;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Listener\EnableHalRenderCollectionsListener;
use Laminas\ApiTools\Hal\Plugin\Hal;
use Laminas\Mvc\ApplicationInterface;
use Laminas\Mvc\MvcEvent;
use LaminasTest\ApiTools\Admin\RouteAssetsTrait;
use PHPUnit_Framework_TestCase as TestCase;

class EnableHalRenderCollectionsListenerTest extends TestCase
{
    use RouteAssetsTrait;

    public function setUp()
    {
        $this->event = $this->prophesize(MvcEvent::class);
        $this->routeMatch = $this->prophesize($this->getRouteMatchClass());
    }

    public function testListenerDoesNothingIfEventHasNoRouteMatch()
    {
        $listener = new EnableHalRenderCollectionsListener();
        $this->event->getRouteMatch()->willReturn(null)->shouldBeCalled();
        $this->event->getTarget()->shouldNotBeCalled();
        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerDoesNothingIfRouteMatchHasNoControllerParam()
    {
        $listener = new EnableHalRenderCollectionsListener();
        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal'])->shouldBeCalled();
        $this->routeMatch
            ->getParam('controller')
            ->willReturn(null)
            ->shouldBeCalled();

        $this->event->getTarget()->shouldNotBeCalled();
        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerDoesNothingIfRouteMatchControllerParamDoesNotMatchAdminNamespace()
    {
        $listener = new EnableHalRenderCollectionsListener();
        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal'])->shouldBeCalled();
        $this->routeMatch
            ->getParam('controller')
            ->willReturn('Foo\Bar\Baz')
            ->shouldBeCalled();

        $this->event->getTarget()->shouldNotBeCalled();
        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerEnablesCollectionRenderingOnHalPluginWhenControllerMatchesAdminNamespace()
    {
        $listener = new EnableHalRenderCollectionsListener();

        $plugin = $this->prophesize(Hal::class);
        $plugin->setRenderCollections(true)->shouldBeCalled();

        $helpers = $this->prophesize(ContainerInterface::class);
        $helpers->get('Hal')->will([$plugin, 'reveal'])->shouldBeCalled();

        $services = $this->prophesize(ContainerInterface::class);
        $services->get('ViewHelperManager')->will([$helpers, 'reveal'])->shouldBeCalled();

        $app = $this->prophesize(ApplicationInterface::class);
        $app->getServiceManager()->will([$services, 'reveal'])->shouldBeCalled();

        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal'])->shouldBeCalled();
        $this->routeMatch
            ->getParam('controller')
            ->willReturn('Laminas\ApiTools\Admin\Model\RestServiceModel')
            ->shouldBeCalled();

        $this->event->getTarget()->will([$app, 'reveal'])->shouldBeCalled();
        $this->assertNull($listener($this->event->reveal()));
    }
}
