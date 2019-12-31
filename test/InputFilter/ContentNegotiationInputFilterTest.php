<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\InputFilter;

use Laminas\ApiTools\Admin\InputFilter\ContentNegotiationInputFilter;
use PHPUnit\Framework\TestCase;

class ContentNegotiationInputFilterTest extends TestCase
{
    public function dataProviderIsValid()
    {
        return [
            'valid' => [['selectors' =>
                [
                    'Laminas\View\Model\ViewModel' => ['text/html', 'application/xhtml+xml'],
                ],
            ]],
        ];
    }

    public function dataProviderIsInvalid()
    {
        return [
            'class-does-not-exist' => [
                ['selectors' => [
                    'Laminas\View\Model\ViewMode' => ['text/html', 'application/xhtml+xml'],
                ]],
                ['selectors' => [
                    'classNotFound' => 'Class name (Laminas\View\Model\ViewMode) does not exist',
                ]],
            ],
            'class-is-not-view-model' => [
                ['selectors' => [
                    __CLASS__ => ['text/html', 'application/xhtml+xml'],
                ]],
                ['selectors' => [
                    'invalidViewModel' => 'Class name (' . __CLASS__ . ') is invalid;'
                    . ' must be a valid Laminas\View\Model\ModelInterface instance',
                ]],
            ],
            'media-types-not-array' => [
                ['selectors' => [
                    'Laminas\View\Model\ViewModel' => 'foo',
                ]],
                ['selectors' => [
                    'invalidMediaTypes' => 'Values for the media-types must be provided as an indexed array',
                ]],
            ],
            'invalid-media-type' => [
                ['selectors' => [
                    'Laminas\View\Model\ViewModel' => ['texthtml', 'application/xhtml+xml'],
                ]],
                ['selectors' => [
                    'invalidMediaType' => 'Invalid media type (texthtml) provided',
                ]],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderIsValid
     */
    public function testIsValid($data)
    {
        $filter = new ContentNegotiationInputFilter;
        $filter->setData($data);
        $this->assertTrue($filter->isValid(), var_export($filter->getMessages(), 1));
    }

    /**
     * @dataProvider dataProviderIsInvalid
     */
    public function testIsInvalid($data, $messages)
    {
        $filter = new ContentNegotiationInputFilter;
        $filter->setData($data);
        $input = $filter->get('selectors');
        $this->assertFalse($filter->isValid());
        $this->assertEquals($messages, $filter->getMessages());
    }
}
