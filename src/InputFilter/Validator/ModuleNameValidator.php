<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\InputFilter\Validator;

class ModuleNameValidator extends AbstractValidator
{
    const API_NAME = 'api_name';

    /**
     * @var array
     */
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
