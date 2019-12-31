<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\ServiceManager;
use ReflectionClass;

class AbstractPluginManagerModel
{
    /**
     * @var array
     */
    protected $plugins;

    /**
     * @var ServiceManager
     */
    protected $pluginManager;

    /**
     * $pluginManager should typically be an instance of
     * Laminas\ServiceManager\AbstractPluginManager.
     *
     * @param ServiceManager $pluginManager
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
        $reflClass = new ReflectionClass($this->pluginManager);
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
     * @param ReflectionClass $r
     * @param AbstractPluginManager $pluginManager
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

        switch ($type) {
            case 'resolvedAliases':
                // fall-through
            case 'aliases':
                return array_filter(array_values($rProp->getValue($pluginManager)), [$this, 'filterPluginName']);
            case 'invokableClasses':
                // fall-through
            case 'factories':
                // fall-through
            default:
                return array_filter(array_keys($rProp->getValue($pluginManager)), [$this, 'filterPluginName']);
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
