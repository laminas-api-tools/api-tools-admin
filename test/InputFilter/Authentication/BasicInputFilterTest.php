<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\InputFilter\Authentication;

use Laminas\ApiTools\Admin\InputFilter\Authentication\BasicInputFilter;
use Laminas\InputFilter\Factory;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function sort;
use function sys_get_temp_dir;
use function touch;
use function uniqid;
use function unlink;
use function var_export;

class BasicInputFilterTest extends TestCase
{
    /** @var string */
    private $htpasswd;

    public function setUp(): void
    {
        $this->htpasswd = sys_get_temp_dir() . '/' . uniqid() . '.htpasswd';
        touch($this->htpasswd);
    }

    public function tearDown(): void
    {
        unlink($this->htpasswd);
    }

    public function getInputFilter(): BasicInputFilter
    {
        $factory = new Factory();
        return $factory->createInputFilter([
            'type' => BasicInputFilter::class,
        ]);
    }

    /** @psalm-return array<string, array{0: array<string, string>}> */
    public function dataProviderIsValid(): array
    {
        return [
            'basic-only'       => [
                ['accept_schemes' => ['basic'], 'realm' => 'My Realm'],
            ],
            'basic-and-digest' => [
                ['accept_schemes' => ['digest', 'basic'], 'realm' => 'My Realm'],
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
            'empty'            => [
                [],
                [
                    'accept_schemes',
                    'realm',
                    'htpasswd',
                ],
            ],
            'empty-data'       => [
                ['accept_schemes' => '', 'realm' => '', 'htpasswd' => ''],
                [
                    'accept_schemes',
                    'realm',
                    'htpasswd',
                ],
            ],
            'invalid-htpasswd' => [
                ['accept_schemes' => ['basic'], 'realm' => 'api', 'htpasswd' => '/foo/bar/baz/bat.htpasswd'],
                [
                    'htpasswd',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderIsValid
     * @param array<string, mixed> $data
     */
    public function testIsValid(array $data): void
    {
        $data['htpasswd'] = $this->htpasswd;
        $filter           = $this->getInputFilter();
        $filter->setData($data);
        self::assertTrue($filter->isValid(), var_export($filter->getMessages(), true));
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
