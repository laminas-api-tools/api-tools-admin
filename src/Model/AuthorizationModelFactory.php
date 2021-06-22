<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Configuration\ResourceFactory as ConfigResourceFactory;

class AuthorizationModelFactory
{
    /** @var ConfigResourceFactory */
    protected $configFactory;

    /**
     * Already created model instances
     *
     * @var array
     */
    protected $models = [];

    /** @var ModuleModel */
    protected $moduleModel;

    /** @var ModulePathSpec */
    protected $modules;

    public function __construct(ModulePathSpec $modules, ConfigResourceFactory $configFactory, ModuleModel $moduleModel)
    {
        $this->modules       = $modules;
        $this->configFactory = $configFactory;
        $this->moduleModel   = $moduleModel;
    }

    /**
     * @param  string $module
     * @return AuthorizationModel
     */
    public function factory($module)
    {
        if (isset($this->models[$module])) {
            return $this->models[$module];
        }

        $moduleName   = $this->modules->normalizeModuleName($module);
        $moduleEntity = $this->moduleModel->getModule($moduleName);
        $config       = $this->configFactory->factory($module);

        $this->models[$module] = new AuthorizationModel($moduleEntity, $this->modules, $config);

        return $this->models[$module];
    }

    /**
     * @deprecated
     *
     * @param  string $name
     * @return string
     */
    protected function normalizeModuleName($name)
    {
        return $this->modules->normalizeModuleName($name);
    }
}
