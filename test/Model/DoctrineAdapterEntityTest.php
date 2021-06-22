<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\DoctrineAdapterEntity;
use PHPUnit\Framework\TestCase;

use function getenv;

class DoctrineAdapterEntityTest extends TestCase
{
    public function testCanRepresentAnOrmEntity(): void
    {
        $config     = [
            'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
            'params'      => [],
        ];
        $entity     = new DoctrineAdapterEntity('test', $config);
        $serialized = $entity->getArrayCopy();

        self::assertArrayHasKey('adapter_name', $serialized);
        self::assertEquals('doctrine.entitymanager.test', $serialized['adapter_name']);
    }

    public function testCanRepresentAnOdmEntity(): void
    {
        $config     = [
            'connectionString' => getenv('TESTS_LAMINAS_API_TOOLS_ADMIN_EXTMONGODB_CONNECTSTRING'),
        ];
        $entity     = new DoctrineAdapterEntity('test', $config);
        $serialized = $entity->getArrayCopy();

        self::assertArrayHasKey('adapter_name', $serialized);
        self::assertEquals('doctrine.documentmanager.test', $serialized['adapter_name']);
    }
}
