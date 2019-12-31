<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Rest\Exception\CreationException;

class NewRestServiceEntity extends RestServiceEntity
{
    protected $resourceName;

    public function exchangeArray(array $data)
    {
        parent::exchangeArray($data);
        foreach ($data as $key => $value) {
            $key = strtolower($key);
            $key = str_replace('_', '', $key);
            switch ($key) {
                case 'resourcename':
                    $this->resourceName = $value;
                    break;
            }
        }

        if (null === $this->resourceName) {
            throw new CreationException('No resource name provided; cannot create RESTful resource', 422);
        }

        if (null === $this->identifierName) {
            $this->identifierName = sprintf(
                '%s_id',
                $this->normalizeResourceNameForIdentifier($this->resourceName)
            );
        }

        if (null === $this->routeMatch) {
            $this->routeMatch = sprintf(
                '/%s',
                $this->normalizeResourceNameForRoute($this->resourceName)
            );
        }

        if (null === $this->collectionName) {
            $this->collectionName = $this->normalizeResourceNameForIdentifier($this->resourceName);
        }
    }

    public function getArrayCopy()
    {
        $return = parent::getArrayCopy();
        $return['resource_name'] = $this->resourceName;
        return $return;
    }
}
