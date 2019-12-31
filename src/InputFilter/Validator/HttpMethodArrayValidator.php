<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\InputFilter\Validator;

class HttpMethodArrayValidator extends AbstractValidator
{
    const HTTP_METHOD_ARRAY = 'httpMethodArray';

    /**
     * @var array
     */
    protected $validHttpMethods = [
        'OPTIONS',
        'GET',
        'POST',
        'PATCH',
        'PUT',
        'DELETE',
    ];

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::HTTP_METHOD_ARRAY => "'%value%' is not http method",
    ];

    /**
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        foreach ($value as $httpMethod) {
            if (! in_array($httpMethod, $this->validHttpMethods)) {
                $this->error(self::HTTP_METHOD_ARRAY, $httpMethod);
                return false;
            }
        }
        return true;
    }
}
