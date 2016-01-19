<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\Validator;

class ModuleNameValidator extends AbstractValidator
{
    const API_NAME = 'api_name';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::API_NAME => "'%value%' is not a valid api name"
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
