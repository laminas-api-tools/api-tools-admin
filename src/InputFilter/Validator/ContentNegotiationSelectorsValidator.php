<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\InputFilter\Validator;

use Laminas\Validator\AbstractValidator as LaminasAbstractValidator;

class ContentNegotiationSelectorsValidator extends LaminasAbstractValidator
{
    const INVALID_VALUE       = 'invalidValue';
    const CLASS_NOT_FOUND     = 'classNotFound';
    const INVALID_VIEW_MODEL  = 'invalidViewModel';
    const INVALID_MEDIA_TYPES = 'invalidMediaTypes';
    const INVALID_MEDIA_TYPE  = 'invalidMediaType';

    protected $messageTemplates = [
        self::INVALID_VALUE       => 'Value must be an array; received %value%',
        self::CLASS_NOT_FOUND     => 'Class name (%value%) does not exist',
        self::INVALID_VIEW_MODEL  =>
            'Class name (%value%) is invalid; must be a valid Laminas\View\Model\ModelInterface instance',
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
                (is_object($value) ? get_class($value) : gettype($value))
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
            if (false === $interfaces || ! in_array(\Laminas\View\Model\ModelInterface::class, $interfaces)) {
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
