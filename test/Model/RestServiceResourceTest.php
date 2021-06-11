<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use BarConf;
use Laminas\ApiTools\Admin\Model\DbConnectedRestServiceEntity;
use Laminas\ApiTools\Admin\Model\DbConnectedRestServiceModel;
use Laminas\ApiTools\Admin\Model\DocumentationModel;
use Laminas\ApiTools\Admin\Model\InputFilterModel;
use Laminas\ApiTools\Admin\Model\ModuleEntity;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ApiTools\Admin\Model\ModulePathSpec;
use Laminas\ApiTools\Admin\Model\RestServiceEntity;
use Laminas\ApiTools\Admin\Model\RestServiceModel;
use Laminas\ApiTools\Admin\Model\RestServiceModelFactory;
use Laminas\ApiTools\Admin\Model\RestServiceResource;
use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ApiTools\Configuration\ResourceFactory;
use Laminas\Config\Writer\PhpArray;
use Laminas\EventManager\SharedEventManager;
use Laminas\ModuleManager\ModuleManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

use function array_diff;
use function copy;
use function glob;
use function is_dir;
use function rmdir;
use function scandir;
use function sprintf;
use function unlink;

class RestServiceResourceTest extends TestCase
{
    /** @var string */
    private $module;
    /** @var MockObject|ModuleManager */
    private $moduleManager;
    /** @var ModulePathSpec */
    private $modules;
    /** @var ResourceFactory */
    private $configFactory;
    /** @var InputFilterModel|MockObject */
    private $filter;
    /** @var DocumentationModel|MockObject */
    private $docs;
    /** @var RestServiceResource */
    private $resource;

    /**
     * Remove a directory even if not empty (recursive delete)
     */
    protected function removeDir(string $dir): bool
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            if (is_dir($path)) {
                $this->removeDir($path);
            } else {
                unlink($path);
            }
        }
        return rmdir($dir);
    }

    protected function cleanUpAssets(): void
    {
        $basePath   = sprintf('%s/TestAsset/module/%s', __DIR__, $this->module);
        $configPath = $basePath . '/config';
        foreach (glob(sprintf('%s/src/%s/V*', $basePath, $this->module)) as $dir) {
            $this->removeDir($dir);
        }
        copy($configPath . '/module.config.php.dist', $configPath . '/module.config.php');
    }

    public function setUp(): void
    {
        $this->module = 'BarConf';
        $this->cleanUpAssets();

        $modules = [
            'BarConf' => new BarConf\Module(),
        ];

        $moduleEntity = new ModuleEntity($this->module, [], [], false);

        $this->moduleManager = $this->getMockBuilder(ModuleManager::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $writer              = new PhpArray();
        $moduleUtils         = new ModuleUtils($this->moduleManager);
        $this->modules       = new ModulePathSpec($moduleUtils);
        $this->configFactory = new ResourceFactory($moduleUtils, $writer);
        $config              = $this->configFactory->factory('BarConf');

        $restServiceModel = new RestServiceModel($moduleEntity, $this->modules, $config);

        $restServiceModelFactory = $this->getMockBuilder(RestServiceModelFactory::class)
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $restServiceModelFactory
            ->expects($this->any())
            ->method('factory')
            ->with($this->equalTo('BarConf'), $this->equalTo(RestServiceModelFactory::TYPE_DEFAULT))
            ->will($this->returnValue($restServiceModel));

        $this->filter = $this->getMockBuilder(InputFilterModel::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->docs   = $this->getMockBuilder(DocumentationModel::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->resource = new RestServiceResource($restServiceModelFactory, $this->filter, $this->docs);

        $r    = new ReflectionObject($this->resource);
        $prop = $r->getProperty('moduleName');
        $prop->setAccessible(true);
        $prop->setValue($this->resource, 'BarConf');
    }

    public function tearDown(): void
    {
        $this->cleanUpAssets();
    }

    /**
     * @see https://github.com/zfcampus/zf-apigility/issues/18
     */
    public function testCreateReturnsRestServiceEntityWithControllerServiceNamePopulated(): void
    {
        $entity = $this->resource->create(['service_name' => 'test']);
        self::assertInstanceOf(RestServiceEntity::class, $entity);
        $controllerServiceName = $entity->controllerServiceName;
        self::assertNotEmpty($controllerServiceName);
        self::assertStringContainsString('\\Test\\', $controllerServiceName);
    }

    /**
     * @group 166
     */
    public function testPatchOfADbConnectedServiceUpdatesDbConnectedConfiguration(): void
    {
        $moduleManager           = $this->moduleManager;
        $modulePathSpec          = $this->modules;
        $configResourceFactory   = $this->configFactory;
        $moduleModel             = new ModuleModel($moduleManager, [], []);
        $sharedEvents            = new SharedEventManager();
        $restServiceModelFactory = new RestServiceModelFactory(
            $modulePathSpec,
            $configResourceFactory,
            $sharedEvents,
            $moduleModel
        );
        $resource                = new RestServiceResource($restServiceModelFactory, $this->filter, $this->docs);

        $sharedEvents->attach(
            RestServiceModel::class,
            'fetch',
            [DbConnectedRestServiceModel::class, 'onFetch']
        );

        $r    = new ReflectionObject($resource);
        $prop = $r->getProperty('moduleName');
        $prop->setAccessible(true);
        $prop->setValue($resource, 'BarConf');

        $entity = $resource->create([
            'adapter_name' => 'Db\Test',
            'table_name'   => 'test',
        ]);
        self::assertInstanceOf(DbConnectedRestServiceEntity::class, $entity);

        $id         = $entity->controllerServiceName;
        $updateData = [
            'entity_identifier_name' => 'test_id',
            'hydrator_name'          => 'ObjectPropertyHydrator',
        ];
        $resource->patch($id, $updateData);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        self::assertIsArray($config);
        self::assertArrayHasKey('BarConf\\V1\\Rest\\Test\\TestEntity', $config['api-tools-hal']['metadata_map']);
        self::assertArrayHasKey('BarConf\\V1\\Rest\\Test\\TestResource', $config['api-tools']['db-connected']);
        $halConfig = $config['api-tools-hal']['metadata_map']['BarConf\\V1\\Rest\\Test\\TestEntity'];
        $agConfig  = $config['api-tools']['db-connected']['BarConf\\V1\\Rest\\Test\\TestResource'];

        self::assertEquals('test_id', $halConfig['entity_identifier_name']);
        self::assertEquals('test_id', $agConfig['entity_identifier_name']);
        self::assertStringContainsString('ObjectPropertyHydrator', $halConfig['hydrator']);
        self::assertStringContainsString('ObjectPropertyHydrator', $agConfig['hydrator_name']);
    }
}
