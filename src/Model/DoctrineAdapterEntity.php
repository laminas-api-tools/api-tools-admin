<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Laminas\Stdlib\ArraySerializableInterface;

use function array_merge;
use function strtolower;

class DoctrineAdapterEntity implements ArraySerializableInterface
{
    /** @var string */
    protected $name;

    /** @var array<string, mixed> */
    protected $config;

    /**
     * Constructor
     *
     * @param string $name
     * @param array<string, mixed> $config
     */
    public function __construct($name, $config)
    {
        $this->name   = $name;
        $this->config = $config;
    }

    /**
     * Exchange internal values from provided array
     *
     * @param  array<string, mixed> $array
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

    /**
     * Return an array representation of the object
     *
     * @return array<string, mixed>
     */
    public function getArrayCopy()
    {
        $baseKey = isset($this->config['driverClass'])
            ? 'doctrine.entitymanager.'
            : 'doctrine.documentmanager.';

        return array_merge([
            'adapter_name' => $baseKey . $this->name,
        ], $this->config);
    }
}
