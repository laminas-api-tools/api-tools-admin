<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Configuration\Exception\InvalidArgumentException as InvalidArgumentConfiguration;
use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ApiTools\Configuration\ResourceFactory as ConfigResourceFactory;

class DocumentationModel
{
    const TYPE_REST = 'rest';
    const TYPE_RPC = 'rpc';

    /**
     * @var ConfigResourceFactory
     */
    protected $configFactory;

    protected $moduleUtils;

    public function __construct(ConfigResourceFactory $configFactory, ModuleUtils $moduleUtils)
    {
        $this->configFactory = $configFactory;
        $this->moduleUtils = $moduleUtils;
    }

    public function getSchemaTemplate($type = self::TYPE_REST)
    {
        switch ($type) {
            case self::TYPE_REST:
                return array(
                    'collection' => array(
                        'description' => null,
                        'GET'    => array('description' => null, 'request' => null, 'response' => null),
                        'POST'   => array('description' => null, 'request' => null, 'response' => null),
                        'PUT'    => array('description' => null, 'request' => null, 'response' => null),
                        'PATCH'  => array('description' => null, 'request' => null, 'response' => null),
                        'DELETE' => array('description' => null, 'request' => null, 'response' => null),
                    ),
                    'entity' => array(
                        'description' => null,
                        'GET'    => array('description' => null, 'request' => null, 'response' => null),
                        'POST'   => array('description' => null, 'request' => null, 'response' => null),
                        'PUT'    => array('description' => null, 'request' => null, 'response' => null),
                        'PATCH'  => array('description' => null, 'request' => null, 'response' => null),
                        'DELETE' => array('description' => null, 'request' => null, 'response' => null),
                    ),
                    'description' => null
                );
            case self::TYPE_RPC:
                return array(
                    'description' => null,
                    'GET'    => array('description' => null, 'request' => null, 'response' => null),
                    'POST'   => array('description' => null, 'request' => null, 'response' => null),
                    'PUT'    => array('description' => null, 'request' => null, 'response' => null),
                    'PATCH'  => array('description' => null, 'request' => null, 'response' => null),
                    'DELETE' => array('description' => null, 'request' => null, 'response' => null),
                );
        }
    }

    public function fetchDocumentation($module, $controllerServiceName)
    {
        $configResource = $this->getDocumentationConfigResource($module);
        $value = $configResource->fetch(true);
        if (isset($value[$controllerServiceName])) {
            return $value[$controllerServiceName];
        }
        return array();
    }

    public function storeDocumentation(
        $module,
        $controllerType,
        $controllerServiceName,
        $documentation,
        $replace = false
    ) {
        $configResource = $this->getDocumentationConfigResource($module);
        $template = array($controllerServiceName => $this->getSchemaTemplate($controllerType));
        $templateFlat = $configResource->traverseArray($template);
        $documentationFlat = $configResource->traverseArray(array($controllerServiceName => $documentation));

        $validDocumentationFlat = array_intersect_key($documentationFlat, $templateFlat);

        if ($replace) {
            $configResource->deleteKey($controllerServiceName);
        }

        $configResource->patch($validDocumentationFlat);
        $fullDoc = $configResource->fetch(true);
        return $fullDoc[$controllerServiceName];
    }

    /**
     * Check if the module exists
     *
     * @param  string $module
     * @return boolean
     */
    public function moduleExists($module)
    {
        try {
            $configModule = $this->configFactory->factory($module);
        } catch (InvalidArgumentConfiguration $e) {
            return false;
        }
        return true;
    }


    /**
     * Check if a module and controller exists
     *
     * @param  string $module
     * @param  string $controller
     * @return boolean
     */
    public function controllerExists($module, $controller)
    {
        try {
            $configModule = $this->configFactory->factory($module);
        } catch (InvalidArgumentConfiguration $e) {
            return false;
        }

        $config = $configModule->fetch(true);

        if (isset($config['api-tools-rest'])
            && array_key_exists($controller, $config['api-tools-rest'])
        ) {
            return true;
        }

        if (isset($config['api-tools-rpc'])
            && array_key_exists($controller, $config['api-tools-rpc'])
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param $module
     * @return \Laminas\ApiTools\Configuration\ConfigResource
     */
    protected function getDocumentationConfigResource($module)
    {
        $moduleConfigPath = $this->moduleUtils->getModuleConfigPath($module);
        $docConfigPath = dirname($moduleConfigPath) . '/documentation.config.php';
        $docArray = (file_exists($docConfigPath)) ? include $docConfigPath : array();
        return $this->configFactory->createConfigResource($docArray, $docConfigPath);
    }
}
