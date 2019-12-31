<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\ContentNegotiation\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;

class CacheEnabledController extends AbstractActionController
{
    public function cacheEnabledAction()
    {
        $cacheEnabled = false;

        switch (true) {
            case (php_sapi_name() === 'cli-server'):
                // built-in PHP webserver never truly enables opcode caching
                break;
            case (ini_get('opcache.enable')):
                // api-tools-configuration has opcache rules for invalidating the cache built-in
                break;
            case (ini_get('apc.enabled') && extension_loaded('apc')):
                // APC
                $cacheEnabled = true;
                break;
            case (ini_get('laminas_optimizerplus.enable')):
                // Optimizer+
                $cacheEnabled = true;
                break;
            case (ini_get('eaccelerator.enable')):
                // EAccelerator
                $cacheEnabled = true;
                break;
            case (ini_get('xcache.cacher')):
                // XCache
                $cacheEnabled = true;
                break;
            case (ini_get('wincache.ocenabled')):
                // WinCache
                $cacheEnabled = true;
                break;
        }

        $viewModel = new ViewModel(['cache_enabled' => $cacheEnabled]);
        $viewModel->setTerminal(true);
        return $viewModel;
    }
}
