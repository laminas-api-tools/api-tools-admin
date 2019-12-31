<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Configuration\ResourceFactory as ConfigResourceFactory;

/**
 * Class VersioningModelFactory
 *
 * @deprecated since 1.5; use \Laminas\ApiTools\Admin\Model\ModuleVersioningModelFactory instead
 */
class VersioningModelFactory implements ModuleVersioningModelFactoryInterface
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
    protected $models = [];

    /**
     * @var ModulePathSpec
     */
    protected $moduleUtils;

    /**
     * @param ConfigResourceFactory $configFactory
     * @param ModulePathSpec $moduleUtils
     * @deprecated
     */
    public function __construct(ConfigResourceFactory $configFactory, ModulePathSpec $moduleUtils)
    {
        $this->configFactory = $configFactory;
        $this->moduleUtils   = $moduleUtils;
    }

    /**
     * @param  string $module
     * @return VersioningModel
     * @deprecated
     */
    public function factory($module)
    {
        if (isset($this->models[$module])) {
            return $this->models[$module];
        }

        $moduleName = $this->moduleUtils->normalizeModuleName($module);
        $config     = $this->configFactory->factory($moduleName);
        $docsConfig = $this->getDocsConfig($module);

        $this->models[$module] = new VersioningModel(
            $config,
            $docsConfig,
            $this->moduleUtils
        );

        return $this->models[$module];
    }

    /**
     * @param  string $name
     * @return string
     * @deprecated
     */
    protected function normalizeModuleName($name)
    {
        return $this->moduleUtils->normalizeModuleName($name);
    }

    /**
     * getDocsConfig
     * @param $module
     * @return null|\Laminas\ApiTools\Configuration\ConfigResource
     * @deprecated
     */
    protected function getDocsConfig($module)
    {
        $moduleConfigPath = $this->moduleUtils->getModuleConfigPath($module);
        $docConfigPath    = dirname($moduleConfigPath) . '/documentation.config.php';
        if (! file_exists($docConfigPath)) {
            return null;
        }
        $documentation = include $docConfigPath;
        return $this->configFactory->createConfigResource($documentation, $docConfigPath);
    }
}
