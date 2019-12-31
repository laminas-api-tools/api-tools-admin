<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin;

use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ApiTools\Hal\Link\Link;
use Laminas\ApiTools\Hal\Link\LinkCollection;
use Laminas\ApiTools\Hal\Resource;
use Laminas\ApiTools\Hal\View\HalJsonModel;
use Laminas\Config\Writer\PhpArray as PhpArrayWriter;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Router\RouteMatch;

class Module
{
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
        $events->attach('render', array($this, 'onRender'), 100);
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Laminas\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
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
        return include __DIR__ . '/../../../../config/module.config.php';
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
                $writer = new PhpArrayWriter();

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
            'Laminas\ApiTools\Admin\Model\DbAdapterModel' => function ($services) {
                if (!$services->has('Config')) {
                    throw new ServiceNotCreatedException(
                        'Cannot create Laminas\ApiTools\Admin\Model\DbAdapterModel service because Config service is not present'
                    );
                }
                $config = $services->get('Config');
                $writer = new PhpArrayWriter();

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
                ) {;
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
                $factory = $services->get('Laminas\ApiTools\Admin\Model\RestServiceModelFactory');
                return new Model\RestServiceResource($factory);
            },
            'Laminas\ApiTools\Admin\Model\RpcServiceResource' => function ($services) {
                if (!$services->has('Laminas\ApiTools\Admin\Model\RpcServiceModelFactory')) {
                    throw new ServiceNotCreatedException(
                        'Laminas\ApiTools\Admin\Model\RpcServiceResource is missing one or more dependencies'
                    );
                }
                $factory = $services->get('Laminas\ApiTools\Admin\Model\RpcServiceModelFactory');
                return new Model\RpcServiceResource($factory);
            },
            'Laminas\ApiTools\Admin\Model\VersioningModelFactory' => function ($services) {
                if (!$services->has('Laminas\ApiTools\Configuration\ConfigResourceFactory')) {
                    throw new ServiceNotCreatedException(
                        'Laminas\ApiTools\Admin\Model\VersioningModelFactory is missing one or more dependencies from Laminas\ApiTools\Configuration'
                    );
                }
                $configFactory = $services->get('Laminas\ApiTools\Configuration\ConfigResourceFactory');
                return new Model\VersioningModelFactory($configFactory);
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

        $controller = $matches->getParam('controller', false);
        if ($controller != 'Laminas\ApiTools\Admin\Controller\Module') {
            return;
        }

        $result = $e->getResult();
        if (!$result instanceof HalJsonModel) {
            return;
        }

        if ($result->isResource()) {
            $this->initializeUrlHelper();
            $this->injectServiceLinks($result->getPayload(), $result);
            return;
        }

        if ($result->isCollection()) {
            $this->initializeUrlHelper();
            $viewHelpers = $this->sm->get('ViewHelperManager');
            $halPlugin   = $viewHelpers->get('hal');
            $halPlugin->getEventManager()->attach('renderCollection.resource', array($this, 'onRenderCollectionResource'), 10);
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
     * @param  Resource $resource
     * @param  HalJsonModel $model
     */
    protected function injectServiceLinks(Resource $resource, HalJsonModel $model)
    {
        $module     = $resource->resource;
        $links      = $resource->getLinks();
        $moduleName = $module['name'];

        $this->injectLinksForServicesByType('authorization', array(), $links, $moduleName);

        $this->injectLinksForServicesByType('rest', $module['rest'], $links, $moduleName);
        unset($module['rest']);

        $this->injectLinksForServicesByType('rpc', $module['rpc'], $links, $moduleName);
        unset($module['rpc']);

        $replacement = new Resource($module, $resource->id);
        $replacement->setLinks($links);
        $model->setPayload($replacement);
    }

    /**
     * Inject RPC/REST service links inside module resources that are composed in collections
     *
     * @param  \Laminas\EventManager\Event $e
     */
    public function onRenderCollectionResource($e)
    {
        $resource = $e->getParam('resource');
        if (!$resource instanceof Model\ModuleEntity) {
            return;
        }

        $asArray  = $resource->getArrayCopy();
        $module   = $asArray['name'];
        $rest     = $asArray['rest'];
        $rpc      = $asArray['rpc'];

        unset($asArray['rest']);
        unset($asArray['rpc']);

        $halResource = new Resource($asArray, $module);
        $links       = $halResource->getLinks();
        $links->add(Link::factory(array(
            'rel' => 'self',
            'route' => array(
                'name' => 'api-tools-admin/api/module',
                'params' => array(
                    'name' => $module,
                ),
            ),
        )));
        $this->injectLinksForServicesByType('authorization', array(), $links, $module);
        $this->injectLinksForServicesByType('rest', $rest, $links, $module);
        $this->injectLinksForServicesByType('rpc', $rpc, $links, $module);

        $e->setParam('resource', $halResource);
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
        $routeName    = sprintf('api-tools-admin/api/module/%s', $linkType);
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
}
