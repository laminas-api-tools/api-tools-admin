<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\InputFilter\Validator;

use function in_array;

class HttpMethodArrayValidator extends AbstractValidator
{
    public const HTTP_METHOD_ARRAY = 'httpMethodArray';

    /** @var array */
    protected $validHttpMethods = [
        'OPTIONS',
        'GET',
        'POST',
        'PATCH',
        'PUT',
        'DELETE',
    ];

    /** @var array */
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
