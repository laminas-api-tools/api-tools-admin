<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Exception;
use Laminas\EventManager\EventManager;
use ReflectionClass;

class RestServiceModelFactory extends RpcServiceModelFactory
{
    const TYPE_DEFAULT      = RestServiceModel::class;
    const TYPE_DB_CONNECTED = DbConnectedRestServiceModel::class;

    /**
     * @param string $module
     * @param string $type
     * @return RestServiceModel
     * @throws Exception\InvalidArgumentException
     */
    public function factory($module, $type = self::TYPE_DEFAULT)
    {
        if (isset($this->models[$type][$module])) {
            return $this->models[$type][$module];
        }

        $moduleName   = $this->modules->normalizeModuleName($module);
        $config       = $this->configFactory->factory($module);
        $moduleEntity = $this->moduleModel->getModule($moduleName);

        $restModel = new RestServiceModel($moduleEntity, $this->modules, $config);
        $restModel->setEventManager($this->createEventManager());

        switch ($type) {
            case self::TYPE_DEFAULT:
                $this->models[$type][$module] = $restModel;
                return $restModel;
            case self::TYPE_DB_CONNECTED:
                $model = new $type($restModel);
                $this->models[$type][$module] = $model;
                return $model;
            default:
                throw new Exception\InvalidArgumentException(sprintf(
                    'Model of type "%s" does not exist or cannot be handled by this factory',
                    $type
                ));
        }
    }

    /**
     * Create and return an EventManager composing the shared event manager instance.
     *
     * @return EventManager
     */
    private function createEventManager()
    {
        $r = new ReflectionClass(EventManager::class);

        if ($r->hasMethod('setSharedManager')) {
            // laminas-eventmanager v2 initialization
            $eventManager = new EventManager();
            $eventManager->setSharedManager($this->sharedEventManager);
            return $eventManager;
        }

        // laminas-eventmanager v3 initialization
        return new EventManager($this->sharedEventManager);
    }
}
