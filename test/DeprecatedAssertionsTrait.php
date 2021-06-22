<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin;

use PHPUnit\Framework\Assert;
use ReflectionProperty;

trait DeprecatedAssertionsTrait
{
    /**
     * @param mixed $expected
     * @param class-string|object $classOrObject
     */
    public static function assertAttributeEquals(
        $expected,
        string $property,
        $classOrObject,
        string $message = ''
    ): void {
        $r = new ReflectionProperty($classOrObject, $property);
        $r->setAccessible(true);

        Assert::assertEquals($expected, $r->getValue($classOrObject), $message);
    }

    /** @param class-string|object $classOrObject */
    public static function assertAttributeEmpty(
        string $property,
        $classOrObject,
        string $message = ''
    ): void {
        $r = new ReflectionProperty($classOrObject, $property);
        $r->setAccessible(true);

        Assert::assertEmpty($r->getValue($classOrObject), $message);
    }
}
