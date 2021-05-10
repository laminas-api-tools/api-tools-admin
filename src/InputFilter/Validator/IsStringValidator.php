<?php

namespace Laminas\ApiTools\Admin\InputFilter\Validator;

use Laminas\Validator\AbstractValidator as LaminasAbstractValidator;

class IsStringValidator extends LaminasAbstractValidator
{
    const INVALID_TYPE = 'invalidType';

    protected $messageTemplates = [
        self::INVALID_TYPE => 'Value must be a string; received %value%',
    ];

    /**
     * Test if a value is a string
     *
     * @param array $value
     * @return bool
     */
    public function isValid($value)
    {
        if (! is_string($value)) {
            $this->error(self::INVALID_TYPE, (is_object($value) ? get_class($value) : gettype($value)));
            return false;
        }

        return true;
    }
}
