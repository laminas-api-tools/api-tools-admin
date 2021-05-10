<?php

namespace Laminas\ApiTools\Admin\Model;

class ContentNegotiationEntity
{
    public function __construct($name, $config)
    {
        $this->name = $name;
        $this->config = $config;
    }

    /**
     * Retrieve array serialization of entity
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return array_merge(
            ['content_name' => $this->name],
            ['selectors' => $this->config]
        );
    }

    /**
     * Reset state of entity
     *
     * @param  array $array
     */
    public function exchangeArray(array $array)
    {
        $this->config = [];
        foreach ($array as $key => $value) {
            switch (strtolower($key)) {
                case 'content_name':
                    $this->name = $value;
                    break;
                default:
                    $this->config[$key] = $value;
                    break;
            }
        }
    }
}
