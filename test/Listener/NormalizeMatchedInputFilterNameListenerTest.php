<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Listener;

use Laminas\ApiTools\Admin\Listener\NormalizeMatchedInputFilterNameListener;
use Laminas\Mvc\MvcEvent;
use LaminasTest\ApiTools\Admin\RouteAssetsTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class NormalizeMatchedInputFilterNameListenerTest extends TestCase
{
    use RouteAssetsTrait;

    public function setUp()
    {
        $this->event      = $this->prophesize(MvcEvent::class);
        $this->routeMatch = $this->prophesize($this->getRouteMatchClass());
    }

    public function testListenerDoesNothingIfEventHasNoRouteMatch()
    {
        $listener = new NormalizeMatchedInputFilterNameListener();
        $this->event->getRouteMatch()->willReturn(null)->shouldBeCalled();
        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerDoesNothingIfRouteMatchHasNoInputFilterName()
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
        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerReplacesDashesWithBackslashesInMatchedInputFilterName()
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
        $this->assertNull($listener($this->event->reveal()));
    }
}
