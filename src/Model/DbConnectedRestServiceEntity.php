<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Rest\Exception\CreationException;
use Laminas\Hydrator\ArraySerializableHydrator;

use function sprintf;
use function str_replace;
use function strtolower;

class DbConnectedRestServiceEntity extends RestServiceEntity
{
    /** @var string */
    protected $adapterName;

    /** @var class-string */
    protected $hydratorName = ArraySerializableHydrator::class;

    /** @var string */
    protected $tableName;

    /** @var string */
    protected $tableService;

    /**
     * @param array $data
     * @return void
     */
    public function exchangeArray(array $data)
    {
        parent::exchangeArray($data);

        foreach ($data as $key => $value) {
            $key = strtolower($key);
            $key = str_replace('_', '', $key);
            switch ($key) {
                case 'adaptername':
                    $this->adapterName = $value;
                    break;
                case 'hydratorname':
                    $this->hydratorName = $value;
                    break;
                case 'tablename':
                    $this->tableName = $value;
                    if (! isset($this->serviceName)) {
                        $this->serviceName = $value;
                    }
                    break;
                case 'tableservice':
                    $this->tableService = $value;
                    break;
            }
        }

        if (null === $this->tableName) {
            throw new CreationException('No table name provided; cannot create RESTful resource', 422);
        }

        if (null === $this->adapterName) {
            throw new CreationException('No database adapter name provided; cannot create RESTful resource', 422);
        }

        if (null === $this->entityIdentifierName) {
            $this->entityIdentifierName = 'id';
        }

        if (null === $this->routeIdentifierName) {
            $this->routeIdentifierName = sprintf(
                '%s_id',
                $this->normalizeServiceNameForIdentifier($this->tableName)
            );
        }

        if (null === $this->routeMatch) {
            $this->routeMatch = sprintf(
                '/%s',
                $this->normalizeServiceNameForRoute($this->tableName)
            );
        }

        if (null === $this->collectionName) {
            $this->collectionName = $this->normalizeServiceNameForIdentifier($this->tableName);
        }
    }

    /** @return array<string, mixed> */
    public function getArrayCopy()
    {
        $data                  = parent::getArrayCopy();
        $data['adapter_name']  = $this->adapterName;
        $data['hydrator_name'] = $this->hydratorName;
        $data['table_name']    = $this->tableName;
        $data['table_service'] = $this->tableService;

        if (empty($data['service_name'])) {
            $data['service_name'] = $this->tableName;
        }

        return $data;
    }
}
