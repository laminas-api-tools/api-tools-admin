<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\InputFilter;

use Exception;
use Laminas\InputFilter\Factory as InputFilterFactory;
use Laminas\InputFilter\InputFilter;

class InputFilterInputFilter extends InputFilter
{
    /** @var array */
    protected $messages = [];

    /** @var InputFilterFactory */
    protected $validationFactory;

    public function __construct(InputFilterFactory $factory)
    {
        $this->validationFactory = $factory;
    }

    /**
     * Is the data set valid?
     *
     * @param  mixed|null $context
     * @return bool
     */
    public function isValid($context = null)
    {
        $this->messages = [];
        try {
            $this->validationFactory->createInputFilter($this->data);
            return true;
        } catch (Exception $e) {
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
