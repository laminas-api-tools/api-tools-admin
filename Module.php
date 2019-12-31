<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin;

use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ApiTools\Hal\Entity;
use Laminas\ApiTools\Hal\Link\Link;
use Laminas\ApiTools\Hal\Link\LinkCollection;
use Laminas\ApiTools\Hal\View\HalJsonModel;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Router\RouteMatch;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

class Module
{
    /**
     * @var MvcEvent
     */
    protected $mvcEvent;

    /**
     * @var \Closure
     */
    protected $urlHelper;

    /**
     * @var \Laminas\ServiceManager\ServiceLocatorInterface
     */
    protected $sm;

    public function onBootstrap(MvcEvent $e)
    {
        $app      = $e->getApplication();
        $this->sm = $app->getServiceManager();
        $events   = $app->getEventManager();
        $events->attach(MvcEvent::EVENT_RENDER, array($this, 'onRender'), 100);
    }

    public function getAutoloaderConfig()
    {
        $this->disableOpCache();

        return array(
            'Laminas\ApiTools\Autoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/',
                ),
            ),
        );
    }

    public function getDiagnostics()
    {
        return array(
            'Config File Writable' => function () {
                if (!defined('APPLICATION_PATH')) {
                    return false;
                }
                if (!is_writable(APPLICATION_PATH . '/config/autoload/development.php')) {
                    return false;
                }
                return true;
            },
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array('factories' => array(
            'Laminas\ApiTools\Admin\Model\AuthenticationModel' => function ($services) {
                if (!$services->has('Config')) {
                    throw new ServiceNotCreatedException(
                        'Cannot create Laminas\ApiTools\Admin\Model\AuthenticationModel service because Config service is not present'
                    );
                }
                $config = $services->get('Config');
                $writer = $services->get('Laminas\ApiTools\Configuration\ConfigWriter');

                $global = new ConfigResource($config, 'config/autoload/global.php', $writer);
                $local  = new ConfigResource($config, 'config/autoload/local.php', $writer);
                return new Model\AuthenticationModel($global, $local);
            },
            'Laminas\ApiTools\Admin\Model\AuthorizationModelFactory' => function ($services) {
                if (!$services->has('Laminas\ApiTools\Configuration\ModuleUtils')
                    || !$services->has('Laminas\ApiTools\Configuration\ConfigResourceFactory')
                    || !$services->has('Laminas\ApiTools\Admin\Model\ModuleModel')
                ) {;
                    throw new ServiceNotCreatedException(
                        'Laminas\ApiTools\Admin\Model\AuthorizationModelFactory is missing one or more dependencies from Laminas\ApiTools\Configuration'
                    );
                }
                $moduleModel   = $services->get('Laminas\ApiTools\Admin\Model\ModuleModel');
                $moduleUtils   = $services->get('Laminas\ApiTools\Configuration\ModuleUtils');
                $configFactory = $services->get('Laminas\ApiTools\Configuration\ConfigResourceFactory');

                return new Model\AuthorizationModelFactory($moduleUtils, $configFactory, $moduleModel);
            },
            'Laminas\ApiTools\Admin\Model\ContentNegotiationModel' => function ($services) {
                if (!$services->has('Config')) {
                    throw new ServiceNotCreatedException(
                        'Cannot create Laminas\ApiTools\Admin\Model\ContentNegotiationModel service because Config service is not present'
                    );
                }
                $config = $services->get('Config');
                $writer = $services->get('Laminas\ApiTools\Configuration\ConfigWriter');

                $global = new ConfigResource($config, 'config/autoload/global.php', $writer);
                return new Model\ContentNegotiationModel($global);
            },
            'Laminas\ApiTools\Admin\Model\ContentNegotiationResource' => function ($services) {
                if (!$services->has('Laminas\ApiTools\Admin\Model\ContentNegotiationModel')) {
                    throw new ServiceNotCreatedException(
                        'Cannot create Laminas\ApiTools\Admin\Model\ContentNegotiationResource service because Laminas\ApiTools\Admin\Model\ContentNegotiationModel service is not present'
                    );
                }
                $model = $services->get('Laminas\ApiTools\Admin\Model\ContentNegotiationModel');
                return new Model\ContentNegotiationResource($model);
            },
            'Laminas\ApiTools\Admin\Model\DbAdapterModel' => function ($services) {
                if (!$services->has('Config')) {
                    throw new ServiceNotCreatedException(
                        'Cannot create Laminas\ApiTools\Admin\Model\DbAdapterModel service because Config service is not present'
                    );
                }
                $config = $services->get('Config');
                $writer = $services->get('Laminas\ApiTools\Configuration\ConfigWriter');

                $global = new ConfigResource($config, 'config/autoload/global.php', $writer);
                $local  = new ConfigResource($config, 'config/autoload/local.php', $writer);
                return new Model\DbAdapterModel($global, $local);
            },
            'Laminas\ApiTools\Admin\Model\DbAdapterResource' => function ($services) {
                if (!$services->has('Laminas\ApiTools\Admin\Model\DbAdapterModel')) {
                    throw new ServiceNotCreatedException(
                        'Cannot create Laminas\ApiTools\Admin\Model\DbAdapterResource service because Laminas\ApiTools\Admin\Model\DbAdapterModel service is not present'
                    );
                }
                $model = $services->get('Laminas\ApiTools\Admin\Model\DbAdapterModel');
                return new Model\DbAdapterResource($model);
            },
            'Laminas\ApiTools\Admin\Model\ModuleModel' => function ($services) {
                if (!$services->has('ModuleManager')) {
                    throw new ServiceNotCreatedException(
                        'Cannot create Laminas\ApiTools\Admin\Model\ModuleModel service because ModuleManager service is not present'
                    );
                }
                $modules    = $services->get('ModuleManager');
                $restConfig = array();
                $rpcConfig  = array();
                if ($services->has('Config')) {
                    $config = $services->get('Config');
                    if (isset($config['api-tools-rest'])) {
                        $restConfig = $config['api-tools-rest'];
                    }
                    if (isset($config['api-tools-rpc'])) {
                        $rpcConfig = $config['api-tools-rpc'];
                    }
                }
                return new Model\ModuleModel($modules, $restConfig, $rpcConfig);
            },
            'Laminas\ApiTools\Admin\Model\ModuleResource' => function ($services) {
                $moduleModel = $services->get('Laminas\ApiTools\Admin\Model\ModuleModel');
                $listener    = new Model\ModuleResource($moduleModel);

                if ($services->has('Config')) {
                    $config = $services->get('Config');
                    if (isset($config['api-tools-admin'])) {
                        if (isset($config['api-tools-admin']['module_path'])) {
                            $listener->setModulePath($config['api-tools-admin']['module_path']);
                        }
                    }
                }
                return $listener;
            },
            'Laminas\ApiTools\Admin\Model\RestServiceModelFactory' => function ($services) {
                if (!$services->has('Laminas\ApiTools\Configuration\ModuleUtils')
                    || !$services->has('Laminas\ApiTools\Configuration\ConfigResourceFactory')
                    || !$services->has('Laminas\ApiTools\Admin\Model\ModuleModel')
                    || !$services->has('SharedEventManager')
                ) {
                    throw new ServiceNotCreatedException(
                        'Laminas\ApiTools\Admin\Model\RestServiceModelFactory is missing one or more dependencies from Laminas\ApiTools\Configuration'
                    );
                }
                $moduleModel   = $services->get('Laminas\ApiTools\Admin\Model\ModuleModel');
                $moduleUtils   = $services->get('Laminas\ApiTools\Configuration\ModuleUtils');
                $configFactory = $services->get('Laminas\ApiTools\Configuration\ConfigResourceFactory');
                $sharedEvents  = $services->get('SharedEventManager');

                // Wire DB-Connected fetch listener
                $sharedEvents->attach(__NAMESPACE__ . '\Model\RestServiceModel', 'fetch', 'Laminas\ApiTools\Admin\Model\DbConnectedRestServiceModel::onFetch');

                return new Model\RestServiceModelFactory($moduleUtils, $configFactory, $sharedEvents, $moduleModel);
            },
            'Laminas\ApiTools\Admin\Model\RpcServiceModelFactory' => function ($services) {
                if (!$services->has('Laminas\ApiTools\Configuration\ModuleUtils')
                    || !$services->has('Laminas\ApiTools\Configuration\ConfigResourceFactory')
                    || !$services->has('Laminas\ApiTools\Admin\Model\ModuleModel')
                    || !$services->has('SharedEventManager')
                ) {
                    throw new ServiceNotCreatedException(
                        'Laminas\ApiTools\Admin\Model\RpcServiceModelFactory is missing one or more dependencies from Laminas\ApiTools\Configuration'
                    );
                }
                $moduleModel   = $services->get('Laminas\ApiTools\Admin\Model\ModuleModel');
                $moduleUtils   = $services->get('Laminas\ApiTools\Configuration\ModuleUtils');
                $configFactory = $services->get('Laminas\ApiTools\Configuration\ConfigResourceFactory');
                $sharedEvents  = $services->get('SharedEventManager');
                return new Model\RpcServiceModelFactory($moduleUtils, $configFactory, $sharedEvents, $moduleModel);
            },
            'Laminas\ApiTools\Admin\Model\RestServiceResource' => function ($services) {
                if (!$services->has('Laminas\ApiTools\Admin\Model\RestServiceModelFactory')) {
                    throw new ServiceNotCreatedException(
                        'Laminas\ApiTools\Admin\Model\RestServiceResource is missing one or more dependencies'
                    );
                }
                if (!$services->has('Laminas\ApiTools\Admin\Model\InputFilterModel')) {
                    throw new ServiceNotCreatedException(
                        'Laminas\ApiTools\Admin\Model\RestServiceResource is missing one or more dependencies'
                    );
                }
                $factory = $services->get('Laminas\ApiTools\Admin\Model\RestServiceModelFactory');
                $inputFilterModel = $services->get('Laminas\ApiTools\Admin\Model\InputFilterModel');
                $documentationModel = $services->get('Laminas\ApiTools\Admin\Model\DocumentationModel');
                return new Model\RestServiceResource($factory, $inputFilterModel, $documentationModel);
            },
            'Laminas\ApiTools\Admin\Model\RpcServiceResource' => function ($services) {
                if (!$services->has('Laminas\ApiTools\Admin\Model\RpcServiceModelFactory')) {
                    throw new ServiceNotCreatedException(
                        'Laminas\ApiTools\Admin\Model\RpcServiceResource is missing RpcServiceModelFactory dependency'
                    );
                }
                if (!$services->has('Laminas\ApiTools\Admin\Model\InputFilterModel')) {
                    throw new ServiceNotCreatedException(
                        'Laminas\ApiTools\Admin\Model\RpcServiceResource is missing InputFilterModel dependency'
                    );
                }
                if (!$services->has('ControllerManager')) {
                    throw new ServiceNotCreatedException(
                        'Laminas\ApiTools\Admin\Model\RpcServiceResource is missing ControllerManager dependency'
                    );
                }
                $factory = $services->get('Laminas\ApiTools\Admin\Model\RpcServiceModelFactory');
                $inputFilterModel = $services->get('Laminas\ApiTools\Admin\Model\InputFilterModel');
                $controllerManager = $services->get('ControllerManager');
                $documentationModel = $services->get('Laminas\ApiTools\Admin\Model\DocumentationModel');
                return new Model\RpcServiceResource($factory, $inputFilterModel, $controllerManager, $documentationModel);
            },
            'Laminas\ApiTools\Admin\Model\VersioningModelFactory' => function ($services) {
                if (!$services->has('Laminas\ApiTools\Configuration\ConfigResourceFactory')
                    || !$services->has('Laminas\ApiTools\Configuration\ModuleUtils')
                ) {
                    throw new ServiceNotCreatedException(
                        'Laminas\ApiTools\Admin\Model\VersioningModelFactory is missing one or more dependencies from Laminas\ApiTools\Configuration'
                    );
                }
                $configFactory = $services->get('Laminas\ApiTools\Configuration\ConfigResourceFactory');
                $moduleUtils   = $services->get('Laminas\ApiTools\Configuration\ModuleUtils');
                return new Model\VersioningModelFactory($configFactory, $moduleUtils);
            },
        ));
    }

    public function getControllerConfig()
    {
        return array('factories' => array(
            'Laminas\ApiTools\Admin\Controller\Authentication' => function ($controllers) {
                $services = $controllers->getServiceLocator();
                $model    = $services->get('Laminas\ApiTools\Admin\Model\AuthenticationModel');
                return new Controller\AuthenticationController($model);
            },
            'Laminas\ApiTools\Admin\Controller\Authorization' => function ($controllers) {
                $services = $controllers->getServiceLocator();
                $factory  = $services->get('Laminas\ApiTools\Admin\Model\AuthorizationModelFactory');
                return new Controller\AuthorizationController($factory);
            },
            'Laminas\ApiTools\Admin\Controller\Config' => function ($controllers) {
                $services = $controllers->getServiceLocator();
                return new Controller\ConfigController($services->get('Laminas\ApiTools\Configuration\ConfigResource'));
            },
            'Laminas\ApiTools\Admin\Controller\ModuleConfig' => function ($controllers) {
                $services = $controllers->getServiceLocator();
                return new Controller\ModuleConfigController($services->get('Laminas\ApiTools\Configuration\ConfigResourceFactory'));
            },
            'Laminas\ApiTools\Admin\Controller\ModuleCreation' => function ($controllers) {
                $services = $controllers->getServiceLocator();
                $model    = $services->get('Laminas\ApiTools\Admin\Model\ModuleModel');
                return new Controller\ModuleCreationController($model);
            },
            'Laminas\ApiTools\Admin\Controller\Source' => function ($controllers) {
                $services = $controllers->getServiceLocator();
                $model    = $services->get('Laminas\ApiTools\Admin\Model\ModuleModel');
                return new Controller\SourceController($model);
            },
            'Laminas\ApiTools\Admin\Controller\Versioning' => function ($controllers) {
                $services = $controllers->getServiceLocator();
                $factory  = $services->get('Laminas\ApiTools\Admin\Model\VersioningModelFactory');
                return new Controller\VersioningController($factory);
            },
        ));
    }

    /**
     * Inject links into Module resources for the service services
     *
     * @param  \Laminas\Mvc\MvcEvent $e
     */
    public function onRender($e)
    {
        $matches = $e->getRouteMatch();
        if (!$matches instanceof RouteMatch) {
            // In 404's, we do not have a route match... nor do we need to do
            // anything
            return;
        }

        $result = $e->getResult();
        if (!$result instanceof HalJsonModel) {
            return;
        }

        $viewHelpers = $this->sm->get('ViewHelperManager');
        $halPlugin = $viewHelpers->get('hal');
        $this->initializeUrlHelper();

        if ($result->isEntity()) {
            $this->injectServiceLinks($result->getPayload(), $result, $e);
            $halPlugin->getEventManager()->attach('renderEntity', array($this, 'onRenderEntity'), 10);
            return;
        }

        if ($result->isCollection()) {
            $this->mvcEvent = $e;
            $halPlugin->getEventManager()->attach('renderCollection.entity', array($this, 'onRenderCollectionEntity'), 10);
        }
    }

    protected function initializeUrlHelper()
    {
        $viewHelpers     = $this->sm->get('ViewHelperManager');
        $urlHelper       = $viewHelpers->get('Url');
        $serverUrlHelper = $viewHelpers->get('ServerUrl');
        $this->urlHelper = function ($routeName, $routeParams, $routeOptions, $reUseMatchedParams) use ($urlHelper, $serverUrlHelper) {
            $url = call_user_func($urlHelper, $routeName, $routeParams, $routeOptions, $reUseMatchedParams);
            if (substr($url, 0, 4) == 'http') {
                return $url;
            }
            return call_user_func($serverUrlHelper, $url);
        };
    }

    /**
     * Inject links for the service services of a module
     *
     * @param  Entity $entity
     * @param  HalJsonModel $model
     * @param  \Laminas\Mvc\MvcEvent $model
     */
    protected function injectServiceLinks(Entity $halEntity, HalJsonModel $model, $e)
    {
        $entity = $halEntity->entity;
        $links  = $halEntity->getLinks();
        if ($entity instanceof Model\ModuleEntity) {
            return $this->injectModuleResourceRelationalLinks($entity, $links, $model);
        }
    }

    protected function injectModuleResourceRelationalLinks(Model\ModuleEntity $module, $links, HalJsonModel $model)
    {
        $moduleData = $module->getArrayCopy();
        $moduleName = $moduleData['name'];

        $this->injectLinksForServicesByType('authorization', array(), $links, $moduleName);

        $this->injectLinksForServicesByType('rest', $moduleData['rest'], $links, $moduleName);
        unset($moduleData['rest']);

        $this->injectLinksForServicesByType('rpc', $moduleData['rpc'], $links, $moduleName);
        unset($moduleData['rpc']);

        $module = new Model\ModuleEntity($module->getNamespace(), array(), array(), $module->isVendor());
        $module->exchangeArray($moduleData);
        $replacement = new Entity($module, $moduleName);
        $replacement->setLinks($links);
        $model->setPayload($replacement);
    }

    public function onRenderEntity($e)
    {
        $halEntity = $e->getParam('entity');
        $entity    = $halEntity->entity;

        if ($entity instanceof Model\RestServiceEntity
            || $entity instanceof Model\RpcServiceEntity
            || (is_array($entity) && array_key_exists('controller_service_name', $entity))
        ) {
            $links = $halEntity->getLinks();

            if ($links->has('input_filter')) {
                $serviceName = is_array($entity) ? $entity['controller_service_name'] : $entity->controllerServiceName;

                $link   = $links->get('input_filter');
                $params = $link->getRouteParams();
                $link->setRouteParams(array_merge($params, array(
                    'controller_service_name' => $serviceName
                )));
            }

            if ($links->has('documentation')) {
                $serviceName = is_array($entity) ? $entity['controller_service_name'] : $entity->controllerServiceName;

                $link   = $links->get('documentation');
                $params = $link->getRouteParams();
                $link->setRouteParams(array_merge($params, array(
                    'controller_service_name' => $serviceName
                )));
            }
        }
    }

    /**
     * Inject links into collections
     *
     * Currently:
     * - Inject RPC/REST service links inside module resources that are composed in collections
     *
     * @param  \Laminas\EventManager\Event $e
     */
    public function onRenderCollectionEntity($e)
    {
        $entity = $e->getParam('entity');
        if ($entity instanceof Model\ModuleEntity) {
            return $this->injectModuleCollectionRelationalLinks($entity, $e);
        }

        if ($entity instanceof Model\RestServiceEntity
            || $entity instanceof Model\RpcServiceEntity
        ) {
            return $this->injectServiceCollectionRelationalLinks($entity, $e);
        }
    }

    /**
     * Inject relational links into a Module resource
     *
     * @param Model\ModuleEntity $resource
     * @param \Laminas\Mvc\MvcEvent $e
     */
    public function injectModuleCollectionRelationalLinks(Model\ModuleEntity $resource, $e)
    {
        $asArray  = $resource->getArrayCopy();
        $module   = $asArray['name'];
        $rest     = $asArray['rest'];
        $rpc      = $asArray['rpc'];

        unset($asArray['rest']);
        unset($asArray['rpc']);

        $halEntity   = new Entity($asArray, $module);
        $links       = $halEntity->getLinks();
        $links->add(Link::factory(array(
            'rel' => 'self',
            'route' => array(
                'name' => 'api-tools/api/module',
                'params' => array(
                    'name' => $module,
                ),
            ),
        )));
        $this->injectLinksForServicesByType('authorization', array(), $links, $module);
        $this->injectLinksForServicesByType('rest', $rest, $links, $module);
        $this->injectLinksForServicesByType('rpc', $rpc, $links, $module);

        $e->setParam('entity', $halEntity);
    }

    public function injectServiceCollectionRelationalLinks($entity, $e)
    {
        $module  = $this->mvcEvent->getRouteMatch()->getParam('name');
        $service = $entity->controllerServiceName;
        $type    = $this->getServiceType($service);

        $halEntity = new Entity($entity, $service);
        $links = $halEntity->getLinks();

        // Need to inject the self relational link, as otherwise the HAL plugin
        // sees we have links, and does not inject one.
        $links->add(Link::factory(array(
            'rel' => 'self',
            'route' => array(
                'name' => sprintf('api-tools/api/module/%s-service', $type),
                'params' => array(
                    'name' => $module,
                    'controller_service_name' => $service,
                ),
            ),
        )));

        // Add the input_filter relational link
        $links->add(Link::factory(array(
            'rel' => 'input_filter',
            'route' => array(
                'name' => sprintf('api-tools/api/module/%s-service/input-filter', $type),
                'params' => array(
                    'name' => $module,
                    'controller_service_name' => $service,
                ),
            ),
        )));

        // Add the documentation relational link
        $links->add(Link::factory(array(
            'rel' => 'documentation',
            'route' => array(
                'name' => sprintf('api-tools/api/module/%s-service/doc', $type),
                'params' => array(
                    'name' => $module,
                    'controller_service_name' => $service,
                ),
            ),
        )));

        $e->setParam('entity', $halEntity);
    }

    /**
     * Inject service links
     *
     * @param  string $type "rpc" | "rest" | "authorization"
     * @param  array|\Traversable $services
     * @param  LinkCollection $links
     * @param  null|string $module
     */
    protected function injectLinksForServicesByType($type, $services, LinkCollection $links, $module = null)
    {
        $urlHelper    = $this->urlHelper;

        $linkType     = $type;
        if (in_array($type, array('rpc', 'rest'))) {
            $linkType .= '-service';
        }
        $routeName    = sprintf('api-tools/api/module/%s', $linkType);
        $routeParams  = array();
        $routeOptions = array();
        if (null !== $module) {
            $routeParams['name'] = $module;
        }
        $url  = call_user_func($urlHelper, $routeName, $routeParams, $routeOptions, false);
        $url .= '{?version}';

        $spec = array(
            'rel'   => $type,
            'url'   => $url,
            'props' => array(
                'templated' => true,
            ),
        );

        $link = Link::factory($spec);
        $links->add($link);
    }

    protected function getServiceType($service)
    {
        if (strstr($service, '\\Rest\\')) {
            return 'rest';
        }
        return 'rpc';
    }

    /**
     * Disable opcode caching
     *
     * Disables opcode caching for opcode caches that allow doing so during
     * runtime; the admin API will not work with opcode caching enabled.
     */
    protected function disableOpCache()
    {
        if (isset($_SERVER['SERVER_SOFTWARE'])
            && preg_match('/^PHP .*? Development Server$/', $_SERVER['SERVER_SOFTWARE'])
        ) {
            // skip the built-in PHP webserver (OPcache reset is not needed +
            // it crashes the server in PHP 5.4 with LaminasOptimizer+)
            return;
        }

        // Disable opcode caches that allow runtime disabling
        if (function_exists('xcache_get')) {
            // XCache; just disable it
            ini_set('xcache.cacher', '0');
        } elseif (function_exists('wincache_ocache_meminfo')) {
            // WinCache; just disable it
            ini_set('wincache.ocenabled', '0');
        }
    }
}
