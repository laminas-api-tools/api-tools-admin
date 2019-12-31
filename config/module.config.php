<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

return array(
    'asset_manager' => array(
        'resolver_configs' => array(
            'paths' => array(
                __DIR__ . '/../asset/dist',
            ),
        ),
    ),

    'view_manager' => array(
        'template_map' => array(
            'laminas/app/app' => __DIR__ . '/../view/app.phtml',
        )
    ),

    'service_manager' => array(
        'factories' => array(
            'Laminas\ApiTools\Admin\Model\DocumentationModel' => 'Laminas\ApiTools\Admin\Model\DocumentationModelFactory',
            'Laminas\ApiTools\Admin\Model\FiltersModel' => 'Laminas\ApiTools\Admin\Model\FiltersModelFactory',
            'Laminas\ApiTools\Admin\Model\HydratorsModel' => 'Laminas\ApiTools\Admin\Model\HydratorsModelFactory',
            'Laminas\ApiTools\Admin\Model\ValidatorMetadataModel' => 'Laminas\ApiTools\Admin\Model\ValidatorMetadataModelFactory',
            'Laminas\ApiTools\Admin\Model\ValidatorsModel' => 'Laminas\ApiTools\Admin\Model\ValidatorsModelFactory',
            'Laminas\ApiTools\Admin\Model\InputFilterModel' => 'Laminas\ApiTools\Admin\Model\InputFilterModelFactory',
        ),
    ),

    'controllers' => array(
        'invokables' => array(
            'Laminas\ApiTools\Admin\Controller\App' => 'Laminas\ApiTools\Admin\Controller\AppController',
            'Laminas\ApiTools\Admin\Controller\CacheEnabled' => 'Laminas\ApiTools\Admin\Controller\CacheEnabledController',
        ),
        'factories' => array(
            'Laminas\ApiTools\Admin\Controller\Documentation' => 'Laminas\ApiTools\Admin\Controller\DocumentationControllerFactory',
            'Laminas\ApiTools\Admin\Controller\Filters' => 'Laminas\ApiTools\Admin\Controller\FiltersControllerFactory',
            'Laminas\ApiTools\Admin\Controller\Hydrators' => 'Laminas\ApiTools\Admin\Controller\HydratorsControllerFactory',
            'Laminas\ApiTools\Admin\Controller\Validators' => 'Laminas\ApiTools\Admin\Controller\ValidatorsControllerFactory',
            'Laminas\ApiTools\Admin\Controller\InputFilter' => 'Laminas\ApiTools\Admin\Controller\InputFilterControllerFactory',
        ),
    ),

    'router' => array(
        'routes' => array(
            'api-tools' => array(
                'child_routes' => array(
                    'ui' => array(
                        'type'  => 'Laminas\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route' => '/ui',
                            'defaults' => array(
                                'controller' => 'Laminas\ApiTools\Admin\Controller\App',
                                'action'     => 'app',
                            ),
                        ),
                    ),
                    'api' => array(
                        'type' => 'literal',
                        'options' => array(
                            'route' => '/api',
                            'defaults' => array(
                                'action' => false,
                            ),
                        ),
                        'may_terminate' => false,
                        'child_routes' => array(
                            'cache-enabled' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/cache-enabled',
                                    'defaults' => array(
                                        'controller' => 'Laminas\ApiTools\Admin\Controller\CacheEnabled',
                                        'action'     => 'cacheEnabled',
                                    ),
                                ),
                            ),
                            'config' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/config',
                                    'defaults' => array(
                                        'controller' => 'Laminas\ApiTools\Configuration\ConfigController',
                                        'action'     => 'process',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'module' => array(
                                        'type' => 'literal',
                                        'options' => array(
                                            'route' => '/module',
                                            'defaults' => array(
                                                'controller' => 'Laminas\ApiTools\Configuration\ModuleConfigController',
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'source' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/source',
                                    'defaults' => array(
                                        'controller' => 'Laminas\ApiTools\Admin\Controller\Source',
                                        'action'     => 'source',
                                    ),
                                ),
                            ),
                            'filters' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/filters',
                                    'defaults' => array(
                                        'controller' => 'Laminas\ApiTools\Admin\Controller\Filters',
                                        'action'     => 'filters',
                                    ),
                                ),
                            ),
                            'hydrators' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/hydrators',
                                    'defaults' => array(
                                        'controller' => 'Laminas\ApiTools\Admin\Controller\Hydrators',
                                        'action'     => 'hydrators',
                                    ),
                                ),
                            ),
                            'validators' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/validators',
                                    'defaults' => array(
                                        'controller' => 'Laminas\ApiTools\Admin\Controller\Validators',
                                        'action'     => 'validators',
                                    ),
                                ),
                            ),
                            'module-enable' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/module.enable',
                                    'defaults' => array(
                                        'controller' => 'Laminas\ApiTools\Admin\Controller\ModuleCreation',
                                        'action'     => 'apiEnable',
                                    ),
                                ),
                            ),
                            'versioning' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/versioning',
                                    'defaults' => array(
                                        'controller' => 'Laminas\ApiTools\Admin\Controller\Versioning',
                                        'action'     => 'versioning',
                                    ),
                                ),
                            ),
                            'default-version' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/default-version',
                                    'defaults' => array(
                                        'controller' => 'Laminas\ApiTools\Admin\Controller\Versioning',
                                        'action'     => 'defaultVersion',
                                    ),
                                ),
                            ),
                            'module' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/module[/:name]',
                                    'defaults' => array(
                                        'controller' => 'Laminas\ApiTools\Admin\Controller\Module',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'authorization' => array(
                                        'type' => 'literal',
                                        'options' => array(
                                            'route' => '/authorization',
                                            'defaults' => array(
                                                'controller' => 'Laminas\ApiTools\Admin\Controller\Authorization',
                                                'action'     => 'authorization',
                                            ),
                                        ),
                                    ),
                                    'rpc-service' => array(
                                        'type' => 'segment',
                                        'options' => array(
                                            'route' => '/rpc[/:controller_service_name]',
                                            'defaults' => array(
                                                'controller' => 'Laminas\ApiTools\Admin\Controller\RpcService',
                                                'controller_type' => 'rpc'
                                            ),
                                        ),
                                        'may_terminate' => true,
                                        'child_routes' => array(
                                            'input-filter' => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                    'route' => '/input-filter[/:input_filter_name]',
                                                    'defaults' => array(
                                                        'controller' => 'Laminas\ApiTools\Admin\Controller\InputFilter',
                                                        'action'     => 'index',
                                                    )
                                                )
                                            ),
                                            'doc' => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                    'route' => '/doc', // [/:http_method[/:http_direction]]
                                                    'defaults' => array(
                                                        'controller' => 'Laminas\ApiTools\Admin\Controller\Documentation',
                                                        'action'     => 'index',
                                                    )
                                                )
                                            )
                                        )
                                    ),
                                    'rest-service' => array(
                                        'type' => 'segment',
                                        'options' => array(
                                            'route' => '/rest[/:controller_service_name]',
                                            'defaults' => array(
                                                'controller' => 'Laminas\ApiTools\Admin\Controller\RestService',
                                                'controller_type' => 'rest'
                                            ),
                                        ),
                                        'may_terminate' => true,
                                        'child_routes' => array(
                                            'input-filter' => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                    'route' => '/input-filter[/:input_filter_name]',
                                                    'defaults' => array(
                                                        'controller' => 'Laminas\ApiTools\Admin\Controller\InputFilter',
                                                        'action'     => 'index',
                                                    )
                                                )
                                            ),
                                            'doc' => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                    'route' => '/doc', // [/:rest_resource_type[/:http_method[/:http_direction]]]
                                                    'defaults' => array(
                                                        'controller' => 'Laminas\ApiTools\Admin\Controller\Documentation',
                                                        'action'     => 'index',
                                                    )
                                                )
                                            )
                                        )
                                    ),
                                ),
                            ),
                            'authentication' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/authentication',
                                    'defaults' => array(
                                        'controller' => 'Laminas\ApiTools\Admin\Controller\Authentication',
                                        'action'     => 'authentication',
                                    ),
                                ),
                            ),
                            'db-adapter' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/db-adapter[/:adapter_name]',
                                    'defaults' => array(
                                        'controller' => 'Laminas\ApiTools\Admin\Controller\DbAdapter',
                                    ),
                                ),
                            ),
                            'content-negotiation' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/content-negotiation[/:content_name]',
                                    'defaults' => array(
                                        'controller' => 'Laminas\ApiTools\Admin\Controller\ContentNegotiation',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),

    'api-tools-content-negotiation' => array(
        'controllers' => array(
            'Laminas\ApiTools\Admin\Controller\Authentication'     => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\Authorization'      => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\CacheEnabled'       => 'Json',
            'Laminas\ApiTools\Admin\Controller\ContentNegotiation' => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\DbAdapter'          => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\Documentation'      => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\Filters'            => 'Json',
            'Laminas\ApiTools\Admin\Controller\Hydrators'          => 'Json',
            'Laminas\ApiTools\Admin\Controller\InputFilter'        => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\ModuleCreation'     => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\Module'             => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\RestService'        => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\RpcService'         => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\Source'             => 'Json',
            'Laminas\ApiTools\Admin\Controller\Validators'         => 'Json',
            'Laminas\ApiTools\Admin\Controller\Versioning'         => 'Json',
        ),
        'accept_whitelist' => array(
            'Laminas\ApiTools\Admin\Controller\Authentication' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\Authorization' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\CacheEnabled' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\ContentNegotiation' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\DbAdapter' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\Documentation' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\Filters' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\Hydrators' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\InputFilter' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\Module' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\ModuleCreation' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\Source' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\Validators' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\Versioning' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\RestService' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\RpcService' => array(
                'application/json',
                'application/*+json',
            ),
        ),
        'content_type_whitelist' => array(
            'Laminas\ApiTools\Admin\Controller\Authentication' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\Authorization' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\CacheEnabled' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\ContentNegotiation' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\DbAdapter' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\Filters' => array(
                'application/json',
            ),
            'Laminas\ApiTools\Admin\Controller\Hydrators' => array(
                'application/json',
            ),
            'Laminas\ApiTools\Admin\Controller\InputFilter' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\Module' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\ModuleCreation' => array(
                'application/json',
            ),
            'Laminas\ApiTools\Admin\Controller\Source' => array(
                'application/json',
            ),
            'Laminas\ApiTools\Admin\Controller\Validators' => array(
                'application/json',
            ),
            'Laminas\ApiTools\Admin\Controller\Versioning' => array(
                'application/json',
            ),
            'Laminas\ApiTools\Admin\Controller\RestService' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\RpcService' => array(
                'application/json',
                'application/*+json',
            ),
        ),
    ),

    'api-tools-hal' => array(
        'metadata_map' => array(
            'Laminas\ApiTools\Admin\Model\AuthenticationEntity' => array(
                'hydrator'        => 'ArraySerializable',
            ),
            'Laminas\ApiTools\Admin\Model\AuthorizationEntity' => array(
                'hydrator'        => 'ArraySerializable',
            ),
            'Laminas\ApiTools\Admin\Model\ContentNegotiationEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'content_name',
                'entity_identifier_name' => 'content_name',
                'route_name'      => 'api-tools/api/content-negotiation'
            ),
            'Laminas\ApiTools\Admin\Model\DbConnectedRestServiceEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'controller_service_name',
                'entity_identifier_name' => 'controller_service_name',
                'route_name'      => 'api-tools/api/module/rest-service',
            ),
            'Laminas\ApiTools\Admin\Model\DbAdapterEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'adapter_name',
                'entity_identifier_name' => 'adapter_name',
                'route_name'      => 'api-tools/api/db-adapter',
            ),
            'Laminas\ApiTools\Admin\Model\InputFilterCollection' => array(
                'route_name'      => 'api-tools/api/module/rest-service/input-filter',
                'is_collection'   => true,
                'collection_name' => 'input_filter',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ),
            'Laminas\ApiTools\Admin\Model\InputFilterEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
                'route_name'      => 'api-tools/api/module/rest-service/input-filter',
            ),
            'Laminas\ApiTools\Admin\Model\ModuleEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'name',
                'route_name'      => 'api-tools/api/module',
            ),
            'Laminas\ApiTools\Admin\Model\RestInputFilterCollection' => array(
                'route_name'      => 'api-tools/api/module/rest-service/input-filter',
                'is_collection'   => true,
                'collection_name' => 'input_filter',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ),
            'Laminas\ApiTools\Admin\Model\RestInputFilterEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'input_filter_name',
                'route_name'      => 'api-tools/api/module/rest-service/input-filter',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ),
            'Laminas\ApiTools\Admin\Model\DocumentationEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'rest_documentation',
                'entity_identifier_name' => 'rest_documentation',
                'route_name'      => 'api-tools/api/module/rest-service/rest-doc',
            ),
            'Laminas\ApiTools\Admin\Model\RestServiceEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'controller_service_name',
                'entity_identifier_name' => 'controller_service_name',
                'route_name'      => 'api-tools/api/module/rest-service',
                'links'           => array(
                    array(
                        'rel' => 'input_filter',
                        'route' => array(
                            'name' => 'api-tools/api/module/rest-service/input-filter'
                        ),
                    ),
                    array(
                        'rel' => 'documentation',
                        'route' => array(
                            'name' => 'api-tools/api/module/rest-service/doc',
                        ),
                    )
                ),
            ),
            'Laminas\ApiTools\Admin\Model\RpcInputFilterCollection' => array(
                'route_name'      => 'api-tools/api/module/rpc-service/input-filter',
                'is_collection'   => true,
                'collection_name' => 'input_filter',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ),
            'Laminas\ApiTools\Admin\Model\RpcInputFilterEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'input_filter_name',
                'route_name'      => 'api-tools/api/module/rpc-service/input-filter',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ),
            'Laminas\ApiTools\Admin\Model\RpcServiceEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'controller_service_name',
                'entity_identifier_name' => 'controller_service_name',
                'route_name'      => 'api-tools/api/module/rpc-service',
                'links'           => array(
                    array(
                        'rel' => 'input_filter',
                        'route' => array(
                            'name' => 'api-tools/api/module/rpc-service/input-filter'
                        ),
                    ),
                    array(
                        'rel' => 'documentation',
                        'route' => array(
                            'name' => 'api-tools/api/module/rpc-service/doc',
                        ),
                    )
                ),
            ),
        ),
    ),

    'api-tools-rest' => array(
        'Laminas\ApiTools\Admin\Controller\ContentNegotiation' => array(
            'listener'                => 'Laminas\ApiTools\Admin\Model\ContentNegotiationResource',
            'route_name'              => 'api-tools/api/content-negotiation',
            'route_identifier_name'   => 'content_name',
            'entity_class'            => 'Laminas\ApiTools\Admin\Model\ContentNegotiationEntity',
            'entity_http_methods'     => array('GET', 'PATCH', 'DELETE'),
            'collection_http_methods' => array('GET', 'POST'),
            'collection_name'         => 'selectors',
        ),
        'Laminas\ApiTools\Admin\Controller\DbAdapter' => array(
            'listener'                => 'Laminas\ApiTools\Admin\Model\DbAdapterResource',
            'route_name'              => 'api-tools/api/db-adapter',
            'route_identifier_name'   => 'adapter_name',
            'entity_class'            => 'Laminas\ApiTools\Admin\Model\DbAdapterEntity',
            'entity_http_methods'     => array('GET', 'PATCH', 'DELETE'),
            'collection_http_methods' => array('GET', 'POST'),
            'collection_name'         => 'db_adapter',
        ),
        'Laminas\ApiTools\Admin\Controller\Module' => array(
            'listener'                => 'Laminas\ApiTools\Admin\Model\ModuleResource',
            'route_name'              => 'api-tools/api/module',
            'route_identifier_name'   => 'name',
            'entity_class'            => 'Laminas\ApiTools\Admin\Model\ModuleEntity',
            'entity_http_methods'     => array('GET'),
            'collection_http_methods' => array('GET', 'POST'),
            'collection_name'         => 'module',
        ),
        'Laminas\ApiTools\Admin\Controller\RpcService' => array(
            'listener'                   => 'Laminas\ApiTools\Admin\Model\RpcServiceResource',
            'route_name'                 => 'api-tools/api/module/rpc-service',
            'entity_class'               => 'Laminas\ApiTools\Admin\Model\RpcServiceEntity',
            'route_identifier_name'      => 'controller_service_name',
            'entity_http_methods'        => array('GET', 'PATCH', 'DELETE'),
            'collection_http_methods'    => array('GET', 'POST'),
            'collection_name'            => 'rpc',
            'collection_query_whitelist' => array('version'),
        ),
        'Laminas\ApiTools\Admin\Controller\RestService' => array(
            'listener'                   => 'Laminas\ApiTools\Admin\Model\RestServiceResource',
            'route_name'                 => 'api-tools/api/module/rest-service',
            'entity_class'               => 'Laminas\ApiTools\Admin\Model\RestServiceEntity',
            'route_identifier_name'      => 'controller_service_name',
            'entity_http_methods'        => array('GET', 'PATCH', 'DELETE'),
            'collection_http_methods'    => array('GET', 'POST'),
            'collection_name'            => 'rest',
            'collection_query_whitelist' => array('version'),
        ),
    ),

    'api-tools-rpc' => array(
        // Dummy entry; still handled by ControllerManager, but this will force
        // it to show up in the list of RPC services
        'Laminas\ApiTools\Admin\Controller\Authentication' => array(
            'http_methods' => array('GET', 'POST', 'PATCH', 'DELETE'),
            'route_name'   => 'api-tools/api/authentication',
        ),
        'Laminas\ApiTools\Admin\Controller\Authorization' => array(
            'http_methods' => array('GET', 'PUT'),
            'route_name'   => 'api-tools/api/module/authorization',
        ),
        'Laminas\ApiTools\Admin\Controller\CacheEnabled' => array(
            'http_methods' => array('GET'),
            'route_name'   => 'api-tools/api/cache-enabled',
        ),
        'Laminas\ApiTools\Admin\Controller\Documentation' => array(
            'http_methods' => array('GET', 'PATCH', 'PUT', 'DELETE'),
            'route_name'   => 'api-tools/api/rest-service/rest-doc',
        ),
        'Laminas\ApiTools\Admin\Controller\Filters' => array(
            'http_methods' => array('GET'),
            'route_name'   => 'api-tools/api/filters',
        ),
        'Laminas\ApiTools\Admin\Controller\Hydrators' => array(
            'http_methods' => array('GET'),
            'route_name'   => 'api-tools/api/hydrators',
        ),
        'Laminas\ApiTools\Admin\Controller\InputFilter' => array(
            'http_methods' => array('GET', 'POST', 'PUT', 'DELETE'),
            'route_name'   => 'api-tools/api/rpc-service/input-filter',
        ),
        'Laminas\ApiTools\Admin\Controller\ModuleCreation' => array(
            'http_methods' => array('PUT'),
            'route_name'   => 'api-tools/api/module-enable',
        ),
        'Laminas\ApiTools\Admin\Controller\Source' => array(
            'http_methods' => array('GET'),
            'route_name'   => 'api-tools/api/source',
        ),
        'Laminas\ApiTools\Admin\Controller\Validators' => array(
            'http_methods' => array('GET'),
            'route_name'   => 'api-tools/api/validators',
        ),
        'Laminas\ApiTools\Admin\Controller\Versioning' => array(
            'http_methods' => array('PATCH'),
            'route_name'   => 'api-tools/api/versioning',
        ),
        'Laminas\ApiTools\Configuration\ConfigController'       => array(
            'http_methods' => array('GET', 'PATCH'),
            'route_name'   => 'api-tools/api/config',
        ),
        'Laminas\ApiTools\Configuration\ModuleConfigController' => array(
            'http_methods' => array('GET', 'PATCH'),
            'route_name'   => 'api-tools/api/config/module',
        ),
    ),

    /*
     * Metadata for scalar filter options.
     *
     * Each key in the map is a filter plugin name. The value is an array of
     * option key/type pairs. If more than one type is possible, the types are
     * OR'd.
     */
    'filter_metadata' => array(
        'Laminas\I18n\Filter\Alnum' => array(
            'allow_white_space' => 'bool',
            'locale' => 'string',
        ),
        'Laminas\I18n\Filter\Alpha' => array(
            'allow_white_space' => 'bool',
            'locale' => 'string',
        ),
        'Laminas\Filter\BaseName' => array(),
        'Laminas\Filter\Boolean' => array(
            'casting' => 'bool',
            'type' => 'string',
        ),
        'Laminas\Filter\Callback' => array(
            'callback' => 'string',
        ),
        'Laminas\Filter\Compress\Bz2' => array(
            'archive' => 'string',
            'blocksize' => 'int',
        ),
        'Laminas\Filter\Compress\Gz' => array(
            'archive' => 'string',
            'level' => 'int',
            'mode' => 'string',
        ),
        'Laminas\Filter\Compress\Llaminas' => array(),
        'Laminas\Filter\Compress' => array(
            'adapter' => 'string',
        ),
        'Laminas\Filter\Compress\Rar' => array(
            'archive' => 'string',
            'callback' => 'string',
            'password' => 'string',
            'target' => 'string',
        ),
        'Laminas\Filter\Compress\Snappy' => array(),
        'Laminas\Filter\Compress\Tar' => array(
            'archive' => 'string',
            'target' => 'string',
            'mode' => 'string',
        ),
        'Laminas\Filter\Compress\Zip' => array(
            'archive' => 'string',
            'target' => 'string',
        ),
        'Laminas\Filter\DateTimeForatter' => array(
            'format' => 'string',
        ),
        'Laminas\Filter\Decompress' => array(
            'adapter' => 'string',
        ),
        'Laminas\Filter\Decrypt' => array(
            'adapter' => 'string',
        ),
        'Laminas\Filter\Digits' => array(),
        'Laminas\Filter\Dir' => array(),
        'Laminas\Filter\Encrypt\BlockCipher' => array(
            'algorithm' => 'string',
            'compression' => 'string',
            'hash' => 'string',
            'key' => 'string',
            'key_iteration' => 'int',
            'vector' => 'string',
        ),
        'Laminas\Filter\Encrypt\Openssl' => array(
            'compression' => 'string',
            'package' => 'bool',
            'passphrase' => 'string',
        ),
        'Laminas\Filter\Encrypt' => array(
            'adapter' => 'string',
        ),
        'Laminas\Filter\File\Decrypt' => array(
            'adapter' => 'string',
            'filename' => 'string',
        ),
        'Laminas\Filter\File\Encrypt' => array(
            'adapter' => 'string',
            'filename' => 'string',
        ),
        'Laminas\Filter\File\LowerCase' => array(
            'encoding' => 'string',
        ),
        'Laminas\Filter\File\Rename' => array(
            'overwrite' => 'bool',
            'randomize' => 'bool',
            'source' => 'string',
            'target' => 'string',
        ),
        'Laminas\Filter\File\RenameUpload' => array(
            'overwrite' => 'bool',
            'randomize' => 'bool',
            'target' => 'string',
            'use_upload_extension' => 'bool',
            'use_upload_name' => 'bool',
        ),
        'Laminas\Filter\File\Uppercase' => array(
            'encoding' => 'string',
        ),
        'Laminas\Filter\HtmlEntities' => array(
            'charset' => 'string',
            'doublequote' => 'bool',
            'encoding' => 'string',
            'quotestyle' => 'int',
        ),
        'Laminas\Filter\Inflector' => array(
            'throwTargetExceptionsOn' => 'bool',
            'targetReplacementIdentifier' => 'string',
            'target' => 'string',
        ),
        'Laminas\Filter\Int' => array(),
        'Laminas\Filter\Null' => array(
            'type' => 'int|string',
        ),
        'Laminas\I18n\Filter\NumberFormat' => array(
            'locale' => 'string',
            'style' => 'int',
            'type' => 'int',
        ),
        'Laminas\I18n\Filter\NumberParse' => array(
            'locale' => 'string',
            'style' => 'int',
            'type' => 'int',
        ),
        'Laminas\Filter\PregReplace' => array(
            'pattern' => 'string',
            'replacement' => 'string',
        ),
        'Laminas\Filter\RealPath' => array(
            'exists' => 'bool',
        ),
        'Laminas\Filter\StringToLower' => array(
            'encoding' => 'string',
        ),
        'Laminas\Filter\StringToUpper' => array(
            'encoding' => 'string',
        ),
        'Laminas\Filter\StringTrim' => array(
            'charlist' => 'string',
        ),
        'Laminas\Filter\StripNewlines' => array(),
        'Laminas\Filter\StripTags' => array(
            'allowAttribs' => 'string',
            'allowTags' => 'string',
        ),
        'Laminas\Filter\UriNormalize' => array(
            'defaultscheme' => 'string',
            'enforcedscheme' => 'string',
        ),
        'Laminas\Filter\Word\CamelCaseToDash' => array(),
        'Laminas\Filter\Word\CamelCaseToSeparator' => array(
            'separator' => 'string',
        ),
        'Laminas\Filter\Word\CamelCaseToUnderscore' => array(),
        'Laminas\Filter\Word\DashToCamelCase' => array(),
        'Laminas\Filter\Word\DashToSeparator' => array(
            'separator' => 'string',
        ),
        'Laminas\Filter\Word\DashToUnderscore' => array(),
        'Laminas\Filter\Word\SeparatorToCamelCase' => array(
            'separator' => 'string',
        ),
        'Laminas\Filter\Word\SeparatorToDash' => array(
            'separator' => 'string',
        ),
        'Laminas\Filter\Word\SeparatorToSeparator' => array(
            'searchseparator' => 'string',
            'replacementseparator' => 'string',
        ),
        'Laminas\Filter\Word\UnderscoreToCamelCase' => array(),
        'Laminas\Filter\Word\UnderscoreToDash' => array(),
        'Laminas\Filter\Word\UnderscoreToSeparator' => array(
            'separator' => 'string',
        ),
    ),

    /*
     * Metadata for scalar validator options.
     *
     * Each key in the map is a validator plugin name. The value is an array of
     * option key/type pairs. If more than one type is possible, the types are
     * OR'd.
     *
     * The "__all__" key is a set of options that are true/available for all
     * validators.
     */
    'validator_metadata' => array(
        '__all__' => array(
            'breakchainonfailure' => 'bool',
            'message' => 'string',
            'messagelength' => 'int',
            'valueobscured' => 'bool',
            'translatortextdomain' => 'string',
            'translatorenabled' => 'bool',
        ),
        'Laminas\Validator\Barcode\Codabar' => array(),
        'Laminas\Validator\Barcode\Code128' => array(),
        'Laminas\Validator\Barcode\Code25interleaved' => array(),
        'Laminas\Validator\Barcode\Code25' => array(),
        'Laminas\Validator\Barcode\Code39ext' => array(),
        'Laminas\Validator\Barcode\Code39' => array(),
        'Laminas\Validator\Barcode\Code93ext' => array(),
        'Laminas\Validator\Barcode\Code93' => array(),
        'Laminas\Validator\Barcode\Ean12' => array(),
        'Laminas\Validator\Barcode\Ean13' => array(),
        'Laminas\Validator\Barcode\Ean14' => array(),
        'Laminas\Validator\Barcode\Ean18' => array(),
        'Laminas\Validator\Barcode\Ean2' => array(),
        'Laminas\Validator\Barcode\Ean5' => array(),
        'Laminas\Validator\Barcode\Ean8' => array(),
        'Laminas\Validator\Barcode\Gtin12' => array(),
        'Laminas\Validator\Barcode\Gtin13' => array(),
        'Laminas\Validator\Barcode\Gtin14' => array(),
        'Laminas\Validator\Barcode\Identcode' => array(),
        'Laminas\Validator\Barcode\Intelligentmail' => array(),
        'Laminas\Validator\Barcode\Issn' => array(),
        'Laminas\Validator\Barcode\Itf14' => array(),
        'Laminas\Validator\Barcode\Leitcode' => array(),
        'Laminas\Validator\Barcode' => array(
            'adapter' => 'string', // this is the validator adapter name to use
            'useChecksum' => 'bool',
        ),
        'Laminas\Validator\Barcode\Planet' => array(),
        'Laminas\Validator\Barcode\Postnet' => array(),
        'Laminas\Validator\Barcode\Royalmail' => array(),
        'Laminas\Validator\Barcode\Sscc' => array(),
        'Laminas\Validator\Barcode\Upca' => array(),
        'Laminas\Validator\Barcode\Upce' => array(),
        'Laminas\Validator\Between' => array(
            'inclusive' => 'bool',
            'max' => 'int',
            'min' => 'int',
        ),
        'Laminas\Validator\Bitwise' => array(
            'control' => 'int',
            'operator' => 'string',
            'strict' => 'bool',
        ),
        'Laminas\Validator\Callback' => array(
            'callback' => 'string',
        ),
        'Laminas\Validator\CreditCard' => array(
            'type' => 'string',
            'service' => 'string',
        ),
        'Laminas\Validator\Csrf' => array(
            'name' => 'string',
            'salt' => 'string',
            'timeout' => 'int',
        ),
        'Laminas\Validator\Date' => array(
            'format' => 'string',
        ),
        'Laminas\Validator\DateStep' => array(
            'format' => 'string',
            'basevalue' => 'string|int',
        ),
        'Laminas\Validator\Db\NoRecordExists' => array(
            'table' => 'string',
            'schema' => 'string',
            'field' => 'string',
            'exclude' => 'string',
        ),
        'Laminas\Validator\Db\RecordExists' => array(
            'table' => 'string',
            'schema' => 'string',
            'field' => 'string',
            'exclude' => 'string',
        ),
        'Laminas\ApiTools\ContentValidation\Validator\DbNoRecordExists' => array(
            'adapter' => 'string',
            'table' => 'string',
            'schema' => 'string',
            'field' => 'string',
            'exclude' => 'string',
        ),
        'Laminas\ApiTools\ContentValidation\Validator\DbRecordExists' => array(
            'adapter' => 'string',
            'table' => 'string',
            'schema' => 'string',
            'field' => 'string',
            'exclude' => 'string',
        ),
        'Laminas\Validator\Digits' => array(),
        'Laminas\Validator\EmailAddress' => array(
            'allow' => 'int',
            'useMxCheck' => 'bool',
            'useDeepMxCheck' => 'bool',
            'useDomainCheck' => 'bool',
        ),
        'Laminas\Validator\Explode' => array(
            'valuedelimiter' => 'string',
            'breakonfirstfailure' => 'bool',
        ),
        'Laminas\Validator\File\Count' => array(
            'max' => 'int',
            'min' => 'int',
        ),
        'Laminas\Validator\File\Crc32' => array(
            'algorithm' => 'string',
            'hash' => 'string',
            'crc32' => 'string',
        ),
        'Laminas\Validator\File\ExcludeExtension' => array(
            'case' => 'bool',
            'extension' => 'string',
        ),
        'Laminas\Validator\File\ExcludeMimeType' => array(
            'disableMagicFile' => 'bool',
            'magicFile' => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType' => 'string',
        ),
        'Laminas\Validator\File\Exists' => array(
            'directory' => 'string',
        ),
        'Laminas\Validator\File\Extension' => array(
            'case' => 'bool',
            'extension' => 'string',
        ),
        'Laminas\Validator\File\FilesSize' => array(
            'max' => 'int',
            'min' => 'int',
            'size' => 'int',
            'useByteString' => 'bool',
        ),
        'Laminas\Validator\File\Hash' => array(
            'algorithm' => 'string',
            'hash' => 'string',
        ),
        'Laminas\Validator\File\ImageSize' => array(
            'maxHeight' => 'int',
            'minHeight' => 'int',
            'maxWidth' => 'int',
            'minWidth' => 'int',
        ),
        'Laminas\Validator\File\IsCompressed' => array(
            'disableMagicFile' => 'bool',
            'magicFile' => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType' => 'string',
        ),
        'Laminas\Validator\File\IsImage' => array(
            'disableMagicFile' => 'bool',
            'magicFile' => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType' => 'string',
        ),
        'Laminas\Validator\File\Md5' => array(
            'algorithm' => 'string',
            'hash' => 'string',
            'md5' => 'string',
        ),
        'Laminas\Validator\File\MimeType' => array(
            'disableMagicFile' => 'bool',
            'magicFile' => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType' => 'string',
        ),
        'Laminas\Validator\File\NotExists' => array(
            'directory' => 'string',
        ),
        'Laminas\Validator\File\Sha1' => array(
            'algorithm' => 'string',
            'hash' => 'string',
            'sha1' => 'string',
        ),
        'Laminas\Validator\File\Size' => array(
            'max' => 'int',
            'min' => 'int',
            'size' => 'int',
            'useByteString' => 'bool',
        ),
        'Laminas\Validator\File\UploadFile' => array(),
        'Laminas\Validator\File\Upload' => array(),
        'Laminas\Validator\File\WordCount' => array(
            'max' => 'int',
            'min' => 'int',
        ),
        'Laminas\Validator\GreaterThan' => array(
            'inclusive' => 'bool',
            'min' => 'int',
        ),
        'Laminas\Validator\Hex' => array(),
        'Laminas\Validator\Hostname' => array(
            'allow' => 'int',
            'useIdnCheck' => 'bool',
            'useTldCheck' => 'bool',
        ),
        'Laminas\Validator\Iban' => array(
            'country_code' => 'string',
            'allow_non_sepa' => 'bool',
        ),
        'Laminas\Validator\Identical' => array(
            'literal' => 'bool',
            'strict' => 'bool',
            'token' => 'string',
        ),
        'Laminas\Validator\InArray' => array(
            'strict' => 'bool',
            'recursive' => 'bool',
        ),
        'Laminas\Validator\Ip' => array(
            'allowipv4' => 'bool',
            'allowipv6' => 'bool',
            'allowipvfuture' => 'bool',
            'allowliteral' => 'bool',
        ),
        'Laminas\Validator\Isbn' => array(
            'type' => 'string',
            'separator' => 'string',
        ),
        'Laminas\Validator\IsInstanceOf' => array(
            'classname' => 'string',
        ),
        'Laminas\Validator\LessThan' => array(
            'inclusive' => 'bool',
            'max' => 'int',
        ),
        'Laminas\Validator\NotEmpty' => array(
            'type' => 'int',
        ),
        'Laminas\Validator\Regex' => array(
            'pattern' => 'string',
        ),
        'Laminas\Validator\Sitemap\Changefreq' => array(),
        'Laminas\Validator\Sitemap\Lastmod' => array(),
        'Laminas\Validator\Sitemap\Loc' => array(),
        'Laminas\Validator\Sitemap\Priority' => array(),
        'Laminas\Validator\Step' => array(
            'baseValue' => 'int|float',
            'step' => 'float',
        ),
        'Laminas\Validator\StringLength' => array(
            'max' => 'int',
            'min' => 'int',
            'encoding' => 'string',
        ),
        'Laminas\Validator\Uri' => array(
            'allowAbsolute' => 'bool',
            'allowRelative' => 'bool',
        ),
        'Laminas\I18n\Validator\Alnum' => array(
            'allowwhitespace' => 'bool',
        ),
        'Laminas\I18n\Validator\Alpha' => array(
            'allowwhitespace' => 'bool',
        ),
        'Laminas\I18n\Validator\DateTime' => array(
            'calendar' => 'int',
            'datetype' => 'int',
            'pattern' => 'string',
            'timetype' => 'int',
            'timezone' => 'string',
            'locale' => 'string',
        ),
        'Laminas\I18n\Validator\Float' => array(
            'locale' => 'string',
        ),
        'Laminas\I18n\Validator\Int' => array(
            'locale' => 'string',
        ),
        'Laminas\I18n\Validator\PhoneNumber' => array(
            'country' => 'string',
            'allow_possible' => 'bool',
        ),
        'Laminas\I18n\Validator\PostCode' => array(
            'locale' => 'string',
            'format' => 'string',
            'service' => 'string',
        ),
    ),

    'input_filters' => array(
        'Laminas\ApiTools\Admin\ModuleName\Validator' => array(
            array(
                'name' => 'name',
                'validators' => array(
                    array(
                        'name' => 'regex',
                        'options' => array(
                            'pattern' => '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/',
                            'message' => 'Invalid API name; must be a valid PHP namespace name',
                        ),
                    ),
                ),
            ),
        ),
    ),

    'api-tools-content-validation' => array(
        'Laminas\ApiTools\Admin\Controller\Module' => array(
            'input_filter' => 'Laminas\ApiTools\Admin\ModuleName\Validator',
        ),
    ),
);
