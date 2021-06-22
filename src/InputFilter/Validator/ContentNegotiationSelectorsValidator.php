<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\InputFilter\Validator;

use Laminas\Validator\AbstractValidator as LaminasAbstractValidator;
use Laminas\View\Model\ModelInterface;

use function class_exists;
use function class_implements;
use function get_class;
use function gettype;
use function in_array;
use function is_array;
use function is_object;
use function strpos;

class ContentNegotiationSelectorsValidator extends LaminasAbstractValidator
{
    public const INVALID_VALUE       = 'invalidValue';
    public const CLASS_NOT_FOUND     = 'classNotFound';
    public const INVALID_VIEW_MODEL  = 'invalidViewModel';
    public const INVALID_MEDIA_TYPES = 'invalidMediaTypes';
    public const INVALID_MEDIA_TYPE  = 'invalidMediaType';

    /** @var array<string, string> */
    protected $messageTemplates = [
        self::INVALID_VALUE   => 'Value must be an array; received %value%',
        self::CLASS_NOT_FOUND => 'Class name (%value%) does not exist',
        self::INVALID_VIEW_MODEL
            => 'Class name (%value%) is invalid; must be a valid Laminas\View\Model\ModelInterface instance',
        self::INVALID_MEDIA_TYPES => 'Values for the media-types must be provided as an indexed array',
        self::INVALID_MEDIA_TYPE  => 'Invalid media type (%value%) provided',
    ];

    /**
     * Test if a set of selectors is valid
     *
     * @param array $value
     * @return bool
     */
    public function isValid($value)
    {
        $isValid = true;

        if (! is_array($value)) {
            $this->error(
                self::INVALID_VALUE,
                is_object($value) ? get_class($value) : gettype($value)
            );
            return false;
        }

        foreach ($value as $className => $mediaTypes) {
            if (! class_exists($className)) {
                $isValid = false;
                $this->error(self::CLASS_NOT_FOUND, $className);
                continue;
            }

            $interfaces = class_implements($className);
            if (false === $interfaces || ! in_array(ModelInterface::class, $interfaces)) {
                $isValid = false;
                $this->error(self::INVALID_VIEW_MODEL, $className);
                continue;
            }

            if (! is_array($mediaTypes)) {
                $isValid = false;
                $this->error(self::INVALID_MEDIA_TYPES);
                continue;
            }

            foreach ($mediaTypes as $mediaType) {
                if (strpos($mediaType, '/') === false) {
                    $isValid = false;
                    $this->error(self::INVALID_MEDIA_TYPE, $mediaType);
                }
            }
        }

        return $isValid;
    }
}
