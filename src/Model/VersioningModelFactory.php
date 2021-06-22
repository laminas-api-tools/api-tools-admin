<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ApiTools\Configuration\ResourceFactory as ConfigResourceFactory;

use function dirname;
use function file_exists;

/**
 * @deprecated since 1.5; use \Laminas\ApiTools\Admin\Model\ModuleVersioningModelFactory instead
 */
class VersioningModelFactory implements ModuleVersioningModelFactoryInterface
{
    /** @var ConfigResourceFactory */
    protected $configFactory;

    /**
     * Already created model instances
     *
     * @var array
     */
    protected $models = [];

    /** @var ModulePathSpec */
    protected $moduleUtils;

    /**
     * @deprecated
     */
    public function __construct(ConfigResourceFactory $configFactory, ModulePathSpec $moduleUtils)
    {
        $this->configFactory = $configFactory;
        $this->moduleUtils   = $moduleUtils;
    }

    /**
     * @deprecated
     *
     * @param  string $module
     * @return VersioningModel
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
     * @deprecated
     *
     * @param  string $name
     * @return string
     */
    protected function normalizeModuleName($name)
    {
        return $this->moduleUtils->normalizeModuleName($name);
    }

    /**
     * getDocsConfig
     *
     * @deprecated
     *
     * @param string $module
     * @return null|ConfigResource
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
