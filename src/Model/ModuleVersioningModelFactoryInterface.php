<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

/**
 * Used primarily to provide backwards-compatibility
 *
 * @author Gabriel Somoza <gabriel@somoza.me>
 */
interface ModuleVersioningModelFactoryInterface
{
    /**
     * factory
     *
     * @param string $module
     *
     * @return ModuleVersioningModel
     */
    public function factory($module);
}
