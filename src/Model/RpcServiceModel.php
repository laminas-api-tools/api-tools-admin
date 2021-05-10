<?php

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Exception;
use Laminas\ApiTools\Admin\Utility;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ApiTools\Rest\Exception\CreationException;
use Laminas\ApiTools\Rest\Exception\PatchException;
use Laminas\Filter\FilterChain;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver;
use ReflectionClass;

class RpcServiceModel
{
    /**
     * @var ConfigResource
     */
    protected $configResource;

    /**
     * @var FilterChain
     */
    protected $filter;

    /**
     * @var string
     */
    protected $module;

    /**
     * @var ModuleEntity
     */
    protected $moduleEntity;

    /**
     * @var ModulePathSpec
     */
    protected $modules;

    /**
     * @param ModuleEntity $moduleEntity
     * @param ModulePathSpec $modules
     * @param ConfigResource $config
     */
    public function __construct(ModuleEntity $moduleEntity, ModulePathSpec $modules, ConfigResource $config)
    {
        $this->module         = $moduleEntity->getName();
        $this->moduleEntity   = $moduleEntity;
        $this->modules        = $modules;
        $this->configResource = $config;
    }

    /**
     * Fetch a single RPC service
     *
     * @todo   get route details?
     * @param  string $controllerServiceName
     * @return RpcServiceEntity|false
     */
    public function fetch($controllerServiceName)
    {
        $data   = ['controller_service_name' => $controllerServiceName];
        $config = $this->configResource->fetch(true);

        if (! isset($config['api-tools-rpc'][$controllerServiceName])) {
            return false;
        }

        $rpcConfig = $config['api-tools-rpc'][$controllerServiceName];

        if (isset($rpcConfig['route_name'])) {
            $data['route_name']  = $rpcConfig['route_name'];
            $data['route_match'] = $this->getRouteMatchStringFromModuleConfig($data['route_name'], $config);
        }

        if (isset($rpcConfig['http_methods'])) {
            $data['http_methods'] = $rpcConfig['http_methods'];
        }

        if (! empty($rpcConfig['service_name'])) {
            $data['service_name'] = $rpcConfig['service_name'];
        } else {
            $data['service_name'] = $controllerServiceName;
            $pattern = vsprintf(
                '#%sV[^%s]+%sRpc%s(?<service>[^%s]+)%sController#',
                array_fill(0, 6, preg_quote('\\'))
            );
            if (preg_match($pattern, $controllerServiceName, $matches)) {
                $data['service_name'] = $matches['service'];
            }
        }

        if (isset($config['api-tools-content-negotiation'])) {
            $contentNegotiationConfig = $config['api-tools-content-negotiation'];
            if (isset($contentNegotiationConfig['controllers'][$controllerServiceName])) {
                $data['selector'] = $contentNegotiationConfig['controllers'][$controllerServiceName];
            }

            if (isset($contentNegotiationConfig['accept_whitelist'][$controllerServiceName])) {
                $data['accept_whitelist'] = $contentNegotiationConfig['accept_whitelist'][$controllerServiceName];
            }

            if (isset($contentNegotiationConfig['content_type_whitelist'][$controllerServiceName])) {
                $data['content_type_whitelist'] =
                    $contentNegotiationConfig['content_type_whitelist'][$controllerServiceName];
            }
        }

        $service = new RpcServiceEntity();
        $service->exchangeArray($data);
        return $service;
    }

    /**
     * Fetch all services
     *
     * @param int $version
     * @return RpcServiceEntity[]
     * @throws Exception\RuntimeException
     */
    public function fetchAll($version = null)
    {
        $config = $this->configResource->fetch(true);
        if (! isset($config['api-tools-rpc'])) {
            return [];
        }

        $services = [];
        $pattern  = false;

        // Initialize pattern if a version was passed and it's valid
        if (null !== $version) {
            if (! in_array($version, $this->moduleEntity->getVersions())) {
                throw new Exception\RuntimeException(sprintf(
                    'Invalid version "%s" provided',
                    $version
                ), 400);
            }
            $namespaceSep = preg_quote('\\');
            $pattern = sprintf(
                '#%s%sV%s#',
                $this->moduleNameToRegex(),
                $namespaceSep,
                $version
            );
        }

        foreach (array_keys($config['api-tools-rpc']) as $controllerService) {
            if (! $pattern) {
                $services[] = $this->fetch($controllerService);
                continue;
            }

            if (preg_match($pattern, $controllerService)) {
                $services[] = $this->fetch($controllerService);
                continue;
            }
        }

        return $services;
    }

    /**
     * Create a new RPC service in this module
     *
     * Creates the controller and all configuration, returning the full configuration as a tree.
     *
     * @todo   Return the controller service name
     * @param  string $serviceName
     * @param  string $routeMatch
     * @param  array $httpMethods
     * @param  null|string $selector
     * @return RpcServiceEntity
     * @throws CreationException
     */
    public function createService($serviceName, $routeMatch, $httpMethods, $selector = null)
    {
        $normalizedServiceName = ucfirst($serviceName);

        if (! preg_match('/^[a-zA-Z][a-zA-Z0-9_]*(\\\[a-zA-Z][a-zA-Z0-9_]*)*$/', $normalizedServiceName)) {
            throw new CreationException('Invalid service name; must be a valid PHP namespace name.');
        }

        $controllerData    = $this->createController($normalizedServiceName);
        $controllerService = $controllerData->service;
        $routeName         = $this->createRoute($routeMatch, $normalizedServiceName, $controllerService);
        $this->createRpcConfig($serviceName, $controllerService, $routeName, $httpMethods);
        $this->createContentNegotiationConfig($controllerService, $selector);

        return $this->fetch($controllerService);
    }

    /**
     * Delete a service
     *
     * @param  RpcServiceEntity $entity
     * @param  bool $recursive
     * @return true
     * @throws Exception\RuntimeException
     */
    public function deleteService(RpcServiceEntity $entity, $recursive = false)
    {
        $serviceName = $entity->controllerServiceName;
        $routeName   = $entity->routeName;

        $this->deleteRouteConfig($routeName, $serviceName);
        $this->deleteRpcConfig($serviceName);
        $this->deleteContentNegotiationConfig($serviceName);
        $this->deleteContentValidationConfig($serviceName);
        $this->deleteVersioningConfig($routeName, $serviceName);
        $this->deleteAuthorizationConfig($serviceName);
        $this->deleteControllersConfig($serviceName);

        if ($recursive) {
            $className = substr($entity->controllerServiceName, 0, strrpos($entity->controllerServiceName, '\\')) .
                '\\' . $entity->serviceName . 'Controller';
            if (! class_exists($className)) {
                throw new Exception\RuntimeException(sprintf(
                    'I cannot determine the class name, tried with "%s"',
                    $className
                ), 400);
            }
            $reflection = new ReflectionClass($className);
            Utility::recursiveDelete(dirname($reflection->getFileName()));
        }
        return true;
    }

    /**
     * @param string $serviceName
     * @return bool|string
     * @throws Exception\RuntimeException
     */
    public function createFactoryController($serviceName)
    {
        $module  = $this->module;
        $version = $this->moduleEntity->getLatestVersion();

        $srcPath = $this->modules->getRpcPath($module, $version, $serviceName);

        $className    = sprintf('%sController', $serviceName);
        $classFactory = sprintf('%sControllerFactory', $serviceName);
        $classPath    = sprintf('%s/%s.php', $srcPath, $classFactory);

        if (file_exists($classPath)) {
            throw new Exception\RuntimeException(sprintf(
                'The controller factory "%s" already exists',
                $className
            ), 409);
        }

        $view = new ViewModel([
            'module'       => $module,
            'classname'    => $className,
            'classfactory' => $classFactory,
            'servicename'  => $serviceName,
            'version'      => $version,
        ]);

        $resolver = new Resolver\TemplateMapResolver([
            'code-connected/rpc-controller' => __DIR__ . '/../../view/code-connected/rpc-factory.phtml',
        ]);

        $view->setTemplate('code-connected/rpc-controller');
        $renderer = new PhpRenderer();
        $renderer->setResolver($resolver);

        if (! file_put_contents(
            $classPath,
            "<" . "?php\n" . $renderer->render($view)
        )) {
            return false;
        }

        return sprintf('%s\\V%s\\Rpc\\%s\\%s', $module, $version, $serviceName, $classFactory);
    }

    /**
     * Create a controller in the current module named for the given service
     *
     * @param  string $serviceName
     * @return object|false
     * @throws Exception\RuntimeException
     */
    public function createController($serviceName)
    {
        $module      = $this->module;
        $version     = $this->moduleEntity->getLatestVersion();
        $serviceName = str_replace("\\", "/", $serviceName);

        $srcPath = $this->modules->getRpcPath($module, $version, $serviceName);

        if (! file_exists($srcPath)) {
            mkdir($srcPath, 0775, true);
        }

        $className         = sprintf('%sController', $serviceName);
        $classPath         = sprintf('%s/%s.php', $srcPath, $className);
        $controllerService = sprintf('%s\\V%s\\Rpc\\%s\\Controller', $module, $version, $serviceName);

        if (file_exists($classPath)) {
            throw new Exception\RuntimeException(sprintf(
                'The controller "%s" already exists',
                $className
            ), 409);
        }

        $view = new ViewModel([
            'module'      => $module,
            'classname'   => $className,
            'servicename' => $serviceName,
            'version'     => $version,
        ]);

        $resolver = new Resolver\TemplateMapResolver([
            'code-connected/rpc-controller' => __DIR__ . '/../../view/code-connected/rpc-controller.phtml',
        ]);

        $view->setTemplate('code-connected/rpc-controller');
        $renderer = new PhpRenderer();
        $renderer->setResolver($resolver);

        if (! file_put_contents(
            $classPath,
            "<" . "?php\n" . $renderer->render($view)
        )) {
            return false;
        }

        $fullClassFactory = $this->createFactoryController($serviceName);

        $this->configResource->patch([
            'controllers' => [
                'factories' => [
                    $controllerService => $fullClassFactory,
                ],
            ],
        ], true);

        $fullClassName = sprintf('%s\\V%s\\Rpc\\%s\\%s', $module, $version, $serviceName, $className);

        return (object) [
            'class'   => $fullClassName,
            'file'    => $classPath,
            'service' => $controllerService,
        ];
    }

    /**
     * Check if a route already exist in the configuration
     *
     * @param  string $route
     * @param  string $excludeRouteName
     * @return bool
     */
    protected function routeAlreadyExist($route, $excludeRouteName = null)
    {
        // Remove optional parameter in the route
        $route = preg_replace('/(\[[^\]]+\])/', '', $route);
        $config = $this->configResource->fetch(true);
        if (isset($config['router']['routes'])) {
            foreach ($config['router']['routes'] as $routeName => $routeConfig) {
                // Remove optional parameters in the route
                $routeWithoutParam = preg_replace('/(\[[^\]]+\])/', '', $routeConfig['options']['route']);
                if ($routeWithoutParam === $route && $excludeRouteName !== $routeName) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Create the route configuration
     *
     * @param  string $route
     * @param  string $serviceName
     * @param  string $controllerService
     * @return string The newly created route name
     * @throws Exception\RuntimeException
     */
    public function createRoute($route, $serviceName, $controllerService = null)
    {
        if (null === $controllerService) {
            $controllerService = sprintf('%s\\Rpc\\%s\\Controller', $this->module, $serviceName);
        }

        $routeName = sprintf('%s.rpc.%s', $this->normalize($this->module), $this->normalize($serviceName));
        $action    = lcfirst($serviceName);

        if ($this->routeAlreadyExist($route, $routeName)) {
            throw new Exception\RuntimeException(sprintf(
                'The route match "%s" already exists',
                $route
            ), 409);
        }

        $config = [
            'router' => [
                'routes' => [
                    $routeName => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => $route,
                            'defaults' => [
                                'controller' => $controllerService,
                                'action'     => $action,
                            ],
                        ],
                    ],
                ],
            ],
            'api-tools-versioning' => [
                'uri' => [
                    $routeName,
                ],
            ],
        ];

        $this->configResource->patch($config, true);
        return $routeName;
    }

    /**
     * Create the api-tools-rpc configuration for the controller service
     *
     * @param  string $serviceName
     * @param  string $controllerService
     * @param  string $routeName
     * @param  array $httpMethods
     * @param  null|string|callable $callable
     * @return array
     */
    public function createRpcConfig(
        $serviceName,
        $controllerService,
        $routeName,
        array $httpMethods = ['GET'],
        $callable = null
    ) {
        $config = ['api-tools-rpc' => [
            $controllerService => [
                'service_name' => $serviceName,
                'http_methods' => $httpMethods,
                'route_name'   => $routeName,
            ],
        ]];
        if (null !== $callable) {
            $config[$controllerService]['callable'] = $callable;
        }
        return $this->configResource->patch($config, true);
    }

    /**
     * Create the selector configuration
     *
     * @param  string $controllerService
     * @param  string $selector
     * @return array
     */
    public function createContentNegotiationConfig($controllerService, $selector = null)
    {
        if (null === $selector) {
            $selector = 'Json';
        }

        $mediaType = $this->createMediaType();

        $config = ['api-tools-content-negotiation' => [
            'controllers' => [
                $controllerService => $selector,
            ],
            'accept_whitelist' => [
                $controllerService => [
                    $mediaType,
                    'application/json',
                    'application/*+json',
                ],
            ],
            'content_type_whitelist' => [
                $controllerService => [
                    $mediaType,
                    'application/json',
                ],
            ],
        ]];
        return $this->configResource->patch($config, true);
    }

    /**
     * Update the route associated with a controller service
     *
     * @param  string $controllerService
     * @param  string $routeMatch
     * @return true
     * @throws Exception\RuntimeException
     */
    public function updateRoute($controllerService, $routeMatch)
    {
        $services  = $this->fetch($controllerService);
        if (! $services) {
            return false;
        }
        $services  = $services->getArrayCopy();
        $routeName = $services['route_name'];
        if ($this->routeAlreadyExist($routeMatch, $routeName)) {
            throw new Exception\RuntimeException(sprintf(
                'The route match "%s" already exists',
                $routeMatch
            ), 409);
        }
        $config = $this->configResource->fetch(true);
        $config['router']['routes'][$routeName]['options']['route'] = $routeMatch;
        $this->configResource->overwrite($config);
        return true;
    }

    /**
     * Update the allowed HTTP methods for a given service
     *
     * @param  string $controllerService
     * @param  array $httpMethods
     * @return true
     */
    public function updateHttpMethods($controllerService, array $httpMethods)
    {
        $config = $this->configResource->fetch(true);
        $config['api-tools-rpc'][$controllerService]['http_methods'] = $httpMethods;
        $this->configResource->overwrite($config);
        return true;
    }

    /**
     * Update the content-negotiation selector for the given service
     *
     * @param  string $controllerService
     * @param  string $selector
     * @return true
     */
    public function updateSelector($controllerService, $selector)
    {
        $config = $this->configResource->fetch(true);
        $config['api-tools-content-negotiation']['controllers'][$controllerService] = $selector;
        $this->configResource->overwrite($config);
        return true;
    }

    /**
     * Update configuration for a content negotiation whitelist for a named controller service
     *
     * @param  string $controllerService
     * @param  string $headerType
     * @param  array $whitelist
     * @return true
     * @throws PatchException
     */
    public function updateContentNegotiationWhitelist($controllerService, $headerType, array $whitelist)
    {
        if (! in_array($headerType, ['accept', 'content_type'])) {
            /** @todo define exception in Rpc namespace */
            throw new PatchException('Invalid content negotiation whitelist type provided', 422);
        }
        $headerType .= '_whitelist';
        $config = $this->configResource->fetch(true);
        $config['api-tools-content-negotiation'][$headerType][$controllerService] = $whitelist;
        $this->configResource->overwrite($config);
        return true;
    }

    /**
     * Removes the route configuration for a named route
     *
     * @param  string $routeName
     * @param  string $serviceName
     */
    public function deleteRouteConfig($routeName, $serviceName)
    {
        if (false === strstr($serviceName, '\\V1\\')) {
            // > V1; nothing to do
            return;
        }

        $key = ['router', 'routes', $routeName];
        $this->configResource->deleteKey($key);
    }

    /**
     * Delete any versionin configuration for a service
     *
     * Only for version 1; later versions will do nothing
     *
     * @param  string $routeName
     * @param  string $serviceName
     */
    public function deleteVersioningConfig($routeName, $serviceName)
    {
        if (false === strstr($serviceName, '\\V1\\')) {
            // > V1; nothing to do
            return;
        }

        $config = $this->configResource->fetch(true);
        if (! isset($config['api-tools-versioning']['uri'])) {
            return;
        }

        if (! in_array($routeName, $config['api-tools-versioning']['uri'], true)) {
            return;
        }

        $versioning = array_filter($config['api-tools-versioning']['uri'], function ($value) use ($routeName) {
            if ($routeName === $value) {
                return false;
            }
            return true;
        });

        $key = ['api-tools-versioning', 'uri'];
        $this->configResource->patchKey($key, $versioning);
    }

    /**
     * Remove any controller service configuration for a service
     *
     * @param  string $serviceName
     */
    public function deleteControllersConfig($serviceName)
    {
        foreach (['invokables', 'factories'] as $serviceType) {
            $key = ['controllers', $serviceType, $serviceName];
            $this->configResource->deleteKey($key);
        }
    }

    /**
     * Delete the RPC configuration for a named RPC service
     *
     * @param  string $serviceName
     */
    public function deleteRpcConfig($serviceName)
    {
        $key = ['api-tools-rpc', $serviceName];
        $this->configResource->deleteKey($key);
    }

    /**
     * Delete the Content Negotiation configuration for a named RPC
     * service
     *
     * @param  string $serviceName
     */
    public function deleteContentNegotiationConfig($serviceName)
    {
        $key = ['api-tools-content-negotiation', 'controllers', $serviceName];
        $this->configResource->deleteKey($key);

        $key = ['api-tools-content-negotiation', 'accept_whitelist', $serviceName];
        $this->configResource->deleteKey($key);

        $key = ['api-tools-content-negotiation', 'content_type_whitelist', $serviceName];
        $this->configResource->deleteKey($key);
    }

    /**
     * Delete content-validation configuration associated with a service
     *
     * @param  string $serviceName
     */
    public function deleteContentValidationConfig($serviceName)
    {
        $key = ['api-tools-content-validation', $serviceName];
        $this->configResource->deleteKey($key);
    }

    /**
     * Delete authorization configuration associated with a service
     *
     * @param  string $serviceName
     */
    public function deleteAuthorizationConfig($serviceName)
    {
        $key = ['api-tools-mvc-auth', 'authorization', $serviceName];
        $this->configResource->deleteKey($key);
    }

    /**
     * Normalize a service or module name to lowercase, dash-separated
     *
     * @param  string $string
     * @return string
     */
    protected function normalize($string)
    {
        $filter = $this->getNormalizationFilter();
        $string = str_replace('\\', '-', $string);
        return $filter->filter($string);
    }

    /**
     * Retrieve and/or initialize the normalization filter chain
     *
     * @return FilterChain
     */
    protected function getNormalizationFilter()
    {
        if ($this->filter instanceof FilterChain) {
            return $this->filter;
        }
        $this->filter = new FilterChain();
        $this->filter->attachByName('WordCamelCaseToDash')
                     ->attachByName('StringToLower');
        return $this->filter;
    }

    /**
     * Retrieve the URL match for the given route name
     *
     * @param  string $routeName
     * @param  array $config
     * @return false|string
     */
    protected function getRouteMatchStringFromModuleConfig($routeName, array $config)
    {
        if (! isset($config['router']['routes'])) {
            return false;
        }

        $config = $config['router']['routes'];
        if (! isset($config[$routeName])
            || ! is_array($config[$routeName])
        ) {
            return false;
        }

        $config = $config[$routeName];

        if (! isset($config['options']['route'])) {
            return false;
        }

        return $config['options']['route'];
    }

    /**
     * Create the mediatype for this
     *
     * Based on the module and the latest module version.
     *
     * @return string
     */
    public function createMediaType()
    {
        return sprintf(
            'application/vnd.%s.v%s+json',
            $this->normalize($this->module),
            $this->moduleEntity->getLatestVersion()
        );
    }

    /**
     * Converts a module name (which could include namespace separators) into a string that can be used in regex
     * matches. Use-cases:
     *      - Acme\Account => Acme\\Account
     *      - Acme\\Account (ideally it should never happen) => Acme\\Account
     *      - Acme => Acme
     *
     * @return string
     */
    private function moduleNameToRegex()
    {
        // find all backslashes (\) that are NOT followed by another \ and replace them with \\ (two of them)
        return preg_replace('#\\\\(?!\\\\)#', '\\\\\\\\', $this->module);
    }
}
