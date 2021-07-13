<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin;

use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;

use function date_default_timezone_set;
use function error_reporting;
use function ini_set;

use const E_ALL;

class Bootstrap
{
    /** @var ServiceManager */
    protected static $serviceManager;

    public static function init(): void
    {
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        ini_set('log_errors_max_len', '0');
        error_reporting(E_ALL);
        date_default_timezone_set('UTC');

        include 'vendor/autoload.php';

        $config = [
            'modules'                 => [
                'Laminas\\Filter',
                'Laminas\\Validator',
                'Laminas\\InputFilter',
                'Laminas\\ApiTools\\Admin',
            ],
            'module_listener_options' => [
                'config_glob_paths'        => [
                    './config/autoload/*.php',
                ],
                'module_paths'             => [
                    './module',
                    './vendor',
                ],
                'config_cache_enabled'     => false,
                'module_map_cache_enabled' => false,
                'check_dependencies'       => true,
            ],
        ];

        $serviceManagerConfig = new ServiceManagerConfig();
        $serviceManager       = new ServiceManager();
        $serviceManagerConfig->configureServiceManager($serviceManager);
        $serviceManager->setService('ApplicationConfig', $config);
        $moduleManager = $serviceManager->get('ModuleManager');
        $moduleManager->loadModules();

        static::$serviceManager = $serviceManager;
    }

    /**
     * @return mixed|object|array
     */
    public static function getService(string $name)
    {
        $serviceManager = self::getServiceManager();

        return $serviceManager->get($name);
    }

    public static function getServiceManager(): ServiceManager
    {
        if (! static::$serviceManager) {
            self::init();
        }

        return static::$serviceManager;
    }
}
