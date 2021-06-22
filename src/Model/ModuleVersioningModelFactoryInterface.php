<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

/**
 * Used primarily to provide backwards-compatibility
 */
interface ModuleVersioningModelFactoryInterface
{
    /**
     * factory
     *
     * @param string $module
     * @return ModuleVersioningModel
     */
    public function factory($module);
}
