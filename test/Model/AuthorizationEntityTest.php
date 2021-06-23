<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\AuthorizationEntity;
use PHPUnit\Framework\TestCase;

class AuthorizationEntityTest extends TestCase
{
    /** @psalm-return array<string, array<string, bool>> */
    protected function getSeedValuesForEntity(): array
    {
        return [
            'Foo\V1\Rest\Session\Controller::__entity__'     => [
                'GET'    => true,
                'POST'   => true,
                'PATCH'  => true,
                'PUT'    => false,
                'DELETE' => false,
            ],
            'Foo\V1\Rest\Session\Controller::__collection__' => [
                'GET'    => true,
                'POST'   => false,
                'PATCH'  => false,
                'PUT'    => false,
                'DELETE' => false,
            ],
            'Foo\V1\Rpc\Message\Controller::message'         => [
                'GET'    => true,
                'POST'   => true,
                'PATCH'  => false,
                'PUT'    => false,
                'DELETE' => false,
            ],
            'Foo\V1\Rpc\Message\Controller::translate'       => [
                'GET'    => true,
                'POST'   => true,
                'PATCH'  => false,
                'PUT'    => false,
                'DELETE' => false,
            ],
        ];
    }

    public function testEntityIsIterable(): void
    {
        $values = $this->getSeedValuesForEntity();
        $entity = new AuthorizationEntity($values);
        self::assertInstanceOf('Traversable', $entity);
    }

    public function testIteratingEntityReturnsAKeyForEachOfRestEntityAndCollection(): void
    {
        $values = $this->getSeedValuesForEntity();
        $entity = new AuthorizationEntity($values);

        $keys = [];
        foreach ($entity as $key => $value) {
            $keys[] = $key;
        }
        self::assertContains('Foo\V1\Rest\Session\Controller::__entity__', $keys);
        self::assertContains('Foo\V1\Rest\Session\Controller::__collection__', $keys);
    }

    public function testIteratingEntityReturnsAKeyForEachActionOfRpcController(): void
    {
        $values = $this->getSeedValuesForEntity();
        $entity = new AuthorizationEntity($values);

        $keys = [];
        foreach ($entity as $key => $value) {
            $keys[] = $key;
        }
        self::assertContains('Foo\V1\Rpc\Message\Controller::message', $keys);
        self::assertContains('Foo\V1\Rpc\Message\Controller::translate', $keys);
    }

    public function testCanAddARestServiceAtATime(): void
    {
        $entity = new AuthorizationEntity();
        $entity->addRestService('Foo\V1\Rest\Session\Controller', AuthorizationEntity::TYPE_ENTITY, [
            'GET'    => true,
            'POST'   => true,
            'PATCH'  => true,
            'PUT'    => false,
            'DELETE' => false,
        ]);
        $entity->addRestService('Foo\V1\Rest\Session\Controller', AuthorizationEntity::TYPE_COLLECTION, [
            'GET'    => true,
            'POST'   => false,
            'PATCH'  => false,
            'PUT'    => false,
            'DELETE' => false,
        ]);

        $keys = [];
        foreach ($entity as $key => $value) {
            $keys[] = $key;
        }
        self::assertContains('Foo\V1\Rest\Session\Controller::__entity__', $keys);
        self::assertContains('Foo\V1\Rest\Session\Controller::__collection__', $keys);
    }

    public function testCanAddAnRpcServiceAtATime(): void
    {
        $entity = new AuthorizationEntity();
        $entity->addRpcService('Foo\V1\Rpc\Message\Controller', 'message', [
            'GET'    => true,
            'POST'   => true,
            'PATCH'  => false,
            'PUT'    => false,
            'DELETE' => false,
        ]);
        $entity->addRpcService('Foo\V1\Rpc\Message\Controller', 'translate', [
            'GET'    => true,
            'POST'   => true,
            'PATCH'  => false,
            'PUT'    => false,
            'DELETE' => false,
        ]);

        $keys = [];
        foreach ($entity as $key => $value) {
            $keys[] = $key;
        }
        self::assertContains('Foo\V1\Rpc\Message\Controller::message', $keys);
        self::assertContains('Foo\V1\Rpc\Message\Controller::translate', $keys);
    }

    public function testCanRetrieveNamedServices(): void
    {
        $entity = new AuthorizationEntity();
        $entity->addRpcService('Foo\V1\Rpc\Message\Controller', 'message', [
            'GET'    => true,
            'POST'   => true,
            'PATCH'  => false,
            'PUT'    => false,
            'DELETE' => false,
        ]);
        self::assertTrue($entity->has('Foo\V1\Rpc\Message\Controller::message'));
        $privileges = $entity->get('Foo\V1\Rpc\Message\Controller::message');
        self::assertEquals([
            'GET'    => true,
            'POST'   => true,
            'PATCH'  => false,
            'PUT'    => false,
            'DELETE' => false,
        ], $privileges);
    }

    public function testAddingARestServiceWithoutHttpMethodsProvidesDefaults(): void
    {
        $entity = new AuthorizationEntity();
        $entity->addRestService('Foo\V1\Rest\Session\Controller', AuthorizationEntity::TYPE_ENTITY);
        self::assertTrue($entity->has('Foo\V1\Rest\Session\Controller::__entity__'));
        $privileges = $entity->get('Foo\V1\Rest\Session\Controller::__entity__');
        self::assertEquals([
            'GET'    => false,
            'POST'   => false,
            'PATCH'  => false,
            'PUT'    => false,
            'DELETE' => false,
        ], $privileges);
    }

    public function testAddingAnRpcServiceWithoutHttpMethodsProvidesDefaults(): void
    {
        $entity = new AuthorizationEntity();
        $entity->addRpcService('Foo\V1\Rpc\Message\Controller', 'message');
        self::assertTrue($entity->has('Foo\V1\Rpc\Message\Controller::message'));
        $privileges = $entity->get('Foo\V1\Rpc\Message\Controller::message');
        self::assertEquals([
            'GET'    => false,
            'POST'   => false,
            'PATCH'  => false,
            'PUT'    => false,
            'DELETE' => false,
        ], $privileges);
    }
}
