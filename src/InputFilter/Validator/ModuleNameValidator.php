<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\InputFilter\Validator;

use function explode;

class ModuleNameValidator extends AbstractValidator
{
    public const API_NAME = 'api_name';

    /** @var array */
    protected $messageTemplates = [
        self::API_NAME => "'%value%' is not a valid api name",
    ];

    /**
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        $this->setValue($value);

        // validate each namespace independently
        $parts = explode('\\', $value);

        foreach ($parts as $part) {
            if (! $this->isValidWordInPhp($part)) {
                $this->error(self::API_NAME);
                return false;
            }
        }

        return true;
    }
}
