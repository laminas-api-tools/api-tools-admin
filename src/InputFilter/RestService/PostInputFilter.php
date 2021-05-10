<?php

namespace Laminas\ApiTools\Admin\InputFilter\RestService;

use Laminas\ApiTools\Admin\InputFilter\Validator\ServiceNameValidator;
use Laminas\InputFilter\InputFilter;

class PostInputFilter extends InputFilter
{
    /**
     * @var array
     */
    protected $localMessages;

    /**
     * @var bool
     */
    protected $isUpdate = false;

    /**
     * Initialize input filter
     */
    public function init()
    {
        $this->add([
            'name' => 'service_name',
            'required' => false,
            'validators' => [
                ['name' => ServiceNameValidator::class],
            ],
        ]);
        $this->add([
            'name' => 'adapter_name',
            'required' => false,
        ]);
        $this->add([
            'name' => 'table_name',
            'required' => false,
        ]);
    }

    /**
     * Override isValid to provide conditional input checking
     * @return bool
     */
    public function isValid($context = null)
    {
        if (! $this->isValidService()) {
            return false;
        }

        return parent::isValid();
    }

    /**
     * Override getMessages() to ensure our conditional logic messages can be passed upward
     * @return array
     */
    public function getMessages()
    {
        if (is_array($this->localMessages) && $this->localMessages) {
            return $this->localMessages;
        }
        return parent::getMessages();
    }

    /**
     * Is the service valid?
     *
     * Ensures that one of the following is present:
     *
     * - service_name OR
     * - adapter_name AND table_name
     *
     * @return bool
     */
    protected function isValidService()
    {
        $context = $this->getRawValues();

        if (! isset($context['service_name'])
            && ! isset($context['adapter_name'])
            && ! isset($context['table_name'])
        ) {
            $this->localMessages = [
                'service_name' => 'You must provide either a Code-Connected service name'
                    . ' OR a DB-Connected database adapter and table name',
            ];
            return false;
        }

        if ($this->isUpdate) {
            $this->get('service_name')->setRequired(true);
            return true;
        }

        if (isset($context['service_name'])) {
            if (isset($context['adapter_name'])
                || isset($context['table_name'])
            ) {
                $this->localMessages = [
                    'service_name' => 'You must provide either a Code-Connected service name'
                        . ' OR a DB-Connected database adapter and table name',
                ];
                return false;
            }
            return true;
        }

        if (! empty($context['adapter_name'])
            && ! isset($context['table_name'])
        ) {
            $this->localMessages = [
                'table_name' => 'DB-Connected services require both a database adapter and table name',
            ];
            return false;
        }

        if (! isset($context['adapter_name'])
            && ! empty($context['table_name'])
        ) {
            $this->localMessages = [
                'adapter_name' => 'DB-Connected services require both a database adapter and table name',
            ];
            return false;
        }

        return true;
    }
}
