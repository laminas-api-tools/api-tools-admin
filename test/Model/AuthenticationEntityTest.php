<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\AuthenticationEntity;
use LaminasTest\ApiTools\Admin\DeprecatedAssertionsTrait;
use PHPUnit\Framework\TestCase;

class AuthenticationEntityTest extends TestCase
{
    use DeprecatedAssertionsTrait;

    public function testIsBasicByDefault(): void
    {
        $entity = new AuthenticationEntity();
        self::assertTrue($entity->isBasic());
        self::assertFalse($entity->isDigest());
        self::assertFalse($entity->isOAuth2());
    }

    public function testRealmHasADefaultValue(): void
    {
        $entity = new AuthenticationEntity();
        self::assertAttributeEquals('api', 'realm', $entity);
    }

    public function testCanSpecifyTypeDuringInstantiation(): void
    {
        $entity = new AuthenticationEntity(AuthenticationEntity::TYPE_DIGEST);
        self::assertFalse($entity->isBasic());
        self::assertTrue($entity->isDigest());
        self::assertFalse($entity->isOAuth2());
    }

    public function testCanSpecifyOauth2TypeDuringInstantiation(): void
    {
        $entity = new AuthenticationEntity(AuthenticationEntity::TYPE_OAUTH2);
        self::assertFalse($entity->isBasic());
        self::assertFalse($entity->isDigest());
        self::assertTrue($entity->isOAuth2());
    }

    public function testCanSpecifyRealmDuringInstantiation(): void
    {
        $entity = new AuthenticationEntity(AuthenticationEntity::TYPE_BASIC, 'laminascon');
        self::assertAttributeEquals('laminascon', 'realm', $entity);
    }

    public function testCanSetBasicParametersDuringInstantiation(): void
    {
        $entity = new AuthenticationEntity(AuthenticationEntity::TYPE_BASIC, 'laminascon', [
            'htpasswd' => __DIR__ . '/htpasswd',
            'htdigest' => __DIR__ . '/htdigest',
        ]);
        self::assertAttributeEquals(__DIR__ . '/htpasswd', 'htpasswd', $entity);
        self::assertAttributeEmpty('htdigest', $entity);
    }

    public function testCanSetDigestParametersDuringInstantiation(): void
    {
        $entity = new AuthenticationEntity(AuthenticationEntity::TYPE_DIGEST, 'laminascon', [
            'htpasswd'       => __DIR__ . '/htpasswd',
            'htdigest'       => __DIR__ . '/htdigest',
            'nonce_timeout'  => 3600,
            'digest_domains' => '/api',
        ]);
        self::assertAttributeEmpty('htpasswd', $entity);
        self::assertAttributeEquals(__DIR__ . '/htdigest', 'htdigest', $entity);
        self::assertAttributeEquals(3600, 'nonceTimeout', $entity);
        self::assertAttributeEquals('/api', 'digestDomains', $entity);
    }

    public function testCanSetOAuth2ParametersDuringInstantiation(): void
    {
        $entity = new AuthenticationEntity(AuthenticationEntity::TYPE_OAUTH2, [
            'dsn'         => 'sqlite::memory:',
            'username'    => 'me',
            'password'    => 'too',
            'route_match' => '/api/oauth',
        ]);
        self::assertAttributeEmpty('htpasswd', $entity);
        self::assertAttributeEmpty('htdigest', $entity);
        self::assertAttributeEmpty('realm', $entity);
        self::assertAttributeEquals('sqlite::memory:', 'dsn', $entity);
        self::assertAttributeEquals('me', 'username', $entity);
        self::assertAttributeEquals('too', 'password', $entity);
        self::assertAttributeEquals('/api/oauth', 'routeMatch', $entity);
    }

    public function testSerializationOfBasicAuthReturnsOnlyKeysSpecificToType(): void
    {
        $entity = new AuthenticationEntity(AuthenticationEntity::TYPE_BASIC, 'laminascon', [
            'htpasswd' => __DIR__ . '/htpasswd',
            'htdigest' => __DIR__ . '/htdigest',
        ]);
        self::assertEquals([
            'type'           => 'http_basic',
            'accept_schemes' => ['basic'],
            'realm'          => 'laminascon',
            'htpasswd'       => __DIR__ . '/htpasswd',
        ], $entity->getArrayCopy());
    }

    public function testSerializationOfDigestAuthReturnsOnlyKeysSpecificToType(): void
    {
        $entity = new AuthenticationEntity(AuthenticationEntity::TYPE_DIGEST, 'laminascon', [
            'htpasswd'       => __DIR__ . '/htpasswd',
            'htdigest'       => __DIR__ . '/htdigest',
            'nonce_timeout'  => 3600,
            'digest_domains' => '/api',
        ]);
        self::assertEquals([
            'type'           => 'http_digest',
            'accept_schemes' => ['digest'],
            'realm'          => 'laminascon',
            'htdigest'       => __DIR__ . '/htdigest',
            'nonce_timeout'  => 3600,
            'digest_domains' => '/api',
        ], $entity->getArrayCopy());
    }

    public function testSerializationOfOauth2AuthReturnsOnlyKeysSpecificToType(): void
    {
        $entity = new AuthenticationEntity(AuthenticationEntity::TYPE_OAUTH2, [
            'dsn'         => 'sqlite::memory:',
            'username'    => 'me',
            'password'    => 'too',
            'route_match' => '/api/oauth',
        ]);
        self::assertEquals([
            'type'        => 'oauth2',
            'dsn_type'    => 'PDO',
            'dsn'         => 'sqlite::memory:',
            'username'    => 'me',
            'password'    => 'too',
            'route_match' => '/api/oauth',
        ], $entity->getArrayCopy());
    }
}
