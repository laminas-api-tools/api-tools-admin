<?php

namespace LaminasTest\ApiTools\Admin\Listener;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Listener\InjectModuleResourceLinksListener;
use Laminas\ApiTools\Admin\Listener\InjectModuleResourceLinksListenerFactory;
use PHPUnit\Framework\TestCase;

class InjectModuleResourceLinksListenerFactoryTest extends TestCase
{
    public function testFactoryReturnsTheListenerWithViewHelpersContainerComposed()
    {
        $factory = new InjectModuleResourceLinksListenerFactory();
        $viewHelpers = $this->prophesize(ContainerInterface::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);

        $container->get('ViewHelperManager')->willReturn($viewHelpers);
        $listener = $factory($container->reveal());
        $this->assertInstanceOf(InjectModuleResourceLinksListener::class, $listener);
        $this->assertAttributeSame($viewHelpers, 'viewHelpers', $listener);
    }
}
