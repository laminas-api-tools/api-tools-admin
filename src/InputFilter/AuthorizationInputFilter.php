<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\InputFilter;

use Laminas\InputFilter\InputFilter;

use function array_keys;
use function in_array;
use function is_array;
use function strpos;

class AuthorizationInputFilter extends InputFilter
{
    /** @var array<string, string[]> */
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
        $isValid        = true;
        foreach ($this->data as $className => $httpMethods) {
            // validate the structure of the controller service name / method
            if (strpos($className, '::') === false) {
                $this->messages[$className][] = 'Class service name is invalid, must be serviceName::method,'
                    . ' serviceName::__collection__, or serviceName::__entity__';
                $isValid                      = false;
            }

            if (! is_array($httpMethods)) {
                $this->messages[$className][] = 'Values for each controller must be an http method'
                    . ' keyed array of true/false values';
                $isValid                      = false;
                continue;
            }

            foreach (array_keys($httpMethods) as $httpMethod) {
                if (! in_array($httpMethod, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                    $this->messages[$className][] = 'Invalid HTTP method (' . $httpMethod . ') provided.';
                    $isValid                      = false;
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
