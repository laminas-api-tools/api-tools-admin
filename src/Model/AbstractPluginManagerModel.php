<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\ServiceManager;
use ReflectionClass;

use function array_filter;
use function array_keys;
use function array_merge;
use function array_unique;
use function array_values;
use function is_array;
use function preg_match;
use function sort;

use const SORT_STRING;

class AbstractPluginManagerModel
{
    /** @var array */
    protected $plugins;

    /** @var ServiceManager */
    protected $pluginManager;

    /**
     * $pluginManager should typically be an instance of
     * Laminas\ServiceManager\AbstractPluginManager.
     */
    public function __construct(ServiceManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    /**
     * @return array
     */
    public function fetchAll()
    {
        return $this->getPlugins();
    }

    /**
     * Lazy loads plugins from attached plugin manager and sorts them by name
     *
     * @return array
     */
    protected function getPlugins()
    {
        if (is_array($this->plugins)) {
            return $this->plugins;
        }

        // Add invokableClasses via reflection
        $reflClass     = new ReflectionClass($this->pluginManager);
        $this->plugins = array_unique(array_merge(
            $this->getPluginNamesByTypeViaReflection('aliases', $reflClass, $this->pluginManager),
            $this->getPluginNamesByTypeViaReflection('invokableClasses', $reflClass, $this->pluginManager),
            $this->getPluginNamesByTypeViaReflection('factories', $reflClass, $this->pluginManager)
        ));

        sort($this->plugins, SORT_STRING);
        return $this->plugins;
    }

    /**
     * Retrieve registered plugin names by type of retrieval.
     *
     * @param string $type 'aliases', 'invokableClasses', 'factories'
     * @return array
     */
    private function getPluginNamesByTypeViaReflection($type, ReflectionClass $r, AbstractPluginManager $pluginManager)
    {
        if ($type === 'aliases') {
            $type = $r->hasProperty('resolvedAliases') ? 'resolvedAliases' : $type;
        }

        if (! $r->hasProperty($type)) {
            return [];
        }
        $rProp = $r->getProperty($type);
        $rProp->setAccessible(true);

        $filterFn = function ($v): bool {
            return $this->filterPluginName($v);
        };

        switch ($type) {
            case 'resolvedAliases':
                // fall-through
            case 'aliases':
                return array_filter(array_values($rProp->getValue($pluginManager)), $filterFn);
            case 'invokableClasses':
                // fall-through
            case 'factories':
                // fall-through
            default:
                return array_filter(array_keys($rProp->getValue($pluginManager)), $filterFn);
        }
    }

    /**
     * Filter plugin name
     *
     * @param string $name
     * @return bool Returns false for normalized v2 plugin names only.
     */
    private function filterPluginName($name)
    {
        return ! preg_match('/^(laminas|zend)(filter|hydrator|i18n|stdlib|validator)/', $name);
    }
}
