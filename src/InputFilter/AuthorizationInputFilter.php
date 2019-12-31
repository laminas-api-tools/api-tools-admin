<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\InputFilter;

use Laminas\InputFilter\InputFilter;

class AuthorizationInputFilter extends InputFilter
{
    protected $messages = [];

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
        foreach ($this->data as $className => $httpMethods) {
            // validate the structure of the controller service name / method
            if (strpos($className, '::') === false) {
                $this->messages[$className][] = 'Class service name is invalid, must be serviceName::method,'
                    . ' serviceName::__collection__, or serviceName::__entity__';
                $isValid = false;
            }

            if (! is_array($httpMethods)) {
                $this->messages[$className][] = 'Values for each controller must be an http method'
                    . ' keyed array of true/false values';
                $isValid = false;
                continue;
            }

            foreach ($httpMethods as $httpMethod => $isRequired) {
                if (! in_array($httpMethod, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
                    $this->messages[$className][] = 'Invalid HTTP method (' . $httpMethod . ') provided.';
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
