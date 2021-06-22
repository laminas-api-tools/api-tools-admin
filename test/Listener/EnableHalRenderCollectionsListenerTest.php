<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Listener;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Listener\EnableHalRenderCollectionsListener;
use Laminas\ApiTools\Admin\Model\RestServiceModel;
use Laminas\ApiTools\Hal\Plugin\Hal;
use Laminas\Mvc\ApplicationInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Router\RouteMatch as V2RouteMatch;
use Laminas\Router\RouteMatch;
use LaminasTest\ApiTools\Admin\RouteAssetsTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class EnableHalRenderCollectionsListenerTest extends TestCase
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
        $listener = new EnableHalRenderCollectionsListener();
        $this->event->getRouteMatch()->willReturn(null)->shouldBeCalled();
        $this->event->getTarget()->shouldNotBeCalled();
        self::assertNull($listener($this->event->reveal()));
    }

    public function testListenerDoesNothingIfRouteMatchHasNoControllerParam(): void
    {
        $listener = new EnableHalRenderCollectionsListener();
        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal'])->shouldBeCalled();
        $this->routeMatch
            ->getParam('controller')
            ->willReturn(null)
            ->shouldBeCalled();

        $this->event->getTarget()->shouldNotBeCalled();
        self::assertNull($listener($this->event->reveal()));
    }

    public function testListenerDoesNothingIfRouteMatchControllerParamDoesNotMatchAdminNamespace(): void
    {
        $listener = new EnableHalRenderCollectionsListener();
        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal'])->shouldBeCalled();
        $this->routeMatch
            ->getParam('controller')
            ->willReturn('Foo\Bar\Baz')
            ->shouldBeCalled();

        $this->event->getTarget()->shouldNotBeCalled();
        self::assertNull($listener($this->event->reveal()));
    }

    public function testListenerEnablesCollectionRenderingOnHalPluginWhenControllerMatchesAdminNamespace(): void
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
            ->willReturn(RestServiceModel::class)
            ->shouldBeCalled();

        $this->event->getTarget()->will([$app, 'reveal'])->shouldBeCalled();
        self::assertNull($listener($this->event->reveal()));
    }
}
