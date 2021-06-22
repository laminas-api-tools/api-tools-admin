<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Listener;

use Laminas\ApiTools\Admin\Listener\NormalizeMatchedControllerServiceNameListener;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Router\RouteMatch as V2RouteMatch;
use Laminas\Router\RouteMatch;
use LaminasTest\ApiTools\Admin\RouteAssetsTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class NormalizeMatchedControllerServiceNameListenerTest extends TestCase
{
    use ProphecyTrait;
    use RouteAssetsTrait;

    /** @var ObjectProphecy|MvcEvent */
    private $event;
    /** @var ObjectProphecy|V2RouteMatch|RouteMatch */
    private $routeMatch;

    public function setUp(): void
    {
        $this->event      = $this->prophesize(MvcEvent::class);
        $this->routeMatch = $this->prophesize($this->getRouteMatchClass());
    }

    public function testListenerDoesNothingIfEventHasNoRouteMatch(): void
    {
        $listener = new NormalizeMatchedControllerServiceNameListener();
        $this->event->getRouteMatch()->willReturn(null)->shouldBeCalled();
        self::assertNull($listener($this->event->reveal()));
    }

    public function testListenerDoesNothingIfRouteMatchHasNoControllerServiceName(): void
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
        self::assertNull($listener($this->event->reveal()));
    }

    public function testListenerReplacesDashesWithBackslashesInMatchedControllerServiceName(): void
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
        self::assertNull($listener($this->event->reveal()));
    }
}
