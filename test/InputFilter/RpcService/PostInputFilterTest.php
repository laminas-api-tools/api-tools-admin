<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\InputFilter\RpcService;

use Laminas\ApiTools\Admin\InputFilter\RpcService\PostInputFilter;
use Laminas\InputFilter\Factory;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function sort;

class PostInputFilterTest extends TestCase
{
    public function getInputFilter(): PostInputFilter
    {
        $factory = new Factory();
        return $factory->createInputFilter([
            'type' => PostInputFilter::class,
        ]);
    }

    /** @psalm-return array<string, array{0: array<string, string>}> */
    public function dataProviderIsValid()
    {
        return [
            'singular-service-name' => [
                ['service_name' => 'Foo', 'route_match' => '/bar'],
            ],
            'compound-service-name' => [
                ['service_name' => 'Foo_Bar', 'route_match' => '/bar'],
            ],
        ];
    }

    /** @psalm-return array<string, array{0: array<string, string>, 1: string[]}> */
    public function dataProviderIsInvalid()
    {
        return [
            'empty'                   => [
                [],
                ['service_name', 'route_match'],
            ],
            'missing-service-name'    => [
                ['route_match' => '/bar'],
                ['service_name'],
            ],
            'missing-route-match'     => [
                ['service_name' => 'Foo_Bar'],
                ['route_match'],
            ],
            'namespaced-service-name' => [
                ['service_name' => 'Foo\Bar', 'route_match' => '/bar'],
                ['service_name'],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderIsValid
     * @param array<string, mixed> $data
     */
    public function testIsValid(array $data): void
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        self::assertTrue($filter->isValid());
    }

    /**
     * @dataProvider dataProviderIsInvalid
     * @param array<string, mixed> $data
     * @param string[] $expectedMessageKeys
     */
    public function testIsInvalid(array $data, array $expectedMessageKeys): void
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        self::assertFalse($filter->isValid());

        $messages = $filter->getMessages();
        $messages = array_keys($messages);
        sort($expectedMessageKeys);
        sort($messages);
        self::assertEquals($expectedMessageKeys, $messages);
    }
}
