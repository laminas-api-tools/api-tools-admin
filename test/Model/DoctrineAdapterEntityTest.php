<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\DoctrineAdapterEntity;
use PHPUnit\Framework\TestCase;

class DoctrineAdapterEntityTest extends TestCase
{
    public function testCanRepresentAnOrmEntity()
    {
        $config = [
            'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
            'params' => [],
        ];
        $entity = new DoctrineAdapterEntity('test', $config);
        $serialized = $entity->getArrayCopy();

        $this->assertArrayHasKey('adapter_name', $serialized);
        $this->assertEquals('doctrine.entitymanager.test', $serialized['adapter_name']);
    }

    public function testCanRepresentAnOdmEntity()
    {
        $config = [
            'connectionString' => 'mongodb://localhost:27017',
        ];
        $entity = new DoctrineAdapterEntity('test', $config);
        $serialized = $entity->getArrayCopy();

        $this->assertArrayHasKey('adapter_name', $serialized);
        $this->assertEquals('doctrine.documentmanager.test', $serialized['adapter_name']);
    }
}
