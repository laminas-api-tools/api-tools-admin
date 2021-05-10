<?php

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Rest\Exception\CreationException;

class NewRestServiceEntity extends RestServiceEntity
{
    protected $serviceName;

    public function exchangeArray(array $data)
    {
        parent::exchangeArray($data);
        foreach ($data as $key => $value) {
            $key = strtolower($key);
            $key = str_replace('_', '', $key);
            switch ($key) {
                case 'servicename':
                    $this->serviceName = $value;
                    break;
            }
        }

        if (null === $this->serviceName) {
            throw new CreationException('No service name provided; cannot create RESTful resource', 422);
        }

        if (null === $this->routeIdentifierName) {
            $this->routeIdentifierName = sprintf(
                '%s_id',
                $this->normalizeServiceNameForIdentifier($this->serviceName)
            );
        }

        if (null === $this->routeMatch) {
            $this->routeMatch = sprintf(
                '/%s',
                $this->normalizeServiceNameForRoute($this->serviceName)
            );
        }

        if (null === $this->collectionName) {
            $this->collectionName = $this->normalizeServiceNameForIdentifier($this->serviceName);
        }
    }

    public function getArrayCopy()
    {
        $return = parent::getArrayCopy();
        $return['service_name'] = $this->serviceName;
        return $return;
    }
}
