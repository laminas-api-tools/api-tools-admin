<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\InputFilter;

use Laminas\ApiTools\Admin\InputFilter\ContentNegotiationInputFilter;
use PHPUnit_Framework_TestCase as TestCase;

class ContentNegotiationInputFilterTest extends TestCase
{
    public function dataProviderIsValid()
    {
        return array(
            'valid' => array(array('selectors' =>
                array(
                    'Laminas\View\Model\ViewModel' => array('text/html', 'application/xhtml+xml'),
                ),
            )),
        );
    }

    public function dataProviderIsInvalid()
    {
        return array(
            'class-does-not-exist' => array(
                array('selectors' => array(
                    'Laminas\View\Model\ViewMode' => array('text/html', 'application/xhtml+xml'),
                )),
                array('selectors' => array(
                    'classNotFound' => 'Class name (Laminas\View\Model\ViewMode) does not exist',
                )),
            ),
            'class-is-not-view-model' => array(
                array('selectors' => array(
                    __CLASS__ => array('text/html', 'application/xhtml+xml'),
                )),
                array('selectors' => array(
                    'invalidViewModel' => 'Class name (' . __CLASS__ . ') is invalid;'
                    . ' must be a valid Laminas\View\Model\ModelInterface instance',
                )),
            ),
            'media-types-not-array' => array(
                array('selectors' => array(
                    'Laminas\View\Model\ViewModel' => 'foo',
                )),
                array('selectors' => array(
                    'invalidMediaTypes' => 'Values for the media-types must be provided as an indexed array',
                )),
            ),
            'invalid-media-type' => array(
                array('selectors' => array(
                    'Laminas\View\Model\ViewModel' => array('texthtml', 'application/xhtml+xml'),
                )),
                array('selectors' => array(
                    'invalidMediaType' => 'Invalid media type (texthtml) provided',
                )),
            ),
        );
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
