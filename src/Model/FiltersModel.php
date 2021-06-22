<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Exception;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;

use function array_flip;
use function array_key_exists;
use function array_walk;
use function get_class;
use function is_array;
use function sprintf;

class FiltersModel extends AbstractPluginManagerModel
{
    /** @var array */
    protected $metadata;

    /**
     * $pluginManager should be an instance of
     * Laminas\Filter\FilterPluginManager.
     *
     * @param array $metadata
     */
    public function __construct(ServiceManager $pluginManager, array $metadata = [])
    {
        if (! $pluginManager instanceof FilterPluginManager) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an instance of Laminas\Filter\FilterPluginManager; received "%s"',
                self::class,
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

        array_walk($plugins, function (&$value, $key) use ($metadata) {
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
