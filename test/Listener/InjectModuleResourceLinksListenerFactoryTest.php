<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Listener;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Listener\InjectModuleResourceLinksListener;
use Laminas\ApiTools\Admin\Listener\InjectModuleResourceLinksListenerFactory;
use PHPUnit_Framework_TestCase as TestCase;

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
