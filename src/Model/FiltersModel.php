<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Exception;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;

class FiltersModel extends AbstractPluginManagerModel
{
    /**
     * @var array
     */
    protected $metadata;

    /**
     * $pluginManager should be an instance of
     * Laminas\Filter\FilterPluginManager.
     *
     * @param ServiceManager $pluginManager
     * @param array $metadata
     */
    public function __construct(ServiceManager $pluginManager, array $metadata = [])
    {
        if (! $pluginManager instanceof FilterPluginManager) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an instance of Laminas\Filter\FilterPluginManager; received "%s"',
                __CLASS__,
                get_class($pluginManager)
            ));
        }

        parent::__construct($pluginManager);
        $this->metadata = $metadata;
    }

    /**
     * Retrieve all plugins
     *
     * Merges the list of plugins with the plugin metadata
     *
     * @return array
     */
    protected function getPlugins()
    {
        if (is_array($this->plugins)) {
            return $this->plugins;
        }

        $plugins  = parent::getPlugins();
        $plugins  = array_flip($plugins);
        $metadata = $this->metadata;

        array_walk($plugins, function (& $value, $key) use ($metadata) {
            if (! array_key_exists($key, $metadata)) {
                $value = [];
                return;
            }
            $value = $metadata[$key];
        });

        $this->plugins = $plugins;
        return $this->plugins;
    }
}
