<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\InputFilter\Validator;

use Laminas\Validator\AbstractValidator as LaminasAbstractValidator;

use function get_class;
use function gettype;
use function is_object;
use function is_string;

class IsStringValidator extends LaminasAbstractValidator
{
    public const INVALID_TYPE = 'invalidType';

    /** @var array<string, string> */
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
            $this->error(self::INVALID_TYPE, is_object($value) ? get_class($value) : gettype($value));
            return false;
        }

        return true;
    }
}
