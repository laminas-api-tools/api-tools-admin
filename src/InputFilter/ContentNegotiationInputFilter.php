<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\InputFilter;

use Laminas\InputFilter\InputFilter;

class ContentNegotiationInputFilter extends InputFilter
{
    protected $messages = array();

    /**
     * Is the data set valid?
     *
     * @return bool
     */
    public function isValid()
    {
        $this->messages = array();
        $isValid = true;

        foreach ($this->data as $className => $mediaTypes) {
            if (! class_exists($className)) {
                $this->messages[$className][] = 'Class name (' . $className . ') does not exist';
                $isValid = false;
                continue;
            }

            $interfaces = class_implements($className);
            if (false === $interfaces || ! in_array('Laminas\View\Model\ModelInterface', $interfaces)) {
                $this->messages[$className][] = 'Class name (' . $className . ') is invalid; must be a valid Laminas\View\Model\ModelInterface class';
                $isValid = false;
                continue;
            }

            if (!is_array($mediaTypes)) {
                $this->messages[$className][] = 'Values for the media-types must be provided as an indexed array';
                $isValid = false;
                continue;
            }

            foreach ($mediaTypes as $mediaType) {
                if (strpos($mediaType, '/') === false) {
                    $this->messages[$className][] = 'Invalid media type (' . $mediaType . ') provided';
                    $isValid = false;
                }
            }
        }

        return $isValid;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
