<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\HydratorsModel;
use Laminas\Hydrator\HydratorPluginManager;
use Prophecy\PhpUnit\ProphecyTrait;

use function count;

class HydratorsModelTest extends AbstractPluginManagerModelTest
{
    use ProphecyTrait;

    public function setUp(): void
    {
        $this->namespace = '\\Hydrator\\';
        $this->plugins   = new HydratorPluginManager($this->prophesize(ContainerInterface::class)->reveal());
        $this->model     = new HydratorsModel($this->plugins);
    }

    public function testFetchAllReturnsListOfAvailablePlugins(): void
    {
        $services = $this->model->fetchAll();
        self::assertGreaterThan(-1, count($services));
        foreach ($services as $service) {
            self::assertStringContainsString($this->namespace, $service);
        }
    }
}
