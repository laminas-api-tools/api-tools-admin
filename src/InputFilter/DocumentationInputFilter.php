<?php

namespace Laminas\ApiTools\Admin\InputFilter;

use Laminas\InputFilter\InputFilter;

class DocumentationInputFilter extends InputFilter
{
    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @var array
     */
    protected $validHttpMethodDocumentationKeys = [
        'identifier',
        'description',
        'request',
        'response',
    ];

    /**
     * @var array
     */
    protected $validHttpMethods = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
    ];

    /**
     * Is the data set valid?
     *
     * @param  mixed|null $context
     * @return bool
     */
    public function isValid($context = null)
    {
        $this->messages = [];
        $isValid = true;

        if (! is_array($this->data)) {
            $this->messages['general']['invalidData'] = 'Documentation payload must be an array';
            return false;
        }

        foreach ($this->data as $key => $data) {
            if (in_array($key, $this->validHttpMethods)) {
                // valid HTTP method?
                if (isset($this->data['collection']) || isset($this->data['entity'])) {
                    $this->messages[$key][] = 'HTTP methods cannot be present when "collection" or "entity"'
                        . ' is also present; please verify data for "' . $key . '"';
                    $isValid = false;
                    continue;
                }

                if (! $this->isValidHttpMethodDocumentation($data)) {
                    $isValid = false;
                    continue;
                }

                continue;
            }

            if (in_array($key, ['collection', 'entity'])) {
                // valid collection or entity
                if (! is_array($data)) {
                    $this->messages[$key][] = 'Collections and entities methods must be an array of HTTP methods;'
                        . ' received invalid entry for "' . $key . '"';
                    $isValid = false;
                    continue;
                }

                foreach ($data as $subKey => $subData) {
                    if (in_array($subKey, $this->validHttpMethods)) {
                        if (! $this->isValidHttpMethodDocumentation($subData)) {
                            $isValid = false;
                            continue;
                        }
                    } elseif ($subKey === 'description') {
                        if (! is_string($subData)) {
                            $this->messages[$key][] = 'Description must be provided as a string;'
                                . ' please verify description for "' . $subKey . '"';
                            $isValid = false;
                            continue;
                        }
                    } else {
                        $this->messages[$key][] = 'Key must be description or an HTTP indexed list;'
                            . ' please verify documentation for "' . $subKey . '"';
                        $isValid = false;
                        continue;
                    }
                }

                continue;
            }

            if ($key === 'description') {
                // documentation checking
                if (! is_string($data)) {
                    $this->messages[$key][] = 'Description must be provided as a string;'
                        . ' please verify description for "' . $key . '"';
                    $isValid = false;
                    continue;
                }

                continue;
            }

            // everything else is invalid
            $this->messages[$key][] = 'An invalid key was encountered in the top position for "' . $key . '"; '
                . 'must be one of an HTTP method, collection, entity, or description';
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * @param  array $data
     * @return bool
     */
    public function isValidHttpMethodDocumentation($data)
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->validHttpMethodDocumentationKeys)) {
                if ($value !== null && ! is_string($value)) {
                    $this->messages[$key][] = 'Documentable elements must be strings; please verify "' . $key . '"';
                    return false;
                }
                continue;
            }

            $this->messages[$key][] = 'Documentable elements must be any or all of description,'
                . ' request or response; please verify "' . $key . '"';
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
