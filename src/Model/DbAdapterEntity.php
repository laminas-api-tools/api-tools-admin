<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use function array_merge;
use function strtolower;

class DbAdapterEntity
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
     * @return array<string, mixed>
     */
    public function getArrayCopy()
    {
        return array_merge([
            'adapter_name' => $this->name,
        ], $this->config);
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
                case 'adapter_name':
                    $this->name = $value;
                    break;
                default:
                    $this->config[$key] = $value;
                    break;
            }
        }
    }
}
