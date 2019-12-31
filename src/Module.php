<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin;

use Laminas\ModuleManager\ModuleManagerInterface;
use Laminas\Mvc\MvcEvent;

class Module
{
    /**
     * @var MvcEvent
     */
    protected $mvcEvent;

    /**
     * @var callable
     */
    protected $urlHelper;

    /**
     * @var \Interop\Container\ContainerInterface
     */
    protected $sm;

    /**
     * Initialize module.
     *
     * If the admin UI module is not loaded yet, load it.
     *
     * Disable the opcache as well.
     *
     * @param ModuleManagerInterface $modules
     */
    public function init(ModuleManagerInterface $modules)
    {
        $loaded = $modules->getLoadedModules(false);
        if (! isset($loaded['Laminas\ApiTools\Admin\Ui'])) {
            $modules->loadModule('Laminas\ApiTools\Admin\Ui');
        }

        $this->disableOpCache();
    }

    /**
     * Returns configuration to merge with application configuration
     *
     * @return array|\Traversable
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Listen to the bootstrap event
     *
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        $app      = $e->getApplication();
        $this->sm = $services = $app->getServiceManager();
        $events   = $app->getEventManager();

        $events->attach(
            MvcEvent::EVENT_ROUTE,
            $services->get(Listener\NormalizeMatchedControllerServiceNameListener::class),
            -20
        );
        $events->attach(
            MvcEvent::EVENT_ROUTE,
            $services->get(Listener\NormalizeMatchedInputFilterNameListener::class),
            -20
        );
        $events->attach(
            MvcEvent::EVENT_ROUTE,
            $services->get(Listener\EnableHalRenderCollectionsListener::class),
            -1000
        );
        $events->attach(
            MvcEvent::EVENT_RENDER,
            $services->get(Listener\InjectModuleResourceLinksListener::class),
            100
        );
        $events->attach(
            MvcEvent::EVENT_FINISH,
            $services->get(Listener\DisableHttpCacheListener::class),
            1000
        );
        $this->sm->get(Listener\CryptFilterListener::class)->attach($events);
    }

    /**
     * Run diagnostics
     *
     * @return array|bool
     */
    public function getDiagnostics()
    {
        return [
            'Config File Writable' => function () {
                if (! defined('APPLICATION_PATH')) {
                    return false;
                }
                if (! is_writable(APPLICATION_PATH . '/config/autoload/development.php')) {
                    return false;
                }
                return true;
            },
        ];
    }

    /**
     * Disable opcode caching
     *
     * Disables opcode caching for opcode caches that allow doing so during
     * runtime; the admin API will not work with opcode caching enabled.
     */
    protected function disableOpCache()
    {
        if (isset($_SERVER['SERVER_SOFTWARE'])
            && preg_match('/^PHP .*? Development Server$/', $_SERVER['SERVER_SOFTWARE'])
        ) {
            // skip the built-in PHP webserver (OPcache reset is not needed +
            // it crashes the server in PHP 5.4 with LaminasOptimizer+)
            return;
        }

        // Disable opcode caches that allow runtime disabling

        if (function_exists('xcache_get')) {
            // XCache; just disable it
            ini_set('xcache.cacher', '0');
            return;
        }

        if (function_exists('wincache_ocache_meminfo')) {
            // WinCache; just disable it
            ini_set('wincache.ocenabled', '0');
            return;
        }
    }
}
