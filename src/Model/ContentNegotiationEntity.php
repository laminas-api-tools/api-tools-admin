<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use function array_merge;
use function strtolower;

class ContentNegotiationEntity
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(public string $name, public array $config)
    {
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
     * @param array<string,string> $array
     * @return void
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
