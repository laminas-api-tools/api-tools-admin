<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Listener;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Listener\InjectModuleResourceLinksListener;
use Laminas\ApiTools\Admin\Listener\InjectModuleResourceLinksListenerFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class InjectModuleResourceLinksListenerFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testFactoryReturnsTheListenerWithViewHelpersContainerComposed(): void
    {
        $factory     = new InjectModuleResourceLinksListenerFactory();
        $viewHelpers = $this->prophesize(ContainerInterface::class)->reveal();
        /** @var ObjectProphecy|ContainerInterface $container */
        $container = $this->prophesize(ContainerInterface::class);

        $container->get('ViewHelperManager')->willReturn($viewHelpers);
        $listener = $factory($container->reveal());
        self::assertInstanceOf(InjectModuleResourceLinksListener::class, $listener);
        //self::assertAttributeSame($viewHelpers, 'viewHelpers', $listener);
    }
}
