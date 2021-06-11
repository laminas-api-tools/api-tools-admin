<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\InputFilter\RestService;

use Laminas\ApiTools\Admin\InputFilter\RestService\PostInputFilter;
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
    public function dataProviderIsValid(): array
    {
        return [
            'code-connected' => [['service_name' => 'Foo']],
            'db-connected'   => [['adapter_name' => 'Status', 'table_name' => 'foo']],
        ];
    }

    /**
     * @psalm-return array<string, array{
     *     0: array<string, string>,
     *     1: string[]
     * }>
     */
    public function dataProviderIsInvalid(): array
    {
        return [
            // no values
            'empty' => [
                [],
                ['service_name'],
            ],
            // invalid service_name
            'invalid-service-name' => [
                ['service_name' => '_'],
                ['service_name'],
            ],
            // adapter without table
            'valid-adapter-missing-table' => [
                ['adapter_name' => 'Foo'],
                ['table_name'],
            ],
            // table without adapter
            'missing-adapter-valid-table' => [
                ['table_name' => 'Foo'],
                ['adapter_name'],
            ],
            // both present
            'conflict' => [
                ['service_name' => 'Foo', 'adapter_name' => 'bar'],
                ['service_name'],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderIsValid
     * @param array<string, mixed> $data
     */
    public function testIsValidTrue(array $data): void
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        self::assertTrue($filter->isValid());
    }

    /**
     * @dataProvider dataProviderIsInvalid
     * @param array<string, mixed> $data
     * @param string[] $expectedValidationKeys
     */
    public function testIsValidFalse(array $data, array $expectedValidationKeys): void
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        self::assertFalse($filter->isValid());

        $messages = $filter->getMessages();
        $messages = array_keys($messages);
        sort($expectedValidationKeys);
        sort($messages);
        self::assertEquals($expectedValidationKeys, $messages);
    }
}
