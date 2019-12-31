<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ServiceManager\ServiceManager;

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

        $this->plugins  = [];
        foreach ($this->pluginManager->getRegisteredServices() as $key => $services) {
            $this->plugins += $services;
        }
        sort($this->plugins, SORT_STRING);
        return $this->plugins;
    }
}
