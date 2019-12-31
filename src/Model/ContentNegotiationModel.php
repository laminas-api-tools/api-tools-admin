<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Configuration\ConfigResource;

class ContentNegotiationModel
{
    /**
     * @var ConfigResource
     */
    protected $globalConfig;

    /**
     * @param ConfigResource $globalConfig
     */
    public function __construct(ConfigResource $globalConfig)
    {
        $this->globalConfig = $globalConfig;
    }

    /**
     * Create Content Negotiation configuration
     *
     * @param  mixed $name
     * @param  array $contentConfig
     * @return ContentNegotiationEntity
     */
    public function create($name, array $contentConfig)
    {
        $key = 'api-tools-content-negotiation.selectors.' . $name;
        $this->globalConfig->patchKey($key, $contentConfig);
        return new ContentNegotiationEntity($name, $contentConfig);
    }

    /**
     * Update an existing Content Negotiation
     *
     * @param  string $name
     * @param  array $contentConfig
     * @return ContentNegotiationEntity
     */
    public function update($name, array $contentConfig)
    {
        return $this->create($name, $contentConfig);
    }

    /**
     * Remove a Content Negotiation
     *
     * @param  string $name
     * @return true
     */
    public function remove($name)
    {
        $key = 'api-tools-content-negotiation.selectors.' . $name;
        $this->globalConfig->deleteKey($key);
        return true;
    }

    /**
     * Retrieve all content negotiations
     *
     * @return array
     */
    public function fetchAll()
    {
        $config = [];
        $fromConfigFile = $this->globalConfig->fetch(true);
        if (isset($fromConfigFile['api-tools-content-negotiation']['selectors'])
            && is_array($fromConfigFile['api-tools-content-negotiation']['selectors'])
        ) {
            $config = $fromConfigFile['api-tools-content-negotiation']['selectors'];
        }

        $negotiations = [];
        foreach ($config as $name => $contentConfig) {
            $negotiations[] = new ContentNegotiationEntity($name, $contentConfig);
        }

        return $negotiations;
    }

    /**
     * Fetch configuration details for a named adapter
     *
     * @param  string $name
     * @return ContentNegotiationEntity|false
     */
    public function fetch($name)
    {
        $config = $this->globalConfig->fetch(true);
        if (! isset($config['api-tools-content-negotiation']['selectors'][$name])
            || ! is_array($config['api-tools-content-negotiation']['selectors'][$name])
        ) {
            return false;
        }

        return new ContentNegotiationEntity($name, $config['api-tools-content-negotiation']['selectors'][$name]);
    }
}
