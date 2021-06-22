<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\DbAdapterEntity;
use Laminas\ApiTools\Admin\Model\DbAdapterModel;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\Config\Writer\PhpArray as ConfigWriter;
use Laminas\Stdlib\ArrayUtils;
use PHPUnit\Framework\TestCase;

use function array_merge;
use function dirname;
use function file_exists;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function unlink;

class DbAdapterModelTest extends TestCase
{
    /** @var string */
    private $configPath;

    /** @var string */
    private $globalConfigPath;

    /** @var string */
    private $localConfigPath;

    /** @var ConfigWriter */
    private $configWriter;

    public function setUp(): void
    {
        $this->configPath       = sys_get_temp_dir() . '/api-tools-admin/config';
        $this->globalConfigPath = $this->configPath . '/global.php';
        $this->localConfigPath  = $this->configPath . '/local.php';
        $this->removeConfigMocks();
        $this->createConfigMocks();
        $this->configWriter = new ConfigWriter();
    }

    public function tearDown(): void
    {
        $this->removeConfigMocks();
    }

    public function createConfigMocks(): void
    {
        if (! is_dir($this->configPath)) {
            mkdir($this->configPath, 0775, true);
        }

        $contents = "<" . "?php\nreturn array();";
        file_put_contents($this->globalConfigPath, $contents);
        file_put_contents($this->localConfigPath, $contents);
    }

    public function removeConfigMocks(): void
    {
        if (file_exists($this->globalConfigPath)) {
            unlink($this->globalConfigPath);
        }
        if (file_exists($this->localConfigPath)) {
            unlink($this->localConfigPath);
        }
        if (is_dir($this->configPath)) {
            rmdir($this->configPath);
        }
        if (is_dir(dirname($this->configPath))) {
            rmdir(dirname($this->configPath));
        }
    }

    /**
     * @param array<string, mixed> $global
     * @param array<string, mixed> $local
     */
    public function createModelFromConfigArrays(array $global, array $local): DbAdapterModel
    {
        $this->configWriter->toFile($this->globalConfigPath, $global);
        $this->configWriter->toFile($this->localConfigPath, $local);
        $mergedConfig = ArrayUtils::merge($global, $local);
        $globalConfig = new ConfigResource($mergedConfig, $this->globalConfigPath, $this->configWriter);
        $localConfig  = new ConfigResource($mergedConfig, $this->localConfigPath, $this->configWriter);
        return new DbAdapterModel($globalConfig, $localConfig);
    }

    /** @param array<string, mixed> $config */
    public function assertDbConfigExists(string $adapterName, array $config): void
    {
        self::assertArrayHasKey('db', $config);
        self::assertArrayHasKey('adapters', $config['db']);
        self::assertArrayHasKey($adapterName, $config['db']['adapters']);
        self::assertIsArray($config['db']['adapters'][$adapterName]);
    }

    /**
     * @param array<string, mixed> $expected
     * @param array<string, mixed> $config
     */
    public function assertDbConfigEquals(array $expected, string $adapterName, array $config): void
    {
        self::assertDbConfigExists($adapterName, $config);
        $config = $config['db']['adapters'][$adapterName];
        self::assertEquals($expected, $config);
    }

    /**
     * @param array<string, mixed> $expected
     * @param array<string, mixed> $config
     */
    public function assertDbConfigContains(array $expected, string $adapterName, array $config): void
    {
        self::assertDbConfigExists($adapterName, $config);
        $config = $config['db']['adapters'][$adapterName];
        foreach ($expected as $key => $value) {
            self::assertArrayHasKey($key, $config);
            self::assertEquals($value, $config[$key]);
        }
    }

    /**
     * @group 279
     */
    public function testCreatesBothGlobalAndLocalDbConfigWhenNoneExistedPreviously(): void
    {
        $toCreate = [
            'driver'   => 'Pdo_Sqlite',
            'database' => __FILE__,
            'dsn'      => '',
        ];

        $model = $this->createModelFromConfigArrays([], []);
        $model->create('Db\New', $toCreate);

        $global = include $this->globalConfigPath;
        self::assertDbConfigEquals([], 'Db\New', $global);

        $local = include $this->localConfigPath;
        self::assertDbConfigEquals([
            'driver'   => 'Pdo_Sqlite',
            'database' => __FILE__,
        ], 'Db\New', $local);
    }

    public function testCreateDoesNotCreateEmptyDsnEntry(): void
    {
        $toCreate = ['driver' => 'Pdo_Sqlite', 'database' => __FILE__];
        $model    = $this->createModelFromConfigArrays([], []);
        $model->create('Db\New', $toCreate);

        $global = include $this->globalConfigPath;
        self::assertDbConfigEquals([], 'Db\New', $global);

        $local = include $this->localConfigPath;
        self::assertDbConfigEquals($toCreate, 'Db\New', $local);
    }

    public function testCreatesNewEntriesInBothGlobalAndLocalDbConfigWhenConfigExistedPreviously(): void
    {
        $globalSeedConfig = [
            'db' => [
                'adapters' => [
                    'Db\Old' => [],
                ],
            ],
        ];
        $localSeedConfig  = [
            'db' => [
                'adapters' => [
                    'Db\Old' => [
                        'driver'   => 'Pdo_Sqlite',
                        'database' => __FILE__,
                    ],
                ],
            ],
        ];
        $model            = $this->createModelFromConfigArrays($globalSeedConfig, $localSeedConfig);
        $model->create('Db\New', ['driver' => 'Pdo_Sqlite', 'database' => __FILE__]);

        $global = include $this->globalConfigPath;
        self::assertDbConfigEquals([], 'Db\Old', $global);
        self::assertDbConfigEquals([], 'Db\New', $global);

        $local = include $this->localConfigPath;
        self::assertDbConfigEquals($localSeedConfig['db']['adapters']['Db\Old'], 'Db\Old', $local);
        self::assertDbConfigEquals($localSeedConfig['db']['adapters']['Db\Old'], 'Db\New', $local);
    }

    public function testCanRetrieveListOfAllConfiguredAdapters(): void
    {
        $globalSeedConfig = [
            'db' => [
                'adapters' => [
                    'Db\Old'   => [],
                    'Db\New'   => [],
                    'Db\Newer' => [],
                ],
            ],
        ];
        $localSeedConfig  = [
            'db' => [
                'adapters' => [
                    'Db\Old'   => [
                        'driver'   => 'Pdo_Sqlite',
                        'database' => __FILE__,
                    ],
                    'Db\New'   => [
                        'driver'   => 'Pdo_Sqlite',
                        'database' => __FILE__,
                    ],
                    'Db\Newer' => [
                        'driver'   => 'Pdo_Sqlite',
                        'database' => __FILE__,
                    ],
                ],
            ],
        ];
        $model            = $this->createModelFromConfigArrays($globalSeedConfig, $localSeedConfig);
        $adapters         = $model->fetchAll();
        $adapterNames     = [];
        foreach ($adapters as $adapter) {
            self::assertInstanceOf(DbAdapterEntity::class, $adapter);
            $adapter        = $adapter->getArrayCopy();
            $adapterNames[] = $adapter['adapter_name'];
        }
        self::assertEquals([
            'Db\Old',
            'Db\New',
            'Db\Newer',
        ], $adapterNames);
    }

    public function testCanRetrieveIndividualAdapterDetails(): void
    {
        $globalSeedConfig = [
            'db' => [
                'adapters' => [
                    'Db\Old'   => [],
                    'Db\New'   => [],
                    'Db\Newer' => [],
                ],
            ],
        ];
        $localSeedConfig  = [
            'db' => [
                'adapters' => [
                    'Db\Old'   => [
                        'driver'   => 'Pdo_Sqlite',
                        'database' => __FILE__,
                    ],
                    'Db\New'   => [
                        'driver'   => 'Pdo_Sqlite',
                        'database' => __FILE__,
                    ],
                    'Db\Newer' => [
                        'driver'   => 'Pdo_Sqlite',
                        'database' => __FILE__,
                    ],
                ],
            ],
        ];
        $model            = $this->createModelFromConfigArrays($globalSeedConfig, $localSeedConfig);
        $adapter          = $model->fetch('Db\New');
        self::assertInstanceOf(DbAdapterEntity::class, $adapter);
        $adapter = $adapter->getArrayCopy();
        self::assertEquals('Db\New', $adapter['adapter_name']);
        unset($adapter['adapter_name']);
        self::assertEquals($localSeedConfig['db']['adapters']['Db\New'], $adapter);
    }

    public function testUpdatesLocalDbConfigWhenUpdating(): void
    {
        $toCreate = ['driver' => 'Pdo_Sqlite', 'database' => __FILE__];
        $model    = $this->createModelFromConfigArrays([], []);
        $model->create('Db\New', $toCreate);

        $newConfig = [
            'driver'   => 'Pdo_Mysql',
            'database' => 'laminas_api-tools',
            'username' => 'username',
            'password' => 'password',
        ];
        $entity    = $model->update('Db\New', $newConfig);

        // Ensure the entity returned from the update is what we expect
        self::assertInstanceOf(DbAdapterEntity::class, $entity);
        $entity   = $entity->getArrayCopy();
        $expected = array_merge(['adapter_name' => 'Db\New'], $newConfig);
        self::assertEquals($expected, $entity);

        // Ensure fetching the entity after an update will return what we expect
        $config = include $this->localConfigPath;
        self::assertDbConfigEquals($newConfig, 'Db\New', $config);
    }

    public function testRemoveDeletesConfigurationFromBothLocalAndGlobalConfigFiles(): void
    {
        $toCreate = ['driver' => 'Pdo_Sqlite', 'database' => __FILE__];
        $model    = $this->createModelFromConfigArrays([], []);
        $model->create('Db\New', $toCreate);

        $model->remove('Db\New');
        $global = include $this->globalConfigPath;
        self::assertArrayNotHasKey('Db\New', $global['db']['adapters']);
        $local = include $this->localConfigPath;
        self::assertArrayNotHasKey('Db\New', $local['db']['adapters']);
    }

    /** @psalm-return array<string, array{0: string}> */
    public function postgresDbTypes(): array
    {
        return [
            'pdo'    => ['Pdo_Pgsql'],
            'native' => ['Pgsql'],
        ];
    }

    /**
     * @group 184
     * @dataProvider postgresDbTypes
     */
    public function testCreatingPostgresConfigDoesNotIncludeCharset(string $driver): void
    {
        $toCreate = [
            'driver'   => $driver,
            'database' => 'test',
            'username' => 'test',
            'password' => 'test',
            'charset'  => 'UTF-8',
        ];
        $model    = $this->createModelFromConfigArrays([], []);
        $model->create('Db\New', $toCreate);

        $local = include $this->localConfigPath;

        $expected = $toCreate;
        unset($expected['charset']);

        self::assertDbConfigEquals($expected, 'Db\New', $local);
    }

    /**
     * @group 184
     * @dataProvider postgresDbTypes
     */
    public function testUpdatingPostgresConfigDoesNotAllowCharset(string $driver): void
    {
        $toCreate = [
            'driver'   => $driver,
            'database' => 'test',
            'username' => 'test',
            'password' => 'test',
            'charset'  => 'UTF-8',
        ];
        $model    = $this->createModelFromConfigArrays([], []);
        $model->create('Db\New', $toCreate);

        $newConfig = [
            'driver'   => $driver,
            'database' => 'laminas_api-tools',
            'username' => 'test',
            'password' => 'test',
            'charset'  => 'latin-1',
        ];
        $entity    = $model->update('Db\New', $newConfig);

        // Ensure the entity returned from the update is what we expect
        self::assertInstanceOf(DbAdapterEntity::class, $entity);
        $entity   = $entity->getArrayCopy();
        $expected = array_merge(['adapter_name' => 'Db\New'], $newConfig);
        unset($expected['charset']);

        self::assertEquals($expected, $entity);

        // Ensure fetching the entity after an update will return what we expect
        $config = include $this->localConfigPath;
        unset($expected['adapter_name']);
        self::assertDbConfigEquals($expected, 'Db\New', $config);
    }
}
