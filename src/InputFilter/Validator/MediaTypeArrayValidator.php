<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\InputFilter\Validator;

class MediaTypeArrayValidator extends AbstractValidator
{
    const MEDIA_TYPE_ARRAY = 'mediaTypeArray';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::MEDIA_TYPE_ARRAY => "'%value%' is not a correctly formatted media type",
    ];

    /**
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        if (is_string($value)) {
            $value = (array) $value;
        }

        foreach ($value as $mediaType) {
            if (! preg_match('#^[a-z-]+/[a-z0-9*_+.-]+#i', $mediaType)) {
                $this->error(self::MEDIA_TYPE_ARRAY, $mediaType);
                return false;
            }
        }

        return true;
    }
}
