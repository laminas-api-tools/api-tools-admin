<?php

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\DoctrineAdapterModel;
use Laminas\ApiTools\Configuration\ConfigResource;
use PHPUnit\Framework\TestCase;

class DoctrineAdapterModelTest extends TestCase
{
    public function getMockWriter()
    {
        return $this->createMock('Laminas\Config\Writer\WriterInterface');
    }

    public function getGlobalConfig()
    {
        return new ConfigResource([
            'doctrine' => [
                'entitymanager' => [
                    'orm_default' => [
                    ],
                ],
                'documentationmanager' => [
                    'odm_default' => [
                    ],
                ],
            ],
        ], 'php://temp', $this->getMockWriter());
    }

    public function getLocalConfig()
    {
        return new ConfigResource([
            'doctrine' => [
                'connection' => [
                    'orm_default' => [
                        'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                        'params' => [],
                    ],
                    'odm_default' => [
                        'connectionString' => 'mongodb://localhost:27017',
                        'options' => [],
                    ],
                    'odm_dbname' => [
                        'dbname' => 'test',
                        'options' => [],
                    ],
                ],
            ],
        ], 'php://temp', $this->getMockWriter());
    }

    public function testFetchAllReturnsMixOfOrmAndOdmAdapters()
    {
        $model = new DoctrineAdapterModel($this->getGlobalConfig(), $this->getLocalConfig());
        $adapters = $model->fetchAll();
        $this->assertInternalType('array', $adapters);

        foreach ($adapters as $adapter) {
            $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\DoctrineAdapterEntity', $adapter);
            $data = $adapter->getArrayCopy();
            $this->assertArrayHasKey('adapter_name', $data);
            if (strrpos($data['adapter_name'], 'odm_')) {
                $this->assertContains('documentmanager', $data['adapter_name']);
            } elseif (strrpos($data['adapter_name'], 'orm_default')) {
                $this->assertContains('entitymanager', $data['adapter_name']);
            }
        }
    }
}
