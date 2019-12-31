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
                __DIR__ . '/../asset',
            ),
        ),
    ),

    'view_manager' => array(
        'template_map' => array(
        'laminas/app/app' => __DIR__ . '/../view/app.phtml',
        )
    ),

    'controllers' => array(
        'invokables' => array(
            'Laminas\ApiTools\Admin\Controller\App' => 'Laminas\ApiTools\Admin\Controller\AppController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'api-tools-admin' => array(
                'type'  => 'Laminas\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin',
                    'defaults' => array(
                        'controller' => 'Laminas\ApiTools\Admin\Controller\App',
                        'action'     => 'app',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
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
                                        'action'     => 'source'
                                    )
                                )
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
                                            ),
                                        ),
                                    ),
                                    'rest-service' => array(
                                        'type' => 'segment',
                                        'options' => array(
                                            'route' => '/rest[/:controller_service_name]',
                                            'defaults' => array(
                                                'controller' => 'Laminas\ApiTools\Admin\Controller\RestService',
                                            ),
                                        ),
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
                        ),
                    ),
                ),
            ),
        ),
    ),

    'api-tools-content-negotiation' => array(
        'controllers' => array(
            'Laminas\ApiTools\Admin\Controller\Authentication' => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\Authorization'  => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\DbAdapter'      => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\ModuleCreation' => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\Module'         => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\RestService'    => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\RpcService'     => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\Source'         => 'Json',
            'Laminas\ApiTools\Admin\Controller\Versioning'     => 'Json',
        ),
        'accept-whitelist' => array(
            'Laminas\ApiTools\Admin\Controller\Authentication' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\Authorization' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\DbAdapter' => array(
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
        'content-type-whitelist' => array(
            'Laminas\ApiTools\Admin\Controller\Authentication' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\Authorization' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\ApiTools\Admin\Controller\DbAdapter' => array(
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
            'Laminas\ApiTools\Admin\Model\DbConnectedRestServiceEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'controller_service_name',
                'route_name'      => 'api-tools-admin/api/module/rest-service',
            ),
            'Laminas\ApiTools\Admin\Model\DbAdapterEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'adapter_name',
                'route_name'      => 'api-tools-admin/api/db-adapter',
            ),
            'Laminas\ApiTools\Admin\Model\ModuleEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'name',
                'route_name'      => 'api-tools-admin/api/module',
            ),
            'Laminas\ApiTools\Admin\Model\RestServiceEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'controller_service_name',
                'route_name'      => 'api-tools-admin/api/module/rest-service',
            ),
            'Laminas\ApiTools\Admin\Model\RpcServiceEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'controller_service_name',
                'route_name'      => 'api-tools-admin/api/module/rpc-service',
            ),
        ),
    ),

    'api-tools-rest' => array(
        'Laminas\ApiTools\Admin\Controller\DbAdapter' => array(
            'listener'                => 'Laminas\ApiTools\Admin\Model\DbAdapterResource',
            'route_name'              => 'api-tools-admin/api/db-adapter',
            'identifier_name'         => 'adapter_name',
            'entity_class'            => 'Laminas\ApiTools\Admin\Model\DbAdapterEntity',
            'resource_http_methods'   => array('GET', 'PATCH', 'DELETE'),
            'collection_http_methods' => array('GET', 'POST'),
            'collection_name'         => 'db_adapter',
        ),
        'Laminas\ApiTools\Admin\Controller\Module' => array(
            'listener'                => 'Laminas\ApiTools\Admin\Model\ModuleResource',
            'route_name'              => 'api-tools-admin/api/module',
            'identifier_name'         => 'name',
            'entity_class'            => 'Laminas\ApiTools\Admin\Model\ModuleEntity',
            'resource_http_methods'   => array('GET'),
            'collection_http_methods' => array('GET', 'POST'),
            'collection_name'         => 'module',
        ),
        'Laminas\ApiTools\Admin\Controller\RpcService' => array(
            'listener'                   => 'Laminas\ApiTools\Admin\Model\RpcServiceResource',
            'route_name'                 => 'api-tools-admin/api/module/rpc-service',
            'entity_class'               => 'Laminas\ApiTools\Admin\Model\RpcServiceEntity',
            'identifier_name'            => 'controller_service_name',
            'resource_http_methods'      => array('GET', 'PATCH', 'DELETE'),
            'collection_http_methods'    => array('GET', 'POST'),
            'collection_name'            => 'rpc',
            'collection_query_whitelist' => array('version'),
        ),
        'Laminas\ApiTools\Admin\Controller\RestService' => array(
            'listener'                   => 'Laminas\ApiTools\Admin\Model\RestServiceResource',
            'route_name'                 => 'api-tools-admin/api/module/rest-service',
            'entity_class'               => 'Laminas\ApiTools\Admin\Model\RestServiceEntity',
            'identifier_name'            => 'controller_service_name',
            'resource_http_methods'      => array('GET', 'PATCH', 'DELETE'),
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
            'route_name'   => 'api-tools-admin/api/authentication',
        ),
        'Laminas\ApiTools\Admin\Controller\Authorization' => array(
            'http_methods' => array('GET', 'PUT'),
            'route_name'   => 'api-tools-admin/api/module/authorization',
        ),
        'Laminas\ApiTools\Admin\Controller\ModuleCreation' => array(
            'http_methods' => array('PUT'),
            'route_name'   => 'api-tools-admin/api/module-enable',
        ),
        'Laminas\ApiTools\Admin\Controller\Source' => array(
            'http_methods' => array('GET'),
            'route_name'   => 'api-tools-admin/api/source',
        ),
        'Laminas\ApiTools\Admin\Controller\Versioning' => array(
            'http_methods' => array('PATCH'),
            'route_name'   => 'api-tools-admin/api/versioning',
        ),
        'Laminas\ApiTools\Configuration\ConfigController'       => array(
            'http_methods' => array('GET', 'PATCH'),
            'route_name'   => 'api-tools-admin/api/config',
        ),
        'Laminas\ApiTools\Configuration\ModuleConfigController' => array(
            'http_methods' => array('GET', 'PATCH'),
            'route_name'   => 'api-tools-admin/api/config/module',
        ),
    ),
);
