<?php

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Exception;
use Laminas\ApiTools\Admin\Utility;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ApiTools\Rest\Exception\CreationException;
use Laminas\EventManager\Event;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Filter\FilterChain;
use Laminas\Stdlib\ArrayUtils;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver;
use ReflectionClass;

class RestServiceModel implements EventManagerAwareInterface
{
    /**
     * @var ConfigResource
     */
    protected $configResource;

    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @var string
     */
    protected $module;

    /**
     * @var ModuleEntity
     */
    protected $moduleEntity;

    /**
     * @var string
     */
    protected $modulePath;

    /**
     * @var ModulePathSpec
     */
    protected $modules;

    /**
     * @var PhpRenderer
     */
    protected $renderer;

    /**
     * Allowed REST update options that are scalars
     *
     * @var array
     */
    protected $restScalarUpdateOptions = [
        'collectionClass'          => 'collection_class',
        'collectionName'           => 'collection_name',
        'entityClass'              => 'entity_class',
        'routeIdentifierName'      => 'route_identifier_name',
        'pageSize'                 => 'page_size',
        'pageSizeParam'            => 'page_size_param',
    ];

    /**
     * Allowed REST update options that are arrays
     *
     * @var array
     */
    protected $restArrayUpdateOptions = [
        'collectionHttpMethods'    => 'collection_http_methods',
        'collectionQueryWhitelist' => 'collection_query_whitelist',
        'entityHttpMethods'        => 'entity_http_methods',
    ];

    /**
     * @var FilterChain
     */
    protected $routeNameFilter;

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
        $this->modulePath     = $modules->getModulePath($this->module);
    }

    /**
     * Allow read-only access to properties
     *
     * @param  string $name
     * @return mixed
     * @throws \OutOfRangeException
     */
    public function __get($name)
    {
        if (! isset($this->{$name})) {
            throw new \OutOfRangeException(sprintf(
                'Cannot locate property by name of "%s"',
                $name
            ));
        }
        return $this->{$name};
    }

    /**
     * Set the EventManager instance
     *
     * @param  EventManagerInterface $events
     * @return $this
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers([
            __CLASS__,
            get_class($this),
        ]);
        $this->events = $events;
        return $this;
    }

    /**
     * Retrieve the EventManager instance
     *
     * Lazy instantiates one if none currently registered
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (! $this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }

    /**
     * @param  string $controllerService
     * @param  bool $isAFetchOperation If this is for a non-fetch operation,
     *     pass boolean false; allows listeners to include additional data
     *     necessary for clean updates.
     * @return RestServiceEntity|false
     * @throws Exception\RuntimeException
     */
    public function fetch($controllerService, $isAFetchOperation = true)
    {
        $config = $this->configResource->fetch(true);
        if (! isset($config['api-tools-rest'][$controllerService])) {
            throw new Exception\RuntimeException(sprintf(
                'Could not find REST resource by name of %s',
                $controllerService
            ), 404);
        }

        $restConfig = $config['api-tools-rest'][$controllerService];

        $restConfig['controllerServiceName'] = $controllerService;
        $restConfig['module']                = $this->module;
        $restConfig['resource_class']        = $restConfig['listener'];
        unset($restConfig['listener']);

        $entity = new RestServiceEntity();
        $entity->exchangeArray($restConfig);

        $this->getRouteInfo($entity, $config);
        $this->mergeContentNegotiationConfig($controllerService, $entity, $config);
        $this->mergeHalConfig($controllerService, $entity, $config);

        // Trigger an event, allowing a listener to alter the entity and/or
        // curry a new one.
        $eventResults = $this->getEventManager()->triggerEventUntil(function ($r) {
            return ($r instanceof RestServiceEntity);
        }, new Event(__FUNCTION__, $this, [
            'entity' => $entity,
            'config' => $config,
            'fetch'  => $isAFetchOperation,
        ]));

        if ($eventResults->stopped()) {
            $entity = $eventResults->last();
        }

        if (empty($entity->serviceName)) {
            $serviceName = $controllerService;
            $pattern = vsprintf(
                '#%sV[^%s]+%sRest%s(?P<service>[^%s]+)%sController#',
                array_fill(0, 6, preg_quote('\\'))
            );
            if (preg_match($pattern, $controllerService, $matches)) {
                $serviceName = $matches['service'];
            }
            $entity->exchangeArray([
                'service_name' => $serviceName,
            ]);
        }

        return $entity;
    }

    /**
     * Fetch all services
     *
     * @param int $version
     * @return RestServiceEntity[]
     * @throws Exception\RuntimeException
     */
    public function fetchAll($version = null)
    {
        $config = $this->configResource->fetch(true);
        if (! isset($config['api-tools-rest'])) {
            return [];
        }

        $services = [];
        $pattern  = false;

        // Initialize pattern if a version was passed and it's valid
        if (null !== $version) {
            $version = (int) $version;
            if (! in_array($version, $this->moduleEntity->getVersions(), true)) {
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

        foreach (array_keys($config['api-tools-rest']) as $controllerService) {
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
     * Create a new service using the details provided
     *
     * @param  RestServiceEntity $details
     * @return RestServiceEntity
     * @throws CreationException
     */
    public function createService(RestServiceEntity $details)
    {
        $serviceName = ucfirst($details->serviceName);

        if (! preg_match('/^[a-zA-Z][a-zA-Z0-9_]*(\\\[a-zA-Z][a-zA-Z0-9_]*)*$/', $serviceName)) {
            throw new CreationException('Invalid service name; must be a valid PHP namespace name.');
        }

        $entity = new RestServiceEntity();
        $entity->exchangeArray($details->getArrayCopy());

        $mediaType         = $this->createMediaType();
        $controllerService = ($details->controllerServiceName)
            ? $details->controllerServiceName
            : $this->createControllerServiceName($serviceName);
        $resourceClass     = ($details->resourceClass)
            ? $details->resourceClass
            : $this->createResourceClass($serviceName);
        $routeName         = ($details->routeName)
            ? $details->routeName
            : $this->createRoute($serviceName, $details->routeMatch, $details->routeIdentifierName, $controllerService);
        $collectionClass   = ($details->collectionClass)
            ? $details->collectionClass
            : $this->createCollectionClass($serviceName);
        $entityClass       = ($details->entityClass)
            ? $details->entityClass
            : $this->createEntityClass($serviceName, 'entity', $details);
        $module            = ($details->module)
            ? $details->module
            : $this->module;

        $entity->exchangeArray([
            'collection_class'        => $collectionClass,
            'controller_service_name' => $controllerService,
            'entity_class'            => $entityClass,
            'module'                  => $module,
            'resource_class'          => $resourceClass,
            'route_name'              => $routeName,
            'accept_whitelist'        => [
                $mediaType,
                'application/hal+json',
                'application/json',
            ],
            'content_type_whitelist'  => [
                $mediaType,
                'application/json',
            ],
        ]);

        $this->createRestConfig($entity, $controllerService, $resourceClass, $routeName);
        $this->createContentNegotiationConfig($entity, $controllerService);
        $this->createHalConfig($entity, $entityClass, $collectionClass, $routeName);

        return $entity;
    }

    /**
     * Update an existing service
     *
     * @param  RestServiceEntity $update
     * @return RestServiceEntity
     * @throws Exception\RuntimeException
     */
    public function updateService(RestServiceEntity $update)
    {
        $controllerService = $update->controllerServiceName;

        try {
            $original = $this->fetch($controllerService, false);
        } catch (Exception\RuntimeException $e) {
            throw new Exception\RuntimeException(sprintf(
                'Cannot update REST service "%s"; not found',
                $controllerService
            ), 404);
        }

        $this->updateRoute($original, $update);
        $this->updateRestConfig($original, $update);
        $this->updateContentNegotiationConfig($original, $update);
        $this->updateHalConfig($original, $update);

        return $this->fetch($controllerService, false);
    }

    /**
     * Delete a named service
     *
     * @todo   Remove content-negotiation and/or HAL configuration?
     * @param  string $controllerService
     * @param  bool   $recursive
     * @return true
     * @throws Exception\RuntimeException
     */
    public function deleteService($controllerService, $recursive = false)
    {
        try {
            $service = $this->fetch($controllerService);
        } catch (Exception\RuntimeException $e) {
            throw new Exception\RuntimeException(sprintf(
                'Cannot delete REST service "%s"; not found',
                $controllerService
            ), 404);
        }

        $this->deleteRoute($service);
        $this->deleteRestConfig($service);
        $this->deleteContentNegotiationConfig($service);
        $this->deleteContentValidationConfig($service);
        $this->deleteHalConfig($service);
        $this->deleteAuthorizationConfig($service);
        $this->deleteVersioningConfig($service);
        $this->deleteServiceManagerConfig($service);

        if ($recursive) {
            $reflection = new ReflectionClass($service->resourceClass);
            Utility::recursiveDelete(dirname($reflection->getFileName()));
        }
        return true;
    }

    /**
     * Generate the controller service name from the module and service name
     *
     * @param  string $serviceName
     * @return string
     */
    public function createControllerServiceName($serviceName)
    {
        return sprintf(
            '%s\\V%s\\Rest\\%s\\Controller',
            $this->module,
            $this->moduleEntity->getLatestVersion(),
            $serviceName
        );
    }

    public function createFactoryClass($serviceName)
    {
        $module  = $this->module;
        $srcPath = $this->getSourcePath($serviceName);

        $classResource = sprintf('%sResource', $serviceName);
        $className     = sprintf('%sResourceFactory', $serviceName);
        $classPath     = sprintf('%s/%s.php', $srcPath, $className);

        if (file_exists($classPath)) {
            throw new Exception\RuntimeException(sprintf(
                'The resource factory "%s" already exists',
                $className
            ));
        }

        $view = new ViewModel([
            'module'        => $module,
            'resource'      => $serviceName,
            'classfactory'  => $className,
            'classresource' => $classResource,
            'version'       => $this->moduleEntity->getLatestVersion(),
        ]);
        if (! $this->createClassFile($view, 'factory', $classPath)) {
            throw new Exception\RuntimeException(sprintf(
                'Unable to create resource factory "%s"; unable to write file',
                $className
            ));
        }

        $fullClassName = sprintf(
            '%s\\V%s\\Rest\\%s\\%s',
            $module,
            $this->moduleEntity->getLatestVersion(),
            $serviceName,
            $className
        );

        return $fullClassName;
    }

    /**
     * Creates a new resource class based on the specified service name
     *
     * @param  string $serviceName
     * @return string The name of the newly created class
     * @throws Exception\RuntimeException
     */
    public function createResourceClass($serviceName)
    {
        $module  = $this->module;
        $srcPath = $this->getSourcePath($serviceName);

        $className = sprintf('%sResource', $serviceName);
        $classPath = sprintf('%s/%s.php', $srcPath, $className);

        if (file_exists($classPath)) {
            throw new Exception\RuntimeException(sprintf(
                'The resource "%s" already exists',
                $className
            ));
        }

        $view = new ViewModel([
            'module'    => $module,
            'resource'  => $serviceName,
            'classname' => $className,
            'version'   => $this->moduleEntity->getLatestVersion(),
        ]);
        if (! $this->createClassFile($view, 'resource', $classPath)) {
            throw new Exception\RuntimeException(sprintf(
                'Unable to create resource "%s"; unable to write file',
                $className
            ));
        }

        $fullClassName = sprintf(
            '%s\\V%s\\Rest\\%s\\%s',
            $module,
            $this->moduleEntity->getLatestVersion(),
            $serviceName,
            $className
        );

        $factoryClassName = $this->createFactoryClass($serviceName);

        $this->configResource->patch([
            'service_manager' => [
                'factories' => [
                    $fullClassName => $factoryClassName,
                ],
            ],
        ], true);

        return $fullClassName;
    }

    /**
     * Create an entity class for the resource
     *
     * @param  string $serviceName
     * @param  string $template Which template to use; defaults to 'entity'
     * @param  RestServiceEntity $details
     * @return string The name of the newly created entity class
     * @throws Exception\RuntimeException
     */
    public function createEntityClass($serviceName, $template = 'entity', $details = null)
    {
        $module     = $this->module;
        $srcPath    = $this->getSourcePath($serviceName);

        $className = sprintf('%sEntity', $serviceName);
        $classPath = sprintf('%s/%s.php', $srcPath, $className);

        if (file_exists($classPath)) {
            throw new Exception\RuntimeException(sprintf(
                'The entity "%s" already exists',
                $className
            ));
        }

        $view = new ViewModel([
            'module'    => $module,
            'resource'  => $serviceName,
            'classname' => $className,
            'version'   => $this->moduleEntity->getLatestVersion(),
            'details'   => $details,
        ]);
        if (! $this->createClassFile($view, $template, $classPath)) {
            throw new Exception\RuntimeException(sprintf(
                'Unable to create entity "%s"; unable to write file',
                $className
            ));
        }

        $fullClassName = sprintf(
            '%s\\V%s\\Rest\\%s\\%s',
            $module,
            $this->moduleEntity->getLatestVersion(),
            $serviceName,
            $className
        );
        return $fullClassName;
    }

    /**
     * Create a collection class for the resource
     *
     * @param  string $serviceName
     * @return string The name of the newly created collection class
     * @throws Exception\RuntimeException
     */
    public function createCollectionClass($serviceName)
    {
        $module    = $this->module;
        $srcPath   = $this->getSourcePath($serviceName);

        $className = sprintf('%sCollection', $serviceName);
        $classPath = sprintf('%s/%s.php', $srcPath, $className);

        if (file_exists($classPath)) {
            throw new Exception\RuntimeException(sprintf(
                'The collection "%s" already exists',
                $className
            ));
        }

        $view = new ViewModel([
            'module'    => $module,
            'resource'  => $serviceName,
            'classname' => $className,
            'version'   => $this->moduleEntity->getLatestVersion(),
        ]);
        if (! $this->createClassFile($view, 'collection', $classPath)) {
            throw new Exception\RuntimeException(sprintf(
                'Unable to create entity "%s"; unable to write file',
                $className
            ));
        }

        $fullClassName = sprintf(
            '%s\\V%s\\Rest\\%s\\%s',
            $module,
            $this->moduleEntity->getLatestVersion(),
            $serviceName,
            $className
        );
        return $fullClassName;
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
        // Remove optional parameters in the route
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
     * @param  string $serviceName
     * @param  string $route
     * @param  string $routeIdentifier
     * @param  string $controllerService
     * @return string
     * @throws Exception\RuntimeException
     */
    public function createRoute($serviceName, $route, $routeIdentifier, $controllerService)
    {
        $filter    = $this->getRouteNameFilter();
        $routeName = sprintf(
            '%s.rest.%s',
            $filter->filter($this->module),
            $filter->filter($serviceName)
        );

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
                            'route' => sprintf('%s[/:%s]', $route, $routeIdentifier),
                            'defaults' => [
                                'controller' => $controllerService,
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
     * Create the mediatype for this
     *
     * Based on the module and the latest module version.
     *
     * @return string
     */
    public function createMediaType()
    {
        $filter = $this->getRouteNameFilter();
        return sprintf(
            'application/vnd.%s.v%s+json',
            $filter->filter($this->module),
            $this->moduleEntity->getLatestVersion()
        );
    }

    /**
     * Creates REST configuration
     *
     * @param  RestServiceEntity $details
     * @param  string $controllerService
     * @param  string $resourceClass
     * @param  string $routeName
     */
    public function createRestConfig(RestServiceEntity $details, $controllerService, $resourceClass, $routeName)
    {
        $config = ['api-tools-rest' => [
            $controllerService => [
                'listener'                   => $resourceClass,
                'route_name'                 => $routeName,
                'route_identifier_name'      => $details->routeIdentifierName,
                'collection_name'            => $details->collectionName,
                'entity_http_methods'        => $details->entityHttpMethods,
                'collection_http_methods'    => $details->collectionHttpMethods,
                'collection_query_whitelist' => $details->collectionQueryWhitelist,
                'page_size'                  => $details->pageSize,
                'page_size_param'            => $details->pageSizeParam,
                'entity_class'               => $details->entityClass,
                'collection_class'           => $details->collectionClass,
                'service_name'               => $details->serviceName,
            ],
        ]];
        $this->configResource->patch($config, true);
    }

    /**
     * Create content negotiation configuration based on payload and discovered
     * controller service name
     *
     * @param  RestServiceEntity $details
     * @param  string $controllerService
     */
    public function createContentNegotiationConfig(RestServiceEntity $details, $controllerService)
    {
        $config = [
            'controllers' => [
                $controllerService => $details->selector,
            ],
        ];
        $whitelist = $details->acceptWhitelist;
        if (! empty($whitelist)) {
            $config['accept_whitelist'] = [$controllerService => $whitelist];
        }
        $whitelist = $details->contentTypeWhitelist;
        if (! empty($whitelist)) {
            $config['content_type_whitelist'] = [$controllerService => $whitelist];
        }
        $config = ['api-tools-content-negotiation' => $config];
        $this->configResource->patch($config, true);
    }

    /**
     * Create HAL configuration
     *
     * @param  RestServiceEntity $details
     * @param  string $entityClass
     * @param  string $collectionClass
     * @param  string $routeName
     */
    public function createHalConfig(RestServiceEntity $details, $entityClass, $collectionClass, $routeName)
    {
        $config = ['api-tools-hal' => ['metadata_map' => [
            $entityClass => [
                'entity_identifier_name' => $details->entityIdentifierName,
                'route_name'             => $routeName,
                'route_identifier_name'  => $details->routeIdentifierName,
            ],
            $collectionClass => [
                'entity_identifier_name' => $details->entityIdentifierName,
                'route_name'             => $routeName,
                'route_identifier_name'  => $details->routeIdentifierName,
                'is_collection'          => true,
            ],
        ]]];
        if (! empty($details->hydratorName)) {
            $config['api-tools-hal']['metadata_map'][$entityClass]['hydrator'] = $details->hydratorName;
        }
        $this->configResource->patch($config, true);
    }

    /**
     * Update the route for an existing service
     *
     * @param  RestServiceEntity $original
     * @param  RestServiceEntity $update
     * @throws Exception\RuntimeException
     */
    public function updateRoute(RestServiceEntity $original, RestServiceEntity $update)
    {
        $route = $update->routeMatch;
        if (! $route) {
            return;
        }
        $routeName = $original->routeName;
        if ($this->routeAlreadyExist($route, $routeName)) {
            throw new Exception\RuntimeException(sprintf(
                'The route match "%s" already exists',
                $route
            ), 409);
        }
        $config    = ['router' => ['routes' => [
            $routeName => ['options' => [
                'route' => $route,
            ]],
        ]]];
        $this->configResource->patch($config, true);
    }

    /**
     * Update REST configuration
     *
     * @param  RestServiceEntity $original
     * @param  RestServiceEntity $update
     */
    public function updateRestConfig(RestServiceEntity $original, RestServiceEntity $update)
    {
        $patch = [];
        foreach ($this->restScalarUpdateOptions as $property => $configKey) {
            if (! $update->$property) {
                continue;
            }
            $patch[$configKey] = $update->$property;
        }

        if (empty($patch)) {
            goto updateArrayOptions;
        }

        $config = ['api-tools-rest' => [
            $original->controllerServiceName => $patch,
        ]];
        $this->configResource->patch($config, true);

        updateArrayOptions:

        foreach ($this->restArrayUpdateOptions as $property => $configKey) {
            $key = sprintf('api-tools-rest.%s.%s', $original->controllerServiceName, $configKey);
            $this->configResource->patchKey($key, $update->$property);
        }
    }

    /**
     * Update the content negotiation configuration for the service
     *
     * @param  RestServiceEntity $original
     * @param  RestServiceEntity $update
     */
    public function updateContentNegotiationConfig(RestServiceEntity $original, RestServiceEntity $update)
    {
        $baseKey = 'api-tools-content-negotiation.';
        $service = $original->controllerServiceName;

        if ($update->selector) {
            $key = $baseKey . 'controllers.' . $service;
            $this->configResource->patchKey($key, $update->selector);
        }

        // Array dereferencing is a PITA
        $acceptWhitelist = $update->acceptWhitelist;
        if (is_array($acceptWhitelist)
            && ! empty($acceptWhitelist)
        ) {
            $key = $baseKey . 'accept_whitelist.' . $service;
            $this->configResource->patchKey($key, $acceptWhitelist);
        }

        $contentTypeWhitelist = $update->contentTypeWhitelist;
        if (is_array($contentTypeWhitelist)
            && ! empty($contentTypeWhitelist)
        ) {
            $key = $baseKey . 'content_type_whitelist.' . $service;
            $this->configResource->patchKey($key, $contentTypeWhitelist);
        }
    }

    /**
     * Update HAL configuration
     *
     * @param  RestServiceEntity $original
     * @param  RestServiceEntity $update
     */
    public function updateHalConfig(RestServiceEntity $original, RestServiceEntity $update)
    {
        $baseKey = 'api-tools-hal.metadata_map.';

        $entityClass      = $update->entityClass     ?: $original->entityClass;
        $collectionClass  = $update->collectionClass ?: $original->collectionClass;
        $halConfig        = $this->getConfigForSubkey('api-tools-hal.metadata_map');

        $entityUpdated     = false;
        $collectionUpdated = false;

        // Do we have a new entity class?
        if (! isset($halConfig[$entityClass])) {
            $data = [$entityClass => [
                'entity_identifier_name' => $update->entityIdentifierName ?: $original->entityIdentifierName,
                'route_name'             => $update->routeName            ?: $original->routeName,
                'route_identifier_name'  => $update->routeIdentifierName  ?: $original->routeIdentifierName,
            ]];
            $hydratorName = $update->hydratorName ?: $original->hydratorName;
            if ($hydratorName) {
                $data[$entityClass]['hydrator'] = $hydratorName;
            }
            $data = ['api-tools-hal' => ['metadata_map' => $data]];
            $this->configResource->patch($data, true);
            $entityUpdated = true;
        }

        // Do we have a new collection class?
        if (! isset($halConfig[$collectionClass])) {
            $data = [$collectionClass => [
                'entity_identifier_name' => $update->entityIdentifierName ?: $original->entityIdentifierName,
                'route_name'             => $update->routeName            ?: $original->routeName,
                'route_identifier_name'  => $update->routeIdentifierName  ?: $original->routeIdentifierName,
                'is_collection'          => true,
            ]];
            $data = ['api-tools-hal' => ['metadata_map' => $data]];
            $this->configResource->patch($data, true);
            $collectionUpdated = true;
        }

        $entityUpdate     = [];
        $collectionUpdate = [];
        if ((! $entityUpdated && ! $collectionUpdated)
            && $update->routeIdentifierName
            && $update->routeIdentifierName !== $original->routeIdentifierName
        ) {
            $entityUpdate['route_identifier_name']     = $update->routeIdentifierName;
            $collectionUpdate['route_identifier_name'] = $update->routeIdentifierName;
        }

        if ((! $entityUpdated && ! $collectionUpdated)
            && $update->entityIdentifierName
            && $update->entityIdentifierName !== $original->entityIdentifierName
        ) {
            $entityUpdate['entity_identifier_name']     = $update->entityIdentifierName;
            $collectionUpdate['entity_identifier_name'] = $update->entityIdentifierName;
        }

        if ((! $entityUpdated && ! $collectionUpdated)
            && $update->routeName
            && $update->routeName !== $original->routeName
        ) {
            $entityUpdate['route_name']     = $update->routeName;
            $collectionUpdate['route_name'] = $update->routeName;
        }

        if (! $entityUpdated
            && $update->hydratorName
            && $update->hydratorName !== $original->hydratorName
        ) {
            $entityUpdate['hydrator'] = $update->hydratorName;
        }

        if (! $entityUpdated && ! empty($entityUpdate)) {
            $entityConfig = $this->getConfigForSubkey($baseKey . $entityClass);
            $update = ArrayUtils::merge($entityConfig, $entityUpdate);
            $key = $baseKey . $entityClass;
            $this->configResource->patchKey($key, $update);
        }

        if (! $collectionUpdated && ! empty($collectionUpdate)) {
            $collectionConfig = $this->getConfigForSubkey($baseKey . $collectionClass);
            $update = ArrayUtils::merge($collectionConfig, $collectionUpdate);
            $key = $baseKey . $collectionClass;
            $this->configResource->patchKey($key, $update);
        }
    }

    /**
     * Delete the route associated with the given service
     *
     * @param  RestServiceEntity $entity
     */
    public function deleteRoute(RestServiceEntity $entity)
    {
        $serviceName = $entity->controllerServiceName;
        if (false === strstr($serviceName, '\\V1\\')) {
            // service > v1; do not delete route
            return;
        }

        $route = $entity->routeName;
        $key   = ['router', 'routes', $route];
        $this->configResource->deleteKey($key);
    }

    /**
     * Delete the REST configuration associated with the given
     * service
     *
     * @param  RestServiceEntity $entity
     */
    public function deleteRestConfig(RestServiceEntity $entity)
    {
        $controllerService = $entity->controllerServiceName;
        $key = ['api-tools-rest', $controllerService];
        $this->configResource->deleteKey($key);
    }

    /**
     * Delete content-negotiation configuration associated with a service
     *
     * @param  RestServiceEntity $entity
     */
    public function deleteContentNegotiationConfig(RestServiceEntity $entity)
    {
        $controller = $entity->controllerServiceName;

        $key = ['api-tools-content-negotiation', 'controllers', $controller];
        $this->configResource->deleteKey($key);

        $key[1] = 'accept_whitelist';
        $this->configResource->deleteKey($key);

        $key[1] = 'content_type_whitelist';
        $this->configResource->deleteKey($key);
    }

    /**
     * Delete content-validation configuration associated with a service
     *
     * @param  RestServiceEntity $entity
     */
    public function deleteContentValidationConfig(RestServiceEntity $entity)
    {
        $controllerService = $entity->controllerServiceName;
        $key = ['api-tools-content-validation', $controllerService];
        $this->configResource->deleteKey($key);
    }

    /**
     * Delete HAL configuration for the service
     *
     * @param  RestServiceEntity $entity
     */
    public function deleteHalConfig(RestServiceEntity $entity)
    {
        $key = ['api-tools-hal', 'metadata_map'];
        $entityClass = $entity->entityClass;
        array_push($key, $entityClass);
        $this->configResource->deleteKey($key);

        $collectionClass = $entity->collectionClass;
        $key[2] = $collectionClass;
        $this->configResource->deleteKey($key);
    }

    /**
     * Delete any authorization configuration for a service
     *
     * @param  RestServiceEntity $entity
     */
    public function deleteAuthorizationConfig(RestServiceEntity $entity)
    {
        $controllerService = $entity->controllerServiceName;
        $key = ['api-tools-mvc-auth', 'authorization', $controllerService];
        $this->configResource->deleteKey($key);
    }

    /**
     * Delete versioning configuration for a service
     *
     * Removes the route name from api-tools-versioning.
     *
     * @param  RestServiceEntity $entity
     */
    public function deleteVersioningConfig(RestServiceEntity $entity)
    {
        $serviceName = $entity->controllerServiceName;
        if (false === strstr($serviceName, '\\V1\\')) {
            // service > v1; do not delete route
            return;
        }

        $config = $this->configResource->fetch(true);
        if (! isset($config['api-tools-versioning']['uri'])) {
            return;
        }

        $route = $entity->routeName;
        if (! in_array($route, $config['api-tools-versioning']['uri'], true)) {
            return;
        }

        $versioning = array_filter($config['api-tools-versioning']['uri'], function ($value) use ($route) {
            if ($route === $value) {
                return false;
            }
            return true;
        });

        $key = ['api-tools-versioning', 'uri'];
        $this->configResource->patchKey($key, $versioning);
    }

    /**
     * Delete any service manager configuration for the resource
     *
     * @param  RestServiceEntity $entity
     */
    public function deleteServiceManagerConfig(RestServiceEntity $entity)
    {
        $resourceClass = $entity->resourceClass;
        foreach (['invokables', 'factories'] as $serviceType) {
            $key = ['service_manager', $serviceType, $resourceClass];
            $this->configResource->deleteKey($key);
        }
    }

    /**
     * Create a class file
     *
     * Creates a class file based on the view model passed, the type of resource,
     * and writes it to the path provided.
     *
     * @param  ViewModel $model
     * @param  string $type
     * @param  string $classPath
     * @return bool
     */
    protected function createClassFile(ViewModel $model, $type, $classPath)
    {
        $renderer = $this->getRenderer();
        $template = $this->injectResolver($renderer, $type);
        $model->setTemplate($template);

        if (file_put_contents(
            $classPath,
            '<' . "?php\n" . $renderer->render($model)
        )) {
            return true;
        }

        return false;
    }

    /**
     * Get a renderer instance
     *
     * @return PhpRenderer
     */
    protected function getRenderer()
    {
        if ($this->renderer instanceof PhpRenderer) {
            return $this->renderer;
        }

        $this->renderer = new PhpRenderer();
        return $this->renderer;
    }

    /**
     * Inject the renderer with a resolver
     *
     * Seed the resolver with a template name and path based on the $type passed, and inject it
     * into the renderer.
     *
     * @param  PhpRenderer $renderer
     * @param  string $type
     * @return string Template name
     */
    protected function injectResolver(PhpRenderer $renderer, $type)
    {
        $template = sprintf('code-connected/rest-', $type);
        $path     = sprintf('%s/../../view/code-connected/rest-%s.phtml', __DIR__, $type);
        $resolver = new Resolver\TemplateMapResolver([
            $template => $path,
        ]);
        $renderer->setResolver($resolver);
        return $template;
    }

    /**
     * Get the source path for the module
     *
     * @param  string $serviceName
     * @return string
     */
    protected function getSourcePath($serviceName)
    {
        $sourcePath = $this->modules->getRestPath(
            $this->module,
            $this->moduleEntity->getLatestVersion(),
            $serviceName
        );

        if (! file_exists($sourcePath)) {
            mkdir($sourcePath, 0775, true);
        }

        return $sourcePath;
    }

    /**
     * Retrieve the filter chain for generating the route name
     *
     * @return FilterChain
     */
    protected function getRouteNameFilter()
    {
        if ($this->routeNameFilter instanceof FilterChain) {
            return $this->routeNameFilter;
        }

        $this->routeNameFilter = new FilterChain();
        $this->routeNameFilter->attachByName('WordCamelCaseToDash')
            ->attachByName('StringToLower');
        return $this->routeNameFilter;
    }

    /**
     * Retrieve route information for a given service based on the configuration available
     *
     * @param  RestServiceEntity $metadata
     * @param  array $config
     */
    protected function getRouteInfo(RestServiceEntity $metadata, array $config)
    {
        $routeName = $metadata->routeName;
        if (! $routeName
            || ! isset($config['router']['routes'][$routeName]['options']['route'])
        ) {
            return;
        }
        $metadata->exchangeArray([
            'route_match' => $config['router']['routes'][$routeName]['options']['route'],
        ]);
    }

    /**
     * Merge the content negotiation configuration for the given controller
     * service into the REST metadata
     *
     * @param  string $controllerServiceName
     * @param  RestServiceEntity $metadata
     * @param  array $config
     */
    protected function mergeContentNegotiationConfig($controllerServiceName, RestServiceEntity $metadata, array $config)
    {
        if (! isset($config['api-tools-content-negotiation'])) {
            return;
        }
        $config = $config['api-tools-content-negotiation'];

        if (isset($config['controllers'][$controllerServiceName])) {
            $metadata->exchangeArray([
                'selector' => $config['controllers'][$controllerServiceName],
            ]);
        }

        if (isset($config['accept_whitelist'][$controllerServiceName])) {
            $metadata->exchangeArray([
                'accept_whitelist' => $config['accept_whitelist'][$controllerServiceName],
            ]);
        }

        if (isset($config['content_type_whitelist'][$controllerServiceName])) {
            $metadata->exchangeArray([
                'content_type_whitelist' => $config['content_type_whitelist'][$controllerServiceName],
            ]);
        }
    }

    /**
     * Merge entity and collection class into metadata, if found
     *
     * @param  string $controllerServiceName
     * @param  RestServiceEntity $metadata
     * @param  array $config
     */
    protected function mergeHalConfig($controllerServiceName, RestServiceEntity $metadata, array $config)
    {
        if (! isset($config['api-tools-hal']['metadata_map'])) {
            return;
        }

        $entityClass     = $this->deriveEntityClass($controllerServiceName, $metadata, $config);
        $collectionClass = $this->deriveCollectionClass($controllerServiceName, $metadata, $config);

        $config = $config['api-tools-hal']['metadata_map'];
        $merge  = [];

        if (isset($config[$entityClass])) {
            $merge['entity_class'] = $entityClass;
        }

        if (isset($config[$entityClass]['entity_identifier_name'])) {
            $merge['entity_identifier_name'] = $config[$entityClass]['entity_identifier_name'];
        }

        if (isset($config[$entityClass]['hydrator'])) {
            $merge['hydrator_name'] = $config[$entityClass]['hydrator'];
        }

        if (isset($config[$collectionClass])) {
            $merge['collection_class'] = $collectionClass;
        }

        if (! isset($merge['entity_identifier_name']) && isset($config[$collectionClass]['entity_identifier_name'])) {
            $merge['entity_identifier_name'] = $config[$collectionClass]['entity_identifier_name'];
        }

        $metadata->exchangeArray($merge);
    }

    /**
     * Derive the name of the entity class from the controller service name
     *
     * @param  string $controllerServiceName
     * @param  RestServiceEntity $metadata
     * @param  array $config
     * @return string
     */
    protected function deriveEntityClass($controllerServiceName, RestServiceEntity $metadata, array $config)
    {
        if (isset($config['api-tools-rest'][$controllerServiceName]['entity_class'])) {
            return $config['api-tools-rest'][$controllerServiceName]['entity_class'];
        }

        $module = $metadata->module == $this->module ? $this->module : $metadata->module;
        $q = preg_quote('\\');
        $pattern = sprintf(
            '#%s(?P<version>%sV[a-zA-Z0-9]+)%sRest%s(?P<service>[^%s]+)%sController#',
            preg_quote($module),
            $q,
            $q,
            $q,
            $q,
            $q
        );
        if (! preg_match($pattern, $controllerServiceName, $matches)) {
            return null;
        }

        if (! empty($matches['version'])) {
            return sprintf(
                '%s%s\\Rest\\%s\\%sEntity',
                $module,
                $matches['version'],
                $matches['service'],
                $matches['service']
            );
        }

        return sprintf('%s\\Rest\\%s\\%sEntity', $module, $matches['service'], $matches['service']);
    }

    /**
     * Derive the name of the collection class from the controller service name
     *
     * @param  string $controllerServiceName
     * @param  RestServiceEntity $metadata
     * @param  array $config
     * @return string
     */
    protected function deriveCollectionClass($controllerServiceName, RestServiceEntity $metadata, array $config)
    {
        if (isset($config['api-tools-rest'][$controllerServiceName]['collection_class'])) {
            return $config['api-tools-rest'][$controllerServiceName]['collection_class'];
        }

        $module = $metadata->module == $this->module ? $this->module : $metadata->module;
        $q = preg_quote('\\');
        $pattern = sprintf(
            '#%s(?P<version>%sV[a-zA-Z0-9_]+)?%sRest%s(?P<service>[^%s]+)%sController#',
            preg_quote($module),
            $q,
            $q,
            $q,
            $q,
            $q
        );
        if (! preg_match($pattern, $controllerServiceName, $matches)) {
            return null;
        }

        if (! empty($matches['version'])) {
            return sprintf(
                '%s%s\\Rest\\%s\\%sCollection',
                $module,
                $matches['version'],
                $matches['service'],
                $matches['service']
            );
        }

        return sprintf('%s\\Rest\\%s\\%sCollection', $module, $matches['service'], $matches['service']);
    }

    /**
     * Traverse an array for a subkey
     *
     * Subkey is given in "." notation, which is then split, and
     * the configuration is traversed until no more keys are available,
     * or a corresponding entry is not found; in the latter case,
     * the $default will be provided.
     *
     * @param string $subKey
     * @param array|mixed $default
     * @return mixed
     */
    protected function getConfigForSubkey($subKey, $default = [])
    {
        $config = $this->configResource->fetch(true);
        $keys   = explode('.', $subKey);

        do {
            $key = array_shift($keys);
            if (! isset($config[$key])) {
                return $default;
            }
            $config = $config[$key];
        } while (count($keys));

        return $config;
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
