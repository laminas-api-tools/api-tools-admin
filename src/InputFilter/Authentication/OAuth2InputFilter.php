<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\InputFilter\Authentication;

use Laminas\InputFilter\InputFilter;
use Traversable;

use function is_object;
use function is_string;
use function iterator_to_array;

/**
 * @todo DSN validation
 */
class OAuth2InputFilter extends InputFilter
{
    public function init()
    {
        $this->add([
            'name'              => 'dsn',
            'required'          => false,
            'allow_empty'       => true,
            'continue_if_empty' => true,
            'validators'        => [
                [
                    'name'    => 'Callback',
                    'options' => [
                        'callback' => function ($value, $context) {
                            if (empty($context['dsn_type'])) {
                                // PDO is default DSN type; mark as invalid if none provided
                                return false;
                            }

                            if ($context['dsn_type'] === 'Mongo') {
                                /**
                                 * @todo Mongo DSN validation should move out of model to here
                                 */
                                return true;
                            }

                            if (! is_string($value)) {
                                return false;
                            }

                            if (empty($value)) {
                                return false;
                            }

                        /**
                         * @todo PDO DSN validation should move out of model to here
                         */
                            return true;
                        },
                    ],
                ],
            ],
            'error_message'     => 'Please provide a valid DSN (value will vary based on'
                . ' whether you are selecting Mongo or PDO for the DSN type)',
        ]);
        $this->add([
            'name'              => 'database',
            'required'          => false,
            'allow_empty'       => true,
            'continue_if_empty' => true,
            'validators'        => [
                [
                    'name'    => 'Callback',
                    'options' => [
                        'callback' => function ($value, $context) {
                            if (! isset($context['dsn_type']) || $context['dsn_type'] !== 'Mongo') {
                                // Database is only relevant to Mongo
                                return true;
                            }

                            if (! is_string($value)) {
                                return false;
                            }

                            if (empty($value)) {
                                return false;
                            }

                            return true;
                        },
                    ],
                ],
            ],
            'error_message'     => 'Please provide a valid Mongo database',
        ]);
        $this->add([
            'name'          => 'dsn_type',
            'validators'    => [
                [
                    'name'    => 'InArray',
                    'options' => [
                        'haystack' => [
                            'PDO',
                            'Mongo',
                        ],
                    ],
                ],
            ],
            'error_message' => 'Indicate whether you are using Mongo or PDO',
        ]);
        $this->add([
            'name'     => 'username',
            'required' => false,
        ]);
        $this->add([
            'name'     => 'password',
            'required' => false,
        ]);
        $this->add([
            'name'          => 'route_match',
            'error_message' => 'Please provide a valid URI path for where OAuth2 will respond',
        ]);
    }

    /**
     * @param null|array|object $context
     * @return bool
     */
    public function isValid($context = null)
    {
        $data = $this->data;
        if (null === $data) {
            return parent::isValid($context);
        }

        if ($data instanceof Traversable) {
            $data = iterator_to_array($data);
        }
        if (is_object($data)) {
            $data = (array) $data;
        }

        if (! isset($data['dsn'])) {
            $data['dsn'] = null;
        }

        if (isset($data['dsn_type']) && 'Mongo' === $data['dsn_type']) {
            if (! isset($data['database'])) {
                $data['database'] = null;
            }
        }
        $this->setData($data);

        return parent::isValid($context);
    }
}
