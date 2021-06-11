<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Listener;

use Laminas\ApiTools\Admin\Listener\NormalizeMatchedInputFilterNameListener;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Router\RouteMatch as V2RouteMatch;
use Laminas\Router\RouteMatch;
use LaminasTest\ApiTools\Admin\RouteAssetsTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class NormalizeMatchedInputFilterNameListenerTest extends TestCase
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
        $listener = new NormalizeMatchedInputFilterNameListener();
        $this->event->getRouteMatch()->willReturn(null)->shouldBeCalled();
        self::assertNull($listener($this->event->reveal()));
    }

    public function testListenerDoesNothingIfRouteMatchHasNoInputFilterName(): void
    {
        $listener = new NormalizeMatchedInputFilterNameListener();
        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal'])->shouldBeCalled();
        $this->routeMatch
            ->getParam('input_filter_name')
            ->willReturn(null)
            ->shouldBeCalled();
        $this->routeMatch
            ->setParam('input_filter_name', Argument::type('string'))
            ->shouldNotBeCalled();
        self::assertNull($listener($this->event->reveal()));
    }

    public function testListenerReplacesDashesWithBackslashesInMatchedInputFilterName(): void
    {
        $listener = new NormalizeMatchedInputFilterNameListener();
        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal'])->shouldBeCalled();
        $this->routeMatch
            ->getParam('input_filter_name')
            ->willReturn('Foo-Bar-BazInputFilter')
            ->shouldBeCalled();
        $this->routeMatch
            ->setParam('input_filter_name', 'Foo\\Bar\\BazInputFilter')
            ->shouldBeCalled();
        self::assertNull($listener($this->event->reveal()));
    }
}
