<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\DoctrineAdapterEntity;
use PHPUnit\Framework\TestCase;

use function getenv;

class DoctrineAdapterEntityTest extends TestCase
{
    public function testCanRepresentAnOrmEntity()
    {
        $config     = [
            'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
            'params'      => [],
        ];
        $entity     = new DoctrineAdapterEntity('test', $config);
        $serialized = $entity->getArrayCopy();

        $this->assertArrayHasKey('adapter_name', $serialized);
        $this->assertEquals('doctrine.entitymanager.test', $serialized['adapter_name']);
    }

    public function testCanRepresentAnOdmEntity()
    {
        $config     = [
            'connectionString' => getenv('TESTS_LAMINAS_API_TOOLS_ADMIN_EXTMONGODB_CONNECTSTRING'),
        ];
        $entity     = new DoctrineAdapterEntity('test', $config);
        $serialized = $entity->getArrayCopy();

        $this->assertArrayHasKey('adapter_name', $serialized);
        $this->assertEquals('doctrine.documentmanager.test', $serialized['adapter_name']);
    }
}
