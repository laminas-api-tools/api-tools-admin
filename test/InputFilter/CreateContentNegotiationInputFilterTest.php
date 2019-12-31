<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\InputFilter;

use Laminas\ApiTools\Admin\InputFilter\CreateContentNegotiationInputFilter;
use PHPUnit_Framework_TestCase as TestCase;

class CreateContentNegotiationInputFilterTest extends TestCase
{
    public function dataProviderIsValid()
    {
        return [
            'content-name-only' => [
                [
                    'content_name' => 'test',
                ],
            ],
            'content-name-and-selectors' => [
                [
                    'content_name' => 'test',
                    'selectors' => [
                        'Laminas\View\Model\ViewModel' => ['text/html', 'application/xhtml+xml'],
                    ],
                ],
            ],
        ];
    }

    public function dataProviderIsInvalid()
    {
        return [
            'missing-content-name' => [
                [
                    'selectors' => [
                        'Laminas\View\Model\ViewModel' => ['text/html', 'application/xhtml+xml'],
                    ],
                ],
                [
                    'content_name' => [
                        'isEmpty' => 'Value is required and can\'t be empty'
                    ],
                ],
            ],
            'null-content-name' => [
                [
                    'content_name' => null,
                    'selectors' => [
                        'Laminas\View\Model\ViewModel' => ['text/html', 'application/xhtml+xml'],
                    ],
                ],
                ['content_name' => [
                    'isEmpty' => 'Value is required and can\'t be empty',
                ]],
            ],
            'bool-content-name' => [
                [
                    'content_name' => true,
                    'selectors' => [
                        'Laminas\View\Model\ViewModel' => ['text/html', 'application/xhtml+xml'],
                    ],
                ],
                ['content_name' => [
                    'invalidType' => 'Value must be a string; received boolean',
                ]],
            ],
            'int-content-name' => [
                [
                    'content_name' => 1,
                    'selectors' => [
                        'Laminas\View\Model\ViewModel' => ['text/html', 'application/xhtml+xml'],
                    ]
                ],
                ['content_name' => [
                    'invalidType' => 'Value must be a string; received integer',
                ]],
            ],
            'float-content-name' => [
                [
                    'content_name' => 1.1,
                    'selectors' => [
                        'Laminas\View\Model\ViewModel' => ['text/html', 'application/xhtml+xml'],
                    ]
                ],
                ['content_name' => [
                    'invalidType' => 'Value must be a string; received double',
                ]],
            ],
            'array-content-name' => [
                [
                    'content_name' => ['content_name'],
                    'selectors' => [
                        'Laminas\View\Model\ViewModel' => ['text/html', 'application/xhtml+xml'],
                    ]
                ],
                ['content_name' => [
                    'invalidType' => 'Value must be a string; received array',
                ]],
            ],
            'object-content-name' => [
                [
                    'content_name' => (object) ['content_name'],
                    'selectors' => [
                        'Laminas\View\Model\ViewModel' => ['text/html', 'application/xhtml+xml'],
                    ],
                ],
                ['content_name' => [
                    'invalidType' => 'Value must be a string; received stdClass',
                ]],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderIsValid
     */
    public function testIsValid($data)
    {
        $filter = new CreateContentNegotiationInputFilter;
        $filter->setData($data);
        $this->assertTrue($filter->isValid(), var_export($filter->getMessages(), 1));
    }

    /**
     * @dataProvider dataProviderIsInvalid
     */
    public function testIsInvalid($data, $messages)
    {
        $filter = new CreateContentNegotiationInputFilter;
        $filter->setData($data);
        $this->assertFalse($filter->isValid());
        $this->assertEquals($messages, $filter->getMessages());
    }
}
