<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Exception;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\Filter\FilterChain;
use ReflectionClass;
use ReflectionException;

use function class_exists;
use function dirname;
use function file_exists;
use function implode;
use function is_dir;
use function sprintf;
use function str_replace;
use function strlen;
use function substr;

use const DIRECTORY_SEPARATOR;

/**
 * @deprecated use \Laminas\ApiTools\Admin\Model\ModuleVersioningModel instead
 */
class VersioningModel
{
    /** @var ConfigResource */
    protected $configResource;

    /** @var null|ConfigResource */
    protected $docsConfigResource;

    /** @var null|FilterChain */
    protected $moduleNameFilter;

    /** @var null|ModulePathSpec */
    private $pathSpec;

    /**
     * @deprecated
     */
    public function __construct(
        ConfigResource $config,
        ?ConfigResource $docsConfig = null,
        ?ModulePathSpec $pathSpec = null
    ) {
        $this->configResource     = $config;
        $this->docsConfigResource = $docsConfig;
        $this->pathSpec           = $pathSpec;
    }

    /**
     * getModuleVersioningModel
     *
     * @param string      $name
     * @param null|string $srcPath Do not use this parameter unless you're providing for a transition to the new class
     *                          (see deprecation notice on this class)
     * @return ModuleVersioningModel
     * @throws ReflectionException
     */
    private function getModuleVersioningModel($name, $srcPath = null)
    {
        $name        = $this->normalizeModule($name);
        $hasPathSpec = null !== $this->pathSpec;

        if ($hasPathSpec) {
            $pathSpecType = $this->pathSpec->getPathSpec();
            if (! $srcPath) {
                $srcPath = $this->pathSpec->getModuleSourcePath($name);
            }
            $configDirPath = $this->pathSpec->getModuleConfigPath($name);
        } else {
            $pathSpecType  = ModulePathSpec::PSR_0;
            $srcPath       = $this->getModuleSourcePath($name);
            $configDirPath = $this->locateConfigPath($srcPath);
        }

        return new ModuleVersioningModel(
            $name,
            $configDirPath,
            $srcPath,
            $this->configResource,
            $this->docsConfigResource,
            $pathSpecType
        );
    }

    /**
     * Create a new version for a module
     *
     * @deprecated
     *
     * @param string $module
     * @param int $version
     * @param bool|string $path
     * @return bool
     * @throws ReflectionException
     */
    public function createVersion($module, $version, $path = false)
    {
        return $this->getModuleVersioningModel($module, $path)
            ->createVersion($version);
    }

    /**
     * Get the versions of a module
     *
     * @deprecated
     *
     * @param string $module
     * @param bool|string $path
     * @return array|bool
     * @throws ReflectionException
     */
    public function getModuleVersions($module, $path = false)
    {
        return $this->getModuleVersioningModel($module, $path)
            ->getModuleVersions();
    }

    /**
     * Updates the default version of a module that will be used if no version is
     * specified by the API consumer.
     *
     * @deprecated
     *
     * @param  int $defaultVersion
     * @return bool
     */
    public function setDefaultVersion($defaultVersion)
    {
        // here we don't care about module name or path because the operation doesn't need it
        return (new ModuleVersioningModel('', __DIR__, __DIR__, $this->configResource))
            ->setDefaultVersion($defaultVersion);
    }

    /**
     * Normalize a module name
     *
     * Module names come over the wire dot-separated; make them namespaced.
     *
     * @deprecated
     *
     * @param  string $module
     * @return string
     */
    protected function normalizeModule($module)
    {
        if ($this->pathSpec) {
            return $this->pathSpec->normalizeModuleName($module);
        }

        return str_replace(['.', '/'], '\\', $module);
    }

    /**
     * Determine the source path for the module
     *
     * Usually, this is the "src/{module-name}" subdirectory of the module.
     *
     * @deprecated
     *
     * @param string $module
     * @param bool $appendNamespace If true, it will append the module's namespace to the path - for PSR0 compatibility
     * @return string
     * @throws ReflectionException
     */
    protected function getModuleSourcePath($module, $appendNamespace = true)
    {
        // for clients that know how to instantiate this class with a ModulePathSpec
        if (null !== $this->pathSpec) {
            $path = $this->pathSpec->getModuleSourcePath($module);
            if ($this->pathSpec->getPathSpec() === 'psr-0') {
                $path .= DIRECTORY_SEPARATOR . $module;
            }
            return $path;
        }

        // .. or fall back to the old method, which only supports PSR-0
        $moduleClass = sprintf('%s\\Module', $module);

        if (! class_exists($moduleClass)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The module %s doesn\'t exist',
                $module
            ));
        }

        $r       = new ReflectionClass($moduleClass);
        $srcPath = dirname($r->getFileName());
        if (file_exists($srcPath . '/src') && is_dir($srcPath . '/src')) {
            $parts = [$srcPath, 'src'];
            if ($appendNamespace) {
                $parts[] = str_replace('\\', '/', $moduleClass);
            }
            $srcPath = implode(DIRECTORY_SEPARATOR, $parts);
        } else {
            if (! $appendNamespace && substr($srcPath, - strlen($module)) === $module) {
                $srcPath = substr($srcPath, 0, strlen($srcPath) - strlen($module) - 1);
            }
        }

        if (! file_exists($srcPath) && ! is_dir($srcPath)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The module "%s" has a malformed directory structure; cannot determine source path',
                $module
            ));
        }

        return $srcPath;
    }

    /**
     * Locate the config path for this module
     *
     * @deprecated
     *
     * @param  string $srcPath
     * @return string|false
     */
    protected function locateConfigPath($srcPath)
    {
        $config = sprintf('%s/config', $srcPath);
        if (file_exists($config) && is_dir($config)) {
            return $config;
        }

        if ($srcPath === '.' || $srcPath === '/') {
            return false;
        }

        return $this->locateConfigPath(dirname($srcPath));
    }

    /**
     * Filter for module names
     *
     * @deprecated
     *
     * @return FilterChain
     */
    protected function getModuleNameFilter()
    {
        if ($this->moduleNameFilter instanceof FilterChain) {
            return $this->moduleNameFilter;
        }

        $this->moduleNameFilter = new FilterChain();
        $this->moduleNameFilter->attachByName('Word\CamelCaseToDash')
            ->attachByName('StringToLower');
        return $this->moduleNameFilter;
    }
}
