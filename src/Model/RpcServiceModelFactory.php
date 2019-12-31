<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ApiTools\Configuration\ResourceFactory as ConfigResourceFactory;
use Laminas\EventManager\SharedEventManagerInterface;

class RpcServiceModelFactory
{
    /**
     * @var ConfigResourceFactory
     */
    protected $configFactory;

    /**
     * Already created model instances
     *
     * @var array
     */
    protected $models = array();

    /**
     * @var ModuleModel
     */
    protected $moduleModel;

    /**
     * @var ModuleUtils
     */
    protected $modules;

    /**
     * @var SharedEventManagerInterface
     */
    protected $sharedEventManager;

    /**
     * @param  ModuleUtils $modules
     * @param  ConfigResource $config
     * @param  SharedEventManagerInterface $sharedEvents
     * @param  ModuleModel $moduleModel
     */
    public function __construct(
        ModuleUtils $modules,
        ConfigResourceFactory $configFactory,
        SharedEventManagerInterface $sharedEvents,
        ModuleModel $moduleModel
    ) {
        $this->modules            = $modules;
        $this->configFactory      = $configFactory;
        $this->sharedEventManager = $sharedEvents;
        $this->moduleModel        = $moduleModel;
    }

    /**
     * @param  string $module
     * @return RpcServiceModel
     */
    public function factory($module)
    {
        if (isset($this->models[$module])) {
            return $this->models[$module];
        }

        $moduleName   = $this->normalizeModuleName($module);
        $moduleEntity = $this->moduleModel->getModule($moduleName);
        $config       = $this->configFactory->factory($module);

        $this->models[$module] = new RpcServiceModel($moduleEntity, $this->modules, $config);

        return $this->models[$module];
    }

    /**
     * @param  string $name
     * @return string
     */
    protected function normalizeModuleName($name)
    {
        return str_replace('.', '\\', $name);
    }
}
