<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

/**
 * Retrieve and return all validator option metadata
 */
class ValidatorMetadataModel
{
    /**
     * Default validator options available to all validators
     *
     * @var array
     */
    protected $defaults = [];

    /**
     * Unprocessed validator metadata configuration
     *
     * @var array
     */
    protected $metadataConfig;

    /**
     * Processed validator metadata configuration; merges values from $defaults
     * into each validator.
     *
     * @var array
     */
    protected $processedMetadata;

    /**
     * @param array $metadata
     */
    public function __construct(array $metadata = [])
    {
        if (isset($metadata['__all__'])) {
            $this->defaults = $metadata['__all__'];
            unset($metadata['__all__']);
        }

        $this->metadataConfig = $metadata;
    }

    /**
     * Fetch metadata for a single validator
     *
     * Returns $defaults if the plugin is unknown
     *
     * @param string $plugin
     * @return array
     */
    public function fetch($plugin)
    {
        $this->processMetadata();
        if (! array_key_exists($plugin, $this->processedMetadata)) {
            return $this->defaults;
        }
        return $this->processedMetadata[$plugin];
    }

    /**
     * Fetch all known validator plugins and their metadata
     *
     * @return array
     */
    public function fetchAll()
    {
        $this->processMetadata();
        return $this->processedMetadata;
    }

    /**
     * Process metadata
     *
     * If $defaults is non-empty, the values are merged into the metadata for
     * each validator prior to being returned.
     *
     * @return array
     */
    protected function processMetadata()
    {
        if (is_array($this->processedMetadata)) {
            return $this->processedMetadata;
        }

        $this->processedMetadata = $this->metadataConfig;

        if (empty($this->defaults)) {
            return $this->processedMetadata;
        }

        array_walk($this->processedMetadata, function (& $value, $key, $defaults) {
            $value = array_merge($defaults, $value);
        }, $this->defaults);

        return $this->processedMetadata;
    }
}
