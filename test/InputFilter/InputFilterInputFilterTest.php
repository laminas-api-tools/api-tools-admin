<?php

namespace LaminasTest\ApiTools\Admin\InputFilter;

use Laminas\ApiTools\Admin\InputFilter\InputFilterInputFilter;
use Laminas\InputFilter\Factory;
use PHPUnit_Framework_TestCase as TestCase;

class InputFilterInputFilterTest extends TestCase
{
    public function setup()
    {
        $this->inputFilterInputFilter = new InputFilterInputFilter(new Factory());
    }

    public function dataProviderIsValid()
    {
        //[{"name":"one","required":true,"filters":[{"name":"Laminas\\Filter\\Boolea","options":{"casting":false}}],"validators":[],"allow_empty":false,"continue_if_empty":false}]
        return array(
            array(
                array(
                    array(
                        'name' => 'myfilter',
                        'required' => true,
                        'filters' => array(
                            array(
                                'name' => 'Laminas\Filter\Boolean',
                                'options' => array('casting' => false),
                            )
                        ),
                        'validators' => array(),
                        'allow_empty' => true,
                        'continue_if_empty' => false,
                    )
                )
            )
        );
    }

    public function dataProviderIsInvalid()
    {
        return array(
            array(
                array('foobar' => 'baz'),
                array('inputFilter' => 'Laminas\InputFilter\Factory::createInput expects an array or Traversable; received "string"'),
            ),
            array(
                array(
                    array(
                        'name' => 'myfilter',
                        'required' => true,
                        'filters' => array(
                            array(
                                'name' => 'Laminas\Filter\Bool',
                                'options' => array('casting' => false),
                            )
                        ),
                        'validators' => array(),
                        'allow_empty' => true,
                        'continue_if_empty' => false,
                    )
                ),
                array('inputFilter' => 'Laminas\Filter\FilterPluginManager::get was unable to fetch or create an instance for Laminas\Filter\Bool'),
            ),
        );
    }

    /**
     * @dataProvider dataProviderIsValid
     */
    public function testIsValid($data)
    {
        $this->inputFilterInputFilter->setData($data);
        $this->assertTrue($this->inputFilterInputFilter->isValid());
    }

    /**
     * @dataProvider dataProviderIsInvalid
     */
    public function testIsInvalid($data, $messages)
    {
        $this->inputFilterInputFilter->setData($data);
        $this->assertFalse($this->inputFilterInputFilter->isValid());
        $this->assertEquals($messages, $this->inputFilterInputFilter->getMessages());
    }
}
