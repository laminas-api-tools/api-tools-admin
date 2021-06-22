<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\DoctrineAdapterEntity;
use Laminas\ApiTools\Admin\Model\DoctrineAdapterModel;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\Config\Writer\WriterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function getenv;
use function strrpos;

class DoctrineAdapterModelTest extends TestCase
{
    /** @return MockObject&WriterInterface */
    public function getMockWriter()
    {
        return $this->createMock(WriterInterface::class);
    }

    public function getGlobalConfig(): ConfigResource
    {
        return new ConfigResource([
            'doctrine' => [
                'entitymanager'        => [
                    'orm_default' => [],
                ],
                'documentationmanager' => [
                    'odm_default' => [],
                ],
            ],
        ], 'php://temp', $this->getMockWriter());
    }

    public function getLocalConfig(): ConfigResource
    {
        return new ConfigResource([
            'doctrine' => [
                'connection' => [
                    'orm_default' => [
                        'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                        'params'      => [],
                    ],
                    'odm_default' => [
                        'connectionString' => getenv('TESTS_LAMINAS_API_TOOLS_ADMIN_EXTMONGODB_CONNECTSTRING'),
                        'options'          => [],
                    ],
                    'odm_dbname'  => [
                        'dbname'  => 'test',
                        'options' => [],
                    ],
                ],
            ],
        ], 'php://temp', $this->getMockWriter());
    }

    public function testFetchAllReturnsMixOfOrmAndOdmAdapters(): void
    {
        $model    = new DoctrineAdapterModel($this->getGlobalConfig(), $this->getLocalConfig());
        $adapters = $model->fetchAll();
        self::assertIsArray($adapters);

        foreach ($adapters as $adapter) {
            self::assertInstanceOf(DoctrineAdapterEntity::class, $adapter);
            $data = $adapter->getArrayCopy();
            self::assertArrayHasKey('adapter_name', $data);
            if (strrpos($data['adapter_name'], 'odm_')) {
                self::assertStringContainsString('documentmanager', $data['adapter_name']);
            } elseif (strrpos($data['adapter_name'], 'orm_default')) {
                self::assertStringContainsString('entitymanager', $data['adapter_name']);
            }
        }
    }
}
