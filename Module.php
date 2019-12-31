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
use Laminas\Http\Header\GenericHeader;
use Laminas\Http\Header\GenericMultiHeader;
use Laminas\ModuleManager\ModuleManagerInterface;
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

    /**
     * Ensure the UI module is loaded
     *
     * @param ModuleManagerInterface $modules
     */
    public function init(ModuleManagerInterface $modules)
    {
        $loaded = $modules->getLoadedModules(false);
        if (isset($loaded['Laminas\ApiTools\Admin\Ui'])) {
            return;
        }

        $modules->loadModule('Laminas\ApiTools\Admin\Ui');
    }

    /**
     * Listen to the bootstrap event
     *
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        $app      = $e->getApplication();
        $this->sm = $app->getServiceManager();
        $events   = $app->getEventManager();
        $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'normalizeMatchedControllerServiceName'), -20);
        $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'normalizeMatchedInputFilterName'), -20);
        $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'onRoute'), -1000);
        $events->attach(MvcEvent::EVENT_RENDER, array($this, 'onRender'), 100);
        $events->attach(MvcEvent::EVENT_FINISH, array($this, 'onFinish'), 1000);
        $events->attachAggregate(
            $this->sm->get('Laminas\ApiTools\Admin\Listener\CryptFilterListener')
        );
    }

    /**
     * Return an array for passing to Laminas\Loader\AutoloaderFactory.
     *
     * @return array
     */
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

    /**
     * Run diagnostics
     *
     * @return array|bool
     */
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

    /**
     * Returns configuration to merge with application configuration
     *
     * @return array|\Traversable
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Expected to return \Laminas\ServiceManager\Config object or array to
     * seed such an object.
     *
     * @return array|\Laminas\ServiceManager\Config
     */
    public function getServiceConfig()
    {
        return array('factories' => array(
            'Laminas\ApiTools\Admin\Model\AuthenticationModel' => function ($services) {
                if (!$services->has('Config')) {
                    throw new ServiceNotCreatedException(
                        'Cannot create Laminas\ApiTools\Admin\Model\AuthenticationModel service '
                        . 'because Config service is not present'
                    );
                }
                $config = $services->get('Config');
                $writer = $services->get('Laminas\ApiTools\Configuration\ConfigWriter');

                $global  = new ConfigResource($config, 'config/autoload/global.php', $writer);
                $local   = new ConfigResource($config, 'config/autoload/local.php', $writer);
                $modules = $services->get('Laminas\ApiTools\Admin\Model\ModuleModel');

                return new Model\AuthenticationModel($global, $local, $modules);
            },
            'Laminas\ApiTools\Admin\Model\AuthorizationModelFactory' => function ($services) {
                if (!$services->has('Laminas\ApiTools\Admin\Model\ModulePathSpec')
                    || !$services->has('Laminas\ApiTools\Configuration\ConfigResourceFactory')
                    || !$services->has('Laminas\ApiTools\Admin\Model\ModuleModel')
                ) {
                    throw new ServiceNotCreatedException(
                        'Laminas\ApiTools\Admin\Model\AuthorizationModelFactory is missing '
                        . 'one or more dependencies from Laminas\ApiTools\Configuration'
                    );
                }
                $moduleModel   = $services->get('Laminas\ApiTools\Admin\Model\ModuleModel');
                //$moduleUtils   = $services->get('Laminas\ApiTools\Configuration\ModuleUtils');
                $modulePathSpec = $services->get('Laminas\ApiTools\Admin\Model\ModulePathSpec');
                $configFactory = $services->get('Laminas\ApiTools\Configuration\ConfigResourceFactory');

                return new Model\AuthorizationModelFactory($modulePathSpec, $configFactory, $moduleModel);
            },
            'Laminas\ApiTools\Admin\Model\DbAutodiscoveryModel' => function ($services) {
                if (!$services->has('Config')) {
                    throw new ServiceNotCreatedException(
                        'Cannot create Laminas\ApiTools\Admin\Model\DbAutodiscoveryModel service '
                        . 'because Config service is not present'
                    );
                }
                $config = $services->get('Config');

                return new Model\DbAutodiscoveryModel($config);
            },
            'Laminas\ApiTools\Admin\Model\ContentNegotiationModel' => function ($services) {
                if (!$services->has('Config')) {
                    throw new ServiceNotCreatedException(
                        'Cannot create Laminas\ApiTools\Admin\Model\ContentNegotiationModel service '
                        . 'because Config service is not present'
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
                        'Cannot create Laminas\ApiTools\Admin\Model\ContentNegotiationResource service '
                        . 'because Laminas\ApiTools\Admin\Model\ContentNegotiationModel service is not present'
                    );
                }
                $model = $services->get('Laminas\ApiTools\Admin\Model\ContentNegotiationModel');
                return new Model\ContentNegotiationResource($model);
            },
            'Laminas\ApiTools\Admin\Model\DbAdapterModel' => function ($services) {
                if (!$services->has('Config')) {
                    throw new ServiceNotCreatedException(
                        'Cannot create Laminas\ApiTools\Admin\Model\DbAdapterModel service '
                        . 'because Config service is not present'
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
                        'Cannot create Laminas\ApiTools\Admin\Model\DbAdapterResource service '
                        . 'because Laminas\ApiTools\Admin\Model\DbAdapterModel service is not present'
                    );
                }
                $model = $services->get('Laminas\ApiTools\Admin\Model\DbAdapterModel');
                return new Model\DbAdapterResource($model);
            },
            'Laminas\ApiTools\Admin\Model\DoctrineAdapterModel' => function ($services) {
                if (!$services->has('Config')) {
                    throw new ServiceNotCreatedException(
                        'Cannot create Laminas\ApiTools\Admin\Model\DbAdapterModel service '
                        . 'because Config service is not present'
                    );
                }

                $config = $services->get('Config');
                $writer = $services->get('Laminas\ApiTools\Configuration\ConfigWriter');

                $global = new ConfigResource($config, 'config/autoload/doctrine.global.php', $writer);
                $local  = new ConfigResource($config, 'config/autoload/doctrine.local.php', $writer);

                return new Model\DoctrineAdapterModel($global, $local);
            },
            'Laminas\ApiTools\Admin\Model\DoctrineAdapterResource' => function ($services) {
                if (!$services->has('Laminas\ApiTools\Admin\Model\DoctrineAdapterModel')) {
                    throw new ServiceNotCreatedException(
                        'Cannot create Laminas\ApiTools\Admin\Model\DoctrineAdapterResource service '
                        . 'because Laminas\ApiTools\Admin\Model\DoctrineAdapterModel service is not present'
                    );
                }
                $model = $services->get('Laminas\ApiTools\Admin\Model\DoctrineAdapterModel');
                return new Model\DoctrineAdapterResource($model);
            },
            'Laminas\ApiTools\Admin\Model\ModulePathSpec' => function ($services) {
                if (!$services->has('Laminas\ApiTools\Configuration\ModuleUtils')) {
                    throw new ServiceNotCreatedException(
                        'Cannot create Laminas\ApiTools\Admin\Model\ModulePathSpec service '
                        . 'because Laminas\ApiTools\Configuration\ModuleUtils service is not present'
                    );
                }

                $pathSpec   = 'psr-0';
                $path       = '.';
                if ($services->has('Config')) {
                    $config = $services->get('Config');
                    if (!empty($config['api-tools-admin'])) {
                        if (!empty($config['api-tools-admin']['path_spec'])) {
                            $pathSpec = $config['api-tools-admin']['path_spec'];
                        }

                        if (isset($config['api-tools-admin']['module_path'])) {
                            $path = $config['api-tools-admin']['module_path'];
                            if (!is_dir($path)) {
                                throw new ServiceNotCreatedException(sprintf(
                                    'Invalid module path "%s"; does not exist',
                                    $path
                                ));
                            }
                        }
                    }
                }

                $modulePathSpec = new Model\ModulePathSpec(
                    $services->get('Laminas\ApiTools\Configuration\ModuleUtils'),
                    $pathSpec,
                    $path
                );

                return $modulePathSpec;
            },
            'Laminas\ApiTools\Admin\Model\ModuleModel' => function ($services) {
                if (!$services->has('ModuleManager')) {
                    throw new ServiceNotCreatedException(
                        'Cannot create Laminas\ApiTools\Admin\Model\ModuleModel service '
                        . 'because ModuleManager service is not present'
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
                $moduleModel     = $services->get('Laminas\ApiTools\Admin\Model\ModuleModel');
                $modulePathSpec = $services->get('Laminas\ApiTools\Admin\Model\ModulePathSpec');

                $listener        = new Model\ModuleResource($moduleModel, $modulePathSpec);

                return $listener;
            },
            'Laminas\ApiTools\Admin\Model\RestServiceModelFactory' => function ($services) {
                if (!$services->has('Laminas\ApiTools\Admin\Model\ModulePathSpec')
                    || !$services->has('Laminas\ApiTools\Configuration\ConfigResourceFactory')
                    || !$services->has('Laminas\ApiTools\Admin\Model\ModuleModel')
                    || !$services->has('SharedEventManager')
                ) {
                    throw new ServiceNotCreatedException(
                        'Laminas\ApiTools\Admin\Model\RestServiceModelFactory is missing '
                        . 'one or more dependencies from Laminas\ApiTools\Configuration'
                    );
                }
                $moduleModel   = $services->get('Laminas\ApiTools\Admin\Model\ModuleModel');
                $modulePathSpec = $services->get('Laminas\ApiTools\Admin\Model\ModulePathSpec');
                $configFactory = $services->get('Laminas\ApiTools\Configuration\ConfigResourceFactory');
                $sharedEvents  = $services->get('SharedEventManager');


                // Wire DB-Connected fetch listener
                $sharedEvents->attach(
                    __NAMESPACE__ . '\Model\RestServiceModel',
                    'fetch',
                    'Laminas\ApiTools\Admin\Model\DbConnectedRestServiceModel::onFetch'
                );

                $modules = $services->get('ModuleManager');
                $loaded = $modules->getLoadedModules(false);
                if (isset($loaded['Laminas\ApiTools\Doctrine\Admin'])) {
                    // Wire Doctrine-Connected fetch listener
                    $sharedEvents->attach(
                        __NAMESPACE__ . '\Model\RestServiceModel',
                        'fetch',
                        'Laminas\ApiTools\Doctrine\Admin\Model\DoctrineRestServiceModel::onFetch'
                    );
                }
                return new Model\RestServiceModelFactory($modulePathSpec, $configFactory, $sharedEvents, $moduleModel);
            },
            'Laminas\ApiTools\Admin\Model\RpcServiceModelFactory' => function ($services) {
                if (!$services->has('Laminas\ApiTools\Admin\Model\ModulePathSpec')
                    || !$services->has('Laminas\ApiTools\Configuration\ConfigResourceFactory')
                    || !$services->has('Laminas\ApiTools\Admin\Model\ModuleModel')
                    || !$services->has('SharedEventManager')
                ) {
                    throw new ServiceNotCreatedException(
                        'Laminas\ApiTools\Admin\Model\RpcServiceModelFactory is missing '
                        . 'one or more dependencies from Laminas\ApiTools\Configuration'
                    );
                }
                $moduleModel   = $services->get('Laminas\ApiTools\Admin\Model\ModuleModel');
                $modulePathSpec = $services->get('Laminas\ApiTools\Admin\Model\ModulePathSpec');
                $configFactory = $services->get('Laminas\ApiTools\Configuration\ConfigResourceFactory');
                $sharedEvents  = $services->get('SharedEventManager');
                return new Model\RpcServiceModelFactory($modulePathSpec, $configFactory, $sharedEvents, $moduleModel);
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
                return new Model\RpcServiceResource(
                    $factory,
                    $inputFilterModel,
                    $controllerManager,
                    $documentationModel
                );
            },
            'Laminas\ApiTools\Admin\Model\VersioningModelFactory' => function ($services) {
                if (!$services->has('Laminas\ApiTools\Configuration\ConfigResourceFactory')
                    || !$services->has('Laminas\ApiTools\Admin\Model\ModulePathSpec')
                ) {
                    throw new ServiceNotCreatedException(
                        'Laminas\ApiTools\Admin\Model\VersioningModelFactory is missing '
                        . 'one or more dependencies from Laminas\ApiTools\Configuration'
                    );
                }
                $configFactory = $services->get('Laminas\ApiTools\Configuration\ConfigResourceFactory');
                $modulePathSpec = $services->get('Laminas\ApiTools\Admin\Model\ModulePathSpec');
                return new Model\VersioningModelFactory($configFactory, $modulePathSpec);
            },
        ));
    }

    /**
     * Expected to return \Laminas\ServiceManager\Config object or array to seed
     * such an object.
     *
     * @return array|\Laminas\ServiceManager\Config
     */
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

    public function normalizeMatchedControllerServiceName($e)
    {
        $matches = $e->getRouteMatch();
        if (! $matches || ! $matches->getParam('controller_service_name')) {
            return;
        }

        // Replace '-' with namespace separator
        $controller = $matches->getParam('controller_service_name');
        $matches->setParam('controller_service_name', str_replace('-', '\\', $controller));
    }

    public function normalizeMatchedInputFilterName($e)
    {
        $matches = $e->getRouteMatch();
        if (! $matches || ! $matches->getParam('input_filter_name')) {
            return;
        }

        // Replace '-' with namespace separator
        $controller = $matches->getParam('input_filter_name');
        $matches->setParam('input_filter_name', str_replace('-', '\\', $controller));
    }

    /**
     * Ensure the render_collections flag of the HAL view helper is enabled
     * regardless of the configuration setting if we match an admin service.
     *
     * @param MvcEvent $e
     */
    public function onRoute(MvcEvent $e)
    {
        $matches = $e->getRouteMatch();
        if (! $matches
            || 0 !== strpos($matches->getParam('controller'), 'Laminas\ApiTools\Admin\\')
        ) {
            return;
        }

        $app      = $e->getTarget();
        $services = $app->getServiceManager();
        $helpers  = $services->get('ViewHelperManager');
        $hal      = $helpers->get('Hal');
        $hal->setRenderCollections(true);
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

        $halPlugin->getEventManager()->attach(
            array('renderCollection', 'renderEntity', 'renderCollection.Entity'),
            function ($e) use ($matches) {
                if ($matches->getParam('controller_service_name')) {
                    $matches->setParam(
                        'controller_service_name',
                        str_replace('\\', '-', $matches->getParam('controller_service_name'))
                    );
                }
            }
        );

        //if content is empty, then send the response with a 204 and an emtpy body

        if ($result->isEntity()) {
            $this->injectServiceLinks($result->getPayload(), $result, $e);
            $halPlugin->getEventManager()->attach('renderEntity', array($this, 'onRenderEntity'), 10);
            return;
        }

        if ($result->isCollection()) {
            $this->mvcEvent = $e;
            $halPlugin->getEventManager()->attach(
                'renderCollection.entity',
                array($this, 'onRenderCollectionEntity'),
                10
            );
        }
    }

    /**
     * Tell browsers not to cache responses from the admin API
     *
     * @param  \Laminas\Mvc\MvcEvent $e
     */
    public function onFinish($e)
    {
        $matches = $e->getRouteMatch();
        if (!$matches instanceof RouteMatch) {
            // In 404's, we do not have a route match... nor do we need to do
            // anything
            return;
        }

        if (! $matches->getParam('is_api-tools_admin_api', false)) {
            // Not part of the Laminas API Tools Admin API; nothing to do
            return;
        }

        $request = $e->getRequest();
        if ($request->isGet() || $request->isHead()) {
            $this->disableHttpCache($e->getResponse()->getHeaders());
        }
    }

    protected function initializeUrlHelper()
    {
        $viewHelpers     = $this->sm->get('ViewHelperManager');
        $urlHelper       = $viewHelpers->get('Url');
        $serverUrlHelper = $viewHelpers->get('ServerUrl');
        // @codingStandardsIgnoreStart
        $this->urlHelper = function ($routeName, $routeParams, $routeOptions, $reUseMatchedParams) use ($urlHelper, $serverUrlHelper) {
            $url = call_user_func($urlHelper, $routeName, $routeParams, $routeOptions, $reUseMatchedParams);
            if (substr($url, 0, 4) == 'http') {
                return $url;
            }
            return call_user_func($serverUrlHelper, $url);
        };
        // @codingStandardsIgnoreEnd
    }

    /**
     * Inject links for the service services of a module
     *
     * @param Entity $halEntity
     * @param HalJsonModel $model
     * @param $e
     */
    protected function injectServiceLinks(Entity $halEntity, HalJsonModel $model, $e)
    {
        $entity = $halEntity->entity;
        $links  = $halEntity->getLinks();
        if ($entity instanceof Model\ModuleEntity) {
            $this->injectModuleResourceRelationalLinks($entity, $links, $model);
        }
        if ($entity instanceof Model\RestServiceEntity || $entity instanceof Model\RpcServiceEntity) {
            $this->normalizeEntityControllerServiceName($entity, $links, $model);
        }
        if ($entity instanceof Model\InputFilterEntity) {
            $this->normalizeEntityInputFilterName($entity, $links, $model);
        }
    }

    /**
     * @param Model\ModuleEntity $module
     * @param $links
     * @param HalJsonModel $model
     */
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

    /**
     * @param $entity
     * @param $links
     * @param HalJsonModel $model
     */
    protected function normalizeEntityControllerServiceName($entity, $links, HalJsonModel $model)
    {
        $entity->exchangeArray(array(
            'controller_service_name' => str_replace('\\', '-', $entity->controllerServiceName),
        ));
        $halEntity = new Entity($entity, $entity->controllerServiceName);

        if ($links->has('self')) {
            $links->remove('self');
        }
        $halEntity->setLinks($links);

        $model->setPayload($halEntity);
    }

    /**
     * @param Model\InputFilterEntity $entity
     * @param $links
     * @param HalJsonModel $model
     */
    protected function normalizeEntityInputFilterName(Model\InputFilterEntity $entity, $links, HalJsonModel $model)
    {
        $entity['input_filter_name'] = str_replace('\\', '-', $entity['input_filter_name']);
        $halEntity = new Entity($entity, $entity['input_filter_name']);

        if ($links->has('self')) {
            $links->remove('self');
        }
        $halEntity->setLinks($links);

        $model->setPayload($halEntity);
    }

    /**
     * @param $e
     */
    public function onRenderEntity($e)
    {
        $halEntity = $e->getParam('entity');
        $entity    = $halEntity->entity;
        $hal       = $e->getTarget();

        if ($entity instanceof Model\RestServiceEntity
            || $entity instanceof Model\RpcServiceEntity
            || (is_array($entity) && array_key_exists('controller_service_name', $entity))
        ) {
            $serviceName = is_array($entity) ? $entity['controller_service_name'] : $entity->controllerServiceName;
            $links       = $halEntity->getLinks();

            if ($links->has('input_filter')) {
                $link   = $links->get('input_filter');
                $params = $link->getRouteParams();
                $link->setRouteParams(array_merge($params, array(
                    'controller_service_name' => $serviceName
                )));
            }

            if ($links->has('documentation')) {
                $link   = $links->get('documentation');
                $params = $link->getRouteParams();
                $link->setRouteParams(array_merge($params, array(
                    'controller_service_name' => $serviceName
                )));
            }

            if (! $links->has('self')) {
                $route  = 'api-tools/api/module/';
                $route .= $entity instanceof Model\RestServiceEntity ? 'rest-service' : 'rpc-service';
                $hal->injectSelfLink($halEntity, $route, 'controller_service_name');
            }
            return;
        }

        if ($entity instanceof Model\InputFilterEntity
            || (is_array($entity) && isset($entity['input_filter_name']))
        ) {
            switch (true) {
                case ($entity instanceof Model\RestInputFilterEntity):
                    $type = 'rest-service';
                    break;
                case ($entity instanceof Model\RpcInputFilterEntity):
                    $type = 'rpc-service';
                    break;
            }
            $links       = $halEntity->getLinks();

            if (! $links->has('self')) {
                $route  = sprintf('api-tools/api/module/%s/input-filter', $type);
                $hal->injectSelfLink($halEntity, $route, 'input_filter_name');
            }
            return;
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

        if ($entity instanceof Model\InputFilterEntity) {
            return $this->normalizeInputFilterEntityName($entity, $e);
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

    /**
     * @param $entity
     * @param $e
     */
    public function injectServiceCollectionRelationalLinks($entity, $e)
    {
        $entity->exchangeArray(array(
            'controller_service_name' => str_replace('\\', '-', $entity->controllerServiceName),
        ));

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
     * @param $entity
     * @param $e
     */
    protected function normalizeInputFilterEntityName($entity, $e)
    {
        $entity['input_filter_name'] = str_replace('\\', '-', $entity['input_filter_name']);
        $e->setParam('entity', $entity);
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

    /**
     * @param $service
     * @return string
     */
    protected function getServiceType($service)
    {
        if (strstr($service, '\\Rest\\')
            || strstr($service, '-Rest-')
        ) {
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

    /**
     * Prepare cache-busting headers for GET requests
     *
     * Invoked from the onFinish() method for GET requests to disable client-side HTTP caching.
     *
     * @param \Laminas\Http\Headers $headers
     */
    protected function disableHttpCache($headers)
    {
        $headers->addHeader(new GenericHeader('Expires', '0'));
        // $headers->addHeaderLine('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        $headers->addHeader(new GenericMultiHeader('Cache-Control', 'no-store, no-cache, must-revalidate'));
        $headers->addHeader(new GenericMultiHeader('Cache-Control', 'post-check=0, pre-check=0'));
        $headers->addHeaderLine('Pragma', 'no-cache');
    }
}
