<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Configuration\ResourceFactory;

/**
 * Class ModuleVersioningModelFactory
 * @author Gabriel Somoza <gabriel@somoza.me>
 */
class ModuleVersioningModelFactory implements ModuleVersioningModelFactoryInterface
{
    /** @var ResourceFactory */
    private $configFactory;

    /** @var ModulePathSpec */
    private $moduleUtils;

    /** @var ModuleVersioningModel[] */
    private $models = [];

    /**
     * @param ResourceFactory $configFactory
     * @param ModulePathSpec $moduleUtils
     */
    public function __construct(ResourceFactory $configFactory, ModulePathSpec $moduleUtils)
    {
        $this->configFactory = $configFactory;
        $this->moduleUtils   = $moduleUtils;
    }

    /**
     * Create service
     *
     * @param string $module
     *
     * @return ModuleVersioningModel
     */
    public function factory($module)
    {
        $moduleName = $this->moduleUtils->normalizeModuleName($module);

        if (! isset($this->models[$moduleName])) {
            $config     = $this->configFactory->factory($moduleName);
            $docsConfig = $this->getDocsConfig($moduleName);

            $this->models[$moduleName] = ModuleVersioningModel::createWithPathSpec(
                $moduleName,
                $this->moduleUtils,
                $config,
                $docsConfig
            );
        }

        return $this->models[$moduleName];
    }

    /**
     * getDocsConfig
     * @param $module
     * @return null|\Laminas\ApiTools\Configuration\ConfigResource
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
