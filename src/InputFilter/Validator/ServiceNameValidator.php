<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\InputFilter\Validator;

class ServiceNameValidator extends AbstractValidator
{
    public const SERVICE_NAME = 'serviceName';

    /** @var array */
    protected $messageTemplates = [
        self::SERVICE_NAME => "'%value%' is not a valid service name",
    ];

    /**
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        $this->setValue($value);

        if (! $this->isValidWordInPhp($value)) {
            $this->error(self::SERVICE_NAME);
            return false;
        }

        return true;
    }
}
