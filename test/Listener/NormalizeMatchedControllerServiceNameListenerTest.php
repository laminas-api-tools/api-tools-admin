<?php

namespace LaminasTest\ApiTools\Admin\Listener;

use Laminas\ApiTools\Admin\Listener\NormalizeMatchedControllerServiceNameListener;
use Laminas\Mvc\MvcEvent;
use LaminasTest\ApiTools\Admin\RouteAssetsTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class NormalizeMatchedControllerServiceNameListenerTest extends TestCase
{
    use RouteAssetsTrait;

    public function setUp()
    {
        $this->event = $this->prophesize(MvcEvent::class);
        $this->routeMatch = $this->prophesize($this->getRouteMatchClass());
    }

    public function testListenerDoesNothingIfEventHasNoRouteMatch()
    {
        $listener = new NormalizeMatchedControllerServiceNameListener();
        $this->event->getRouteMatch()->willReturn(null)->shouldBeCalled();
        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerDoesNothingIfRouteMatchHasNoControllerServiceName()
    {
        $listener = new NormalizeMatchedControllerServiceNameListener();
        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal'])->shouldBeCalled();
        $this->routeMatch
            ->getParam('controller_service_name')
            ->willReturn(null)
            ->shouldBeCalled();
        $this->routeMatch
            ->setParam('controller_service_name', Argument::type('string'))
            ->shouldNotBeCalled();
        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerReplacesDashesWithBackslashesInMatchedControllerServiceName()
    {
        $listener = new NormalizeMatchedControllerServiceNameListener();
        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal'])->shouldBeCalled();
        $this->routeMatch
            ->getParam('controller_service_name')
            ->willReturn('Foo-Bar-BazController')
            ->shouldBeCalled();
        $this->routeMatch
            ->setParam('controller_service_name', 'Foo\\Bar\\BazController')
            ->shouldBeCalled();
        $this->assertNull($listener($this->event->reveal()));
    }
}
