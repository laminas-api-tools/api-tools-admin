<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\InputFilter;

use Laminas\ApiTools\Admin\InputFilter\DbAdapterInputFilter;
use Laminas\InputFilter\Factory;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function sort;

class DbAdapterInputFilterTest extends TestCase
{
    public function getInputFilter(): DbAdapterInputFilter
    {
        $factory = new Factory();
        return $factory->createInputFilter([
            'type' => DbAdapterInputFilter::class,
        ]);
    }

    /** @psalm-return array<string, array{0: array<string, string>}> */
    public function dataProviderIsValid(): array
    {
        return [
            'valid' => [
                [
                    'adapter_name' => 'Db\Status',
                    'database'     => '/path/to/foobar',
                    'driver'       => 'pdo_sqlite',
                ],
            ],
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
            'missing-adapter-name' => [
                [
                    'database' => '/path/to/foobar',
                    'driver'   => 'pdo_sqlite',
                ],
                ['adapter_name'],
            ],
            'missing-database'     => [
                [
                    'adapter_name' => 'Db\Status',
                    'driver'       => 'pdo_sqlite',
                ],
                ['database'],
            ],
            'missing-driver'       => [
                [
                    'adapter_name' => 'Db\Status',
                    'database'     => '/path/to/foobar',
                ],
                ['driver'],
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

        $messages    = $filter->getMessages();
        $messageKeys = array_keys($messages);
        sort($expectedMessageKeys);
        sort($messageKeys);
        self::assertEquals($expectedMessageKeys, $messageKeys);
    }
}
