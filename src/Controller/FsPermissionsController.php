<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\ContentNegotiation\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;

/**
 * Detect if filesystem permissions will work for the admin api
 */
class FsPermissionsController extends AbstractActionController
{
    /**
     * Path to the root directory
     *
     * @var string
     */
    protected $root;

    /**
     * @var bool
     */
    protected $rootIsWritable;

    public function __construct()
    {
        $this->root = getcwd();
    }

    /**
     * @return ViewModel
     */
    public function fsPermissionsAction()
    {
        $isWritable = $this->configIsWritable() && $this->moduleIsWritable();
        $viewModel = new ViewModel([
            'fs_perms' => $isWritable,
            'www_user' => getenv('USER') ?: '',
        ]);
        return $viewModel;
    }

    /**
     * Is the application root writable?
     *
     * @return bool
     */
    protected function rootIsWritable()
    {
        if (null !== $this->rootIsWritable) {
            return $this->rootIsWritable;
        }

        $this->rootIsWritable = is_writable($this->root);
        return $this->rootIsWritable;
    }

    /**
     * Are the config and config/autoload directories writable?
     *
     * @return bool
     */
    protected function configIsWritable()
    {
        $dir = $this->root . '/config';
        if (! file_exists($dir)) {
            return $this->rootIsWritable();
        }
        if (! is_writable($dir)) {
            return false;
        }

        $dir .= '/autoload';
        return is_writable($dir);
    }

    /**
     * Is the module directory writable?
     *
     * @return bool
     */
    protected function moduleIsWritable()
    {
        $dir = $this->root . '/module';
        if (! file_exists($dir)) {
            return $this->rootIsWritable();
        }

        return is_writable($dir);
    }
}
