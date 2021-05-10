<?php

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
