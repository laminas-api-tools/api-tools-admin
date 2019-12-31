<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\InputFilter;

use Laminas\InputFilter\Factory as InputFilterFactory;
use Laminas\InputFilter\InputFilter;

class InputFilterInputFilter extends InputFilter
{
    /**
     * @var array
     */
    protected $messages = array();

    /**
     * @var InputFilterFactory
     */
    protected $validationFactory;

    /**
     * @param InputFilterFactory $factory
     */
    public function __construct(InputFilterFactory $factory)
    {
        $this->validationFactory = $factory;
    }

    /**
     * Is the data set valid?
     *
     * @return bool
     */
    public function isValid()
    {
        $this->messages = array();
        try {
            $this->validationFactory->createInputFilter($this->data);
            return true;
        } catch (\Exception $e) {
            $this->messages['inputFilter'] = $e->getMessage();
            return false;
        }
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
