<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin;

use Interop\Container\ContainerInterface;
use Laminas\ModuleManager\ModuleManagerInterface;
use Laminas\Mvc\MvcEvent;
use Traversable;

use function defined;
use function function_exists;
use function ini_set;
use function is_writable;
use function preg_match;

class Module
{
    /** @var MvcEvent */
    protected $mvcEvent;

    /** @var callable */
    protected $urlHelper;

    /** @var ContainerInterface */
    protected $sm;

    /**
     * Initialize module.
     *
     * If the admin UI module is not loaded yet, load it.
     *
     * Disable the opcache as well.
     *
     * @return void
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
     * @return array|Traversable
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Listen to the bootstrap event
     *
     * @return void
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
     * @return array<string, callable>
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
     *
     * @return void
     */
    protected function disableOpCache()
    {
        if (
            isset($_SERVER['SERVER_SOFTWARE'])
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
        }
    }
}
