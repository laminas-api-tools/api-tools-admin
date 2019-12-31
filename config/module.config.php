<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin;

use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'api-tools-admin' => [
        // path_spec defines whether modules should be created using PSR-0
        //     or PSR-4 module structure; the default is to use PSR-0.
        //     Valid values are:
        //     - Laminas\ApiTools\Admin\Model\ModulePathSpec::PSR_0 ("psr-0")
        //     - Laminas\ApiTools\Admin\Model\ModulePathSpec::PSR_4 ("psr-4")
        // 'path_spec' => 'psr-0',
    ],

    'service_manager' => [
        // Legacy Zend Framework aliases
        'aliases' => [
            \ZF\Apigility\Admin\Listener\CryptFilterListener::class => Listener\CryptFilterListener::class,
            \ZF\Apigility\Admin\Listener\DisableHttpCacheListener::class => Listener\DisableHttpCacheListener::class,
            \ZF\Apigility\Admin\Listener\EnableHalRenderCollectionsListener::class => Listener\EnableHalRenderCollectionsListener::class,
            \ZF\Apigility\Admin\Listener\InjectModuleResourceLinksListener::class => Listener\InjectModuleResourceLinksListener::class,
            \ZF\Apigility\Admin\Listener\NormalizeMatchedControllerServiceNameListener::class => Listener\NormalizeMatchedControllerServiceNameListener::class,
            \ZF\Apigility\Admin\Listener\NormalizeMatchedInputFilterNameListener::class => Listener\NormalizeMatchedInputFilterNameListener::class,
            \ZF\Apigility\Admin\Model\AuthenticationModel::class => Model\AuthenticationModel::class,
            \ZF\Apigility\Admin\Model\AuthorizationModelFactory::class => Model\AuthorizationModelFactory::class,
            \ZF\Apigility\Admin\Model\ContentNegotiationModel::class => Model\ContentNegotiationModel::class,
            \ZF\Apigility\Admin\Model\ContentNegotiationResource::class => Model\ContentNegotiationResource::class,
            \ZF\Apigility\Admin\Model\DbAdapterModel::class => Model\DbAdapterModel::class,
            \ZF\Apigility\Admin\Model\DbAdapterResource::class => Model\DbAdapterResource::class,
            \ZF\Apigility\Admin\Model\DbAutodiscoveryModel::class => Model\DbAutodiscoveryModel::class,
            \ZF\Apigility\Admin\Model\DoctrineAdapterModel::class => Model\DoctrineAdapterModel::class,
            \ZF\Apigility\Admin\Model\DoctrineAdapterResource::class => Model\DoctrineAdapterResource::class,
            \ZF\Apigility\Admin\Model\DocumentationModel::class => Model\DocumentationModel::class,
            \ZF\Apigility\Admin\Model\FiltersModel::class => Model\FiltersModel::class,
            \ZF\Apigility\Admin\Model\HydratorsModel::class => Model\HydratorsModel::class,
            \ZF\Apigility\Admin\Model\InputFilterModel::class => Model\InputFilterModel::class,
            \ZF\Apigility\Admin\Model\ModuleModel::class => Model\ModuleModel::class,
            \ZF\Apigility\Admin\Model\ModulePathSpec::class => Model\ModulePathSpec::class,
            \ZF\Apigility\Admin\Model\ModuleResource::class => Model\ModuleResource::class,
            \ZF\Apigility\Admin\Model\ModuleVersioningModelFactory::class => Model\ModuleVersioningModelFactory::class,
            \ZF\Apigility\Admin\Model\RestServiceModelFactory::class => Model\RestServiceModelFactory::class,
            \ZF\Apigility\Admin\Model\RestServiceResource::class => Model\RestServiceResource::class,
            \ZF\Apigility\Admin\Model\RpcServiceModelFactory::class => Model\RpcServiceModelFactory::class,
            \ZF\Apigility\Admin\Model\RpcServiceResource::class => Model\RpcServiceResource::class,
            \ZF\Apigility\Admin\Model\ValidatorMetadataModel::class => Model\ValidatorMetadataModel::class,
            \ZF\Apigility\Admin\Model\ValidatorsModel::class => Model\ValidatorsModel::class,
            \ZF\Apigility\Admin\Model\VersioningModelFactory::class => Model\VersioningModelFactory::class,
        ],
        'factories' => [
            // @codingStandardsIgnoreStart
            Listener\CryptFilterListener::class                           => InvokableFactory::class,
            Listener\DisableHttpCacheListener::class                      => InvokableFactory::class,
            Listener\EnableHalRenderCollectionsListener::class            => InvokableFactory::class,
            Listener\InjectModuleResourceLinksListener::class             => Listener\InjectModuleResourceLinksListenerFactory::class,
            Listener\NormalizeMatchedControllerServiceNameListener::class => InvokableFactory::class,
            Listener\NormalizeMatchedInputFilterNameListener::class       => InvokableFactory::class,
            Model\AuthenticationModel::class                              => Model\AuthenticationModelFactory::class,
            Model\AuthorizationModelFactory::class                        => Model\AuthorizationModelFactoryFactory::class,
            Model\ContentNegotiationModel::class                          => Model\ContentNegotiationModelFactory::class,
            Model\ContentNegotiationResource::class                       => Model\ContentNegotiationResourceFactory::class,
            Model\DbAdapterModel::class                                   => Model\DbAdapterModelFactory::class,
            Model\DbAdapterResource::class                                => Model\DbAdapterResourceFactory::class,
            Model\DbAutodiscoveryModel::class                             => Model\DbAutodiscoveryModelFactory::class,
            Model\DoctrineAdapterModel::class                             => Model\DoctrineAdapterModelFactory::class,
            Model\DoctrineAdapterResource::class                          => Model\DoctrineAdapterResourceFactory::class,
            Model\DocumentationModel::class                               => Model\DocumentationModelFactory::class,
            Model\FiltersModel::class                                     => Model\FiltersModelFactory::class,
            Model\HydratorsModel::class                                   => Model\HydratorsModelFactory::class,
            Model\InputFilterModel::class                                 => Model\InputFilterModelFactory::class,
            Model\ModuleModel::class                                      => Model\ModuleModelFactory::class,
            Model\ModulePathSpec::class                                   => Model\ModulePathSpecFactory::class,
            Model\ModuleResource::class                                   => Model\ModuleResourceFactory::class,
            Model\ModuleVersioningModelFactory::class                     => Model\ModuleVersioningModelFactoryFactory::class,
            Model\RestServiceModelFactory::class                          => Model\RestServiceModelFactoryFactory::class,
            Model\RestServiceResource::class                              => Model\RestServiceResourceFactory::class,
            Model\RpcServiceModelFactory::class                           => Model\RpcServiceModelFactoryFactory::class,
            Model\RpcServiceResource::class                               => Model\RpcServiceResourceFactory::class,
            Model\ValidatorMetadataModel::class                           => Model\ValidatorMetadataModelFactory::class,
            Model\ValidatorsModel::class                                  => Model\ValidatorsModelFactory::class,
            Model\VersioningModelFactory::class                           => Model\VersioningModelFactoryFactory::class,
            // @codingStandardsIgnoreEnd
        ],
    ],

    'controllers' => [
        'aliases' => [
            Controller\App::class                      => Controller\AppController::class,
            Controller\Authentication::class           => Controller\AuthenticationController::class,
            Controller\Authorization::class            => Controller\AuthorizationController::class,
            Controller\CacheEnabled::class             => Controller\CacheEnabledController::class,
            Controller\Config::class                   => Controller\ConfigController::class,
            Controller\FsPermissions::class            => Controller\FsPermissionsController::class,
            Controller\HttpBasicAuthentication::class  => Controller\Authentication::class,
            Controller\HttpDigestAuthentication::class => Controller\Authentication::class,
            Controller\ModuleConfig::class             => Controller\ModuleConfigController::class,
            Controller\ModuleCreation::class           => Controller\ModuleCreationController::class,
            Controller\OAuth2Authentication::class     => Controller\Authentication::class,
            Controller\Package::class                  => Controller\PackageController::class,
            Controller\Source::class                   => Controller\SourceController::class,
            Controller\Versioning::class               => Controller\VersioningController::class,

            // Legacy Zend Framework aliases
            \ZF\Apigility\Admin\Controller\App::class => Controller\App::class,
            \ZF\Apigility\Admin\Controller\Authentication::class => Controller\Authentication::class,
            \ZF\Apigility\Admin\Controller\Authorization::class => Controller\Authorization::class,
            \ZF\Apigility\Admin\Controller\CacheEnabled::class => Controller\CacheEnabled::class,
            \ZF\Apigility\Admin\Controller\Config::class => Controller\Config::class,
            \ZF\Apigility\Admin\Controller\FsPermissions::class => Controller\FsPermissions::class,
            \ZF\Apigility\Admin\Controller\HttpBasicAuthentication::class => Controller\HttpBasicAuthentication::class,
            \ZF\Apigility\Admin\Controller\HttpDigestAuthentication::class => Controller\HttpDigestAuthentication::class,
            \ZF\Apigility\Admin\Controller\ModuleConfig::class => Controller\ModuleConfig::class,
            \ZF\Apigility\Admin\Controller\ModuleCreation::class => Controller\ModuleCreation::class,
            \ZF\Apigility\Admin\Controller\OAuth2Authentication::class => Controller\OAuth2Authentication::class,
            \ZF\Apigility\Admin\Controller\Package::class => Controller\Package::class,
            \ZF\Apigility\Admin\Controller\Source::class => Controller\Source::class,
            \ZF\Apigility\Admin\Controller\Versioning::class => Controller\Versioning::class,
            \ZF\Apigility\Admin\Controller\ApigilityVersionController::class => Controller\ApiToolsVersionController::class,
            \ZF\Apigility\Admin\Controller\AppController::class => Controller\AppController::class,
            \ZF\Apigility\Admin\Controller\AuthenticationController::class => Controller\AuthenticationController::class,
            \ZF\Apigility\Admin\Controller\AuthenticationType::class => Controller\AuthenticationType::class,
            \ZF\Apigility\Admin\Controller\AuthorizationController::class => Controller\AuthorizationController::class,
            \ZF\Apigility\Admin\Controller\CacheEnabledController::class => Controller\CacheEnabledController::class,
            \ZF\Apigility\Admin\Controller\ConfigController::class => Controller\ConfigController::class,
            \ZF\Apigility\Admin\Controller\Dashboard::class => Controller\Dashboard::class,
            \ZF\Apigility\Admin\Controller\DbAutodiscovery::class => Controller\DbAutodiscovery::class,
            \ZF\Apigility\Admin\Controller\Documentation::class => Controller\Documentation::class,
            \ZF\Apigility\Admin\Controller\Filters::class => Controller\Filters::class,
            \ZF\Apigility\Admin\Controller\FsPermissionsController::class => Controller\FsPermissionsController::class,
            \ZF\Apigility\Admin\Controller\Hydrators::class => Controller\Hydrators::class,
            \ZF\Apigility\Admin\Controller\InputFilter::class => Controller\InputFilter::class,
            \ZF\Apigility\Admin\Controller\ModuleConfigController::class => Controller\ModuleConfigController::class,
            \ZF\Apigility\Admin\Controller\ModuleCreationController::class => Controller\ModuleCreationController::class,
            \ZF\Apigility\Admin\Controller\PackageController::class => Controller\PackageController::class,
            \ZF\Apigility\Admin\Controller\SettingsDashboard::class => Controller\SettingsDashboard::class,
            \ZF\Apigility\Admin\Controller\SourceController::class => Controller\SourceController::class,
            \ZF\Apigility\Admin\Controller\Strategy::class => Controller\Strategy::class,
            \ZF\Apigility\Admin\Controller\Validators::class => Controller\Validators::class,
            \ZF\Apigility\Admin\Controller\VersioningController::class => Controller\VersioningController::class,
        ],
        'factories' => [
            Controller\ApiToolsVersionController::class => InvokableFactory::class,
            Controller\AppController::class            => InvokableFactory::class,
            Controller\AuthenticationController::class => Controller\AuthenticationControllerFactory::class,
            Controller\AuthenticationType::class       => Controller\AuthenticationTypeControllerFactory::class,
            Controller\AuthorizationController::class  => Controller\AuthorizationControllerFactory::class,
            Controller\CacheEnabledController::class   => InvokableFactory::class,
            Controller\ConfigController::class         => Controller\ConfigControllerFactory::class,
            Controller\Dashboard::class                => Controller\DashboardControllerFactory::class,
            Controller\DbAutodiscovery::class          => Controller\DbAutodiscoveryControllerFactory::class,
            Controller\Documentation::class            => Controller\DocumentationControllerFactory::class,
            Controller\Filters::class                  => Controller\FiltersControllerFactory::class,
            Controller\FsPermissionsController::class  => InvokableFactory::class,
            Controller\Hydrators::class                => Controller\HydratorsControllerFactory::class,
            Controller\InputFilter::class              => Controller\InputFilterControllerFactory::class,
            Controller\ModuleConfigController::class   => Controller\ModuleConfigControllerFactory::class,
            Controller\ModuleCreationController::class => Controller\ModuleCreationControllerFactory::class,
            Controller\PackageController::class        => InvokableFactory::class,
            Controller\SettingsDashboard::class        => Controller\DashboardControllerFactory::class,
            Controller\SourceController::class         => Controller\SourceControllerFactory::class,
            Controller\Strategy::class                 => Controller\StrategyControllerFactory::class,
            Controller\Validators::class               => Controller\ValidatorsControllerFactory::class,
            Controller\VersioningController::class     => Controller\VersioningControllerFactory::class,
        ],
    ],

    'router' => [
        'routes' => [
            'api-tools' => [
                'child_routes' => [
                    'ui' => [
                        'type'  => 'Literal',
                        'options' => [
                            'route' => '/ui',
                            'defaults' => [
                                'controller' => Controller\App::class,
                                'action'     => 'app',
                            ],
                        ],
                    ],
                    'api' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/api',
                            'defaults' => [
                                'is_api-tools_admin_api' => true,
                                'action'                 => false,
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'api-tools-version' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/api-tools-version',
                                    'defaults' => [
                                        'controller' => Controller\ApiToolsVersionController::class,
                                        'action'     => 'index',
                                    ],
                                ],
                            ],
                            'dashboard' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/dashboard',
                                    'defaults' => [
                                        'controller' => Controller\Dashboard::class,
                                        'action'     => 'dashboard',
                                    ],
                                ],
                            ],
                            'settings-dashboard' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/settings-dashboard',
                                    'defaults' => [
                                        'controller' => Controller\SettingsDashboard::class,
                                        'action'     => 'settingsDashboard',
                                    ],
                                ],
                            ],
                            'strategy' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/strategy/:strategy_name',
                                    'defaults' => [
                                        'controller' => Controller\Strategy::class,
                                        'action'     => 'exists',
                                    ],
                                ],
                            ],
                            'cache-enabled' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/cache-enabled',
                                    'defaults' => [
                                        'controller' => Controller\CacheEnabled::class,
                                        'action'     => 'cacheEnabled',
                                    ],
                                ],
                            ],
                            'fs-permissions' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/fs-permissions',
                                    'defaults' => [
                                        'controller' => Controller\FsPermissions::class,
                                        'action'     => 'fsPermissions',
                                    ],
                                ],
                            ],
                            'config' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/config',
                                    'defaults' => [
                                        'controller' => Controller\Config::class,
                                        'action'     => 'process',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'module' => [
                                        'type' => 'Literal',
                                        'options' => [
                                            'route' => '/module',
                                            'defaults' => [
                                                'controller' => Controller\ModuleConfig::class,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'source' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/source',
                                    'defaults' => [
                                        'controller' => Controller\Source::class,
                                        'action'     => 'source',
                                    ],
                                ],
                            ],
                            'filters' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/filters',
                                    'defaults' => [
                                        'controller' => Controller\Filters::class,
                                        'action'     => 'filters',
                                    ],
                                ],
                            ],
                            'hydrators' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/hydrators',
                                    'defaults' => [
                                        'controller' => Controller\Hydrators::class,
                                        'action'     => 'hydrators',
                                    ],
                                ],
                            ],
                            'validators' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/validators',
                                    'defaults' => [
                                        'controller' => Controller\Validators::class,
                                        'action'     => 'validators',
                                    ],
                                ],
                            ],
                            'module-enable' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/module.enable',
                                    'defaults' => [
                                        'controller' => Controller\ModuleCreation::class,
                                        'action'     => 'apiEnable',
                                    ],
                                ],
                            ],
                            'versioning' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/versioning',
                                    'defaults' => [
                                        'controller' => Controller\Versioning::class,
                                        'action'     => 'versioning',
                                    ],
                                ],
                            ],
                            'default-version' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/default-version',
                                    'defaults' => [
                                        'controller' => Controller\Versioning::class,
                                        'action'     => 'defaultVersion',
                                    ],
                                ],
                            ],
                            'module' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/module[/:name]',
                                    'defaults' => [
                                        'controller' => Controller\Module::class,
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'authentication' => [
                                        'type' => 'Literal',
                                        'options' => [
                                            'route' => '/authentication',
                                            'defaults' => [
                                                'controller' => Controller\Authentication::class,
                                                'action'     => 'mapping',
                                            ],
                                        ],
                                    ],
                                    'authorization' => [
                                        'type' => 'Literal',
                                        'options' => [
                                            'route' => '/authorization',
                                            'defaults' => [
                                                'controller' => Controller\Authorization::class,
                                                'action'     => 'authorization',
                                            ],
                                        ],
                                    ],
                                    'rpc-service' => [
                                        'type' => 'Segment',
                                        'options' => [
                                            'route' => '/rpc[/:controller_service_name]',
                                            'defaults' => [
                                                'controller' => Controller\RpcService::class,
                                                'controller_type' => 'rpc',
                                            ],
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => [
                                            'input-filter' => [
                                                'type' => 'Segment',
                                                'options' => [
                                                    'route' => '/input-filter[/:input_filter_name]',
                                                    'defaults' => [
                                                        'controller' => Controller\InputFilter::class,
                                                        'action'     => 'index',
                                                    ],
                                                ],
                                            ],
                                            'doc' => [
                                                'type' => 'Segment',
                                                'options' => [
                                                    'route' => '/doc', // [/:http_method[/:http_direction]]
                                                    'defaults' => [
                                                        'controller' => Controller\Documentation::class,
                                                        'action'     => 'index',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    'rest-service' => [
                                        'type' => 'Segment',
                                        'options' => [
                                            'route' => '/rest[/:controller_service_name]',
                                            'defaults' => [
                                                'controller' => Controller\RestService::class,
                                                'controller_type' => 'rest',
                                            ],
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => [
                                            'input-filter' => [
                                                'type' => 'Segment',
                                                'options' => [
                                                    'route' => '/input-filter[/:input_filter_name]',
                                                    'defaults' => [
                                                        'controller' => Controller\InputFilter::class,
                                                        'action'     => 'index',
                                                    ],
                                                ],
                                            ],
                                            'doc' => [
                                                'type' => 'Segment',
                                                'options' => [
                                                    'route' => '/doc', // [/:rest_resource_type[/:http_method[/:http_direction]]]
                                                    'defaults' => [
                                                        'controller' => Controller\Documentation::class,
                                                        'action'     => 'index',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    'db-autodiscovery' => [
                                        'type' => 'Segment',
                                        'options' => [
                                            'route' => '/:version/autodiscovery/:adapter_name',
                                            'defaults' => [
                                                'controller' => Controller\DbAutodiscovery::class,
                                                'action' => 'discover',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'authentication' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/authentication[/:authentication_adapter]',
                                    'defaults' => [
                                        'action'     => 'authentication',
                                        'controller' => Controller\Authentication::class,
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'oauth2' => [
                                        'type' => 'Literal',
                                        'options' => [
                                            'route' => '/oauth2',
                                            'defaults' => [
                                                'controller' => Controller\OAuth2Authentication::class,
                                            ],
                                        ],
                                    ],
                                    'http-basic' => [
                                        'type' => 'Literal',
                                        'options' => [
                                            'route' => '/http-basic',
                                            'defaults' => [
                                                'controller' => Controller\HttpBasicAuthentication::class,
                                            ],
                                        ],
                                    ],
                                    'http-digest' => [
                                        'type' => 'Literal',
                                        'options' => [
                                            'route' => '/http-digest',
                                            'defaults' => [
                                                'controller' => Controller\HttpDigestAuthentication::class,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'db-adapter' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/db-adapter[/:adapter_name]',
                                    'defaults' => [
                                        'controller' => Controller\DbAdapter::class,
                                    ],
                                ],
                            ],
                            'doctrine-adapter' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/doctrine-adapter[/:adapter_name]',
                                    'defaults' => [
                                        'controller' => Controller\DoctrineAdapter::class,
                                    ],
                                ],
                            ],
                            'content-negotiation' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/content-negotiation[/:content_name]',
                                    'defaults' => [
                                        'controller' => Controller\ContentNegotiation::class,
                                    ],
                                ],
                            ],
                            'package' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/package',
                                    'defaults' => [
                                        'controller' => Controller\Package::class,
                                        'action'     => 'index',
                                    ],
                                ],
                            ],
                            'authentication-type' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/auth-type',
                                    'defaults' => [
                                        'controller' => Controller\AuthenticationType::class,
                                        'action'     => 'authType',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'api-tools-content-negotiation' => [
        'controllers' => [
            Controller\ApiToolsVersionController::class => 'Json',
            Controller\Authentication::class           => 'HalJson',
            Controller\AuthenticationType::class       => 'Json',
            Controller\Authorization::class            => 'HalJson',
            Controller\CacheEnabled::class             => 'Json',
            Controller\ContentNegotiation::class       => 'HalJson',
            Controller\Dashboard::class                => 'HalJson',
            Controller\DbAdapter::class                => 'HalJson',
            Controller\DbAutodiscovery::class          => 'Json',
            Controller\DoctrineAdapter::class          => 'HalJson',
            Controller\Documentation::class            => 'HalJson',
            Controller\Filters::class                  => 'Json',
            Controller\FsPermissions::class            => 'Json',
            Controller\HttpBasicAuthentication::class  => 'HalJson',
            Controller\HttpDigestAuthentication::class => 'HalJson',
            Controller\Hydrators::class                => 'Json',
            Controller\InputFilter::class              => 'HalJson',
            Controller\Module::class                   => 'HalJson',
            Controller\ModuleCreation::class           => 'HalJson',
            Controller\OAuth2Authentication::class     => 'HalJson',
            Controller\Package::class                  => 'Json',
            Controller\RestService::class              => 'HalJson',
            Controller\RpcService::class               => 'HalJson',
            Controller\SettingsDashboard::class        => 'HalJson',
            Controller\Source::class                   => 'Json',
            Controller\Strategy::class                 => 'Json',
            Controller\Validators::class               => 'Json',
            Controller\Versioning::class               => 'Json',
        ],
        'accept_whitelist' => [
            Controller\ApiToolsVersionController::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Authentication::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Authorization::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\CacheEnabled::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\ContentNegotiation::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Dashboard::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\DbAdapter::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\DbAutodiscovery::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\DoctrineAdapter::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Documentation::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Filters::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\FsPermissions::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\HttpBasicAuthentication::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\HttpDigestAuthentication::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Hydrators::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\InputFilter::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Module::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\ModuleCreation::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\OAuth2Authentication::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\SettingsDashboard::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Source::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Strategy::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Validators::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Versioning::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\RestService::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\RpcService::class => [
                'application/json',
                'application/*+json',
            ],
        ],
        'content_type_whitelist' => [
            Controller\Authorization::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\CacheEnabled::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\ContentNegotiation::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Dashboard::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\DbAdapter::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\DbAutodiscovery::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\DoctrineAdapter::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Filters::class => [
                'application/json',
            ],
            Controller\FsPermissions::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Hydrators::class => [
                'application/json',
            ],
            Controller\HttpBasicAuthentication::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\HttpDigestAuthentication::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\InputFilter::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Module::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\ModuleCreation::class => [
                'application/json',
            ],
            Controller\OAuth2Authentication::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\SettingsDashboard::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Source::class => [
                'application/json',
            ],
            Controller\Strategy::class => [
                'application/json',
            ],
            Controller\Validators::class => [
                'application/json',
            ],
            Controller\Versioning::class => [
                'application/json',
            ],
            Controller\RestService::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\RpcService::class => [
                'application/json',
                'application/*+json',
            ],
        ],
    ],

    'api-tools-hal' => [
        'metadata_map' => [
            Model\AuthenticationEntity::class => [
                'hydrator'        => 'ArraySerializable',
            ],
            Model\AuthorizationEntity::class => [
                'hydrator'        => 'ArraySerializable',
            ],
            Model\ContentNegotiationEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'content_name',
                'entity_identifier_name' => 'content_name',
                'route_name'      => 'api-tools/api/content-negotiation',
            ],
            Model\DbConnectedRestServiceEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'controller_service_name',
                'entity_identifier_name' => 'controller_service_name',
                'route_name'      => 'api-tools/api/module/rest-service',
            ],
            Model\DbAdapterEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'adapter_name',
                'entity_identifier_name' => 'adapter_name',
                'route_name'      => 'api-tools/api/db-adapter',
            ],
            Model\DoctrineAdapterEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'adapter_name',
                'entity_identifier_name' => 'adapter_name',
                'route_name'      => 'api-tools/api/doctrine-adapter',
            ],
            Model\InputFilterCollection::class => [
                'route_name'      => 'api-tools/api/module/rest-service/input-filter',
                'is_collection'   => true,
                'collection_name' => 'input_filter',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ],
            Model\InputFilterEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
                'route_name'      => 'api-tools/api/module/rest-service/input-filter',
            ],
            Model\ModuleEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'name',
                'entity_identifier_name' => 'name',
                'route_name'      => 'api-tools/api/module',
            ],
            Model\RestInputFilterCollection::class => [
                'route_name'      => 'api-tools/api/module/rest-service/input-filter',
                'is_collection'   => true,
                'collection_name' => 'input_filter',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ],
            Model\RestInputFilterEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'input_filter_name',
                'route_name'      => 'api-tools/api/module/rest-service/input-filter',
                'entity_identifier_name' => 'input_filter_name',
            ],
            Model\DocumentationEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'rest_documentation',
                'entity_identifier_name' => 'rest_documentation',
                'route_name'      => 'api-tools/api/module/rest-service/rest-doc',
            ],
            Model\RestServiceEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'controller_service_name',
                'entity_identifier_name' => 'controller_service_name',
                'route_name'      => 'api-tools/api/module/rest-service',
                'links'           => [
                    [
                        'rel' => 'input_filter',
                        'route' => [
                            'name' => 'api-tools/api/module/rest-service/input-filter',
                        ],
                    ],
                    [
                        'rel' => 'documentation',
                        'route' => [
                            'name' => 'api-tools/api/module/rest-service/doc',
                        ],
                    ],
                ],
            ],
            Model\RpcInputFilterCollection::class => [
                'route_name'      => 'api-tools/api/module/rpc-service/input-filter',
                'is_collection'   => true,
                'collection_name' => 'input_filter',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ],
            Model\RpcInputFilterEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'input_filter_name',
                'route_name'      => 'api-tools/api/module/rpc-service/input-filter',
                'entity_identifier_name' => 'input_filter_name',
            ],
            Model\RpcServiceEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'controller_service_name',
                'entity_identifier_name' => 'controller_service_name',
                'route_name'      => 'api-tools/api/module/rpc-service',
                'links'           => [
                    [
                        'rel' => 'input_filter',
                        'route' => [
                            'name' => 'api-tools/api/module/rpc-service/input-filter',
                        ],
                    ],
                    [
                        'rel' => 'documentation',
                        'route' => [
                            'name' => 'api-tools/api/module/rpc-service/doc',
                        ],
                    ],
                ],
            ],
        ],
    ],

    'api-tools-rest' => [
        Controller\ContentNegotiation::class => [
            'listener'                => Model\ContentNegotiationResource::class,
            'route_name'              => 'api-tools/api/content-negotiation',
            'route_identifier_name'   => 'content_name',
            'entity_class'            => Model\ContentNegotiationEntity::class,
            'entity_http_methods'     => ['GET', 'PATCH', 'DELETE'],
            'collection_http_methods' => ['GET', 'POST'],
            'collection_name'         => 'selectors',
        ],
        Controller\DbAdapter::class => [
            'listener'                => Model\DbAdapterResource::class,
            'route_name'              => 'api-tools/api/db-adapter',
            'route_identifier_name'   => 'adapter_name',
            'entity_class'            => Model\DbAdapterEntity::class,
            'entity_http_methods'     => ['GET', 'PATCH', 'DELETE'],
            'collection_http_methods' => ['GET', 'POST'],
            'collection_name'         => 'db_adapter',
        ],
        Controller\DoctrineAdapter::class => [
            'listener'                => Model\DoctrineAdapterResource::class,
            'route_name'              => 'api-tools/api/doctrine-adapter',
            'route_identifier_name'   => 'adapter_name',
            'entity_class'            => Model\DoctrineAdapterEntity::class,
            'entity_http_methods'     => ['GET', 'PATCH', 'DELETE'],
            'collection_http_methods' => ['GET'],
            'collection_name'         => 'doctrine_adapter',
        ],
        Controller\Module::class => [
            'listener'                => Model\ModuleResource::class,
            'route_name'              => 'api-tools/api/module',
            'route_identifier_name'   => 'name',
            'entity_class'            => Model\ModuleEntity::class,
            'entity_http_methods'     => ['GET', 'DELETE'],
            'collection_http_methods' => ['GET', 'POST'],
            'collection_name'         => 'module',
        ],
        Controller\RpcService::class => [
            'listener'                   => Model\RpcServiceResource::class,
            'route_name'                 => 'api-tools/api/module/rpc-service',
            'entity_class'               => Model\RpcServiceEntity::class,
            'route_identifier_name'      => 'controller_service_name',
            'entity_http_methods'        => ['GET', 'PATCH', 'DELETE'],
            'collection_http_methods'    => ['GET', 'POST'],
            'collection_name'            => 'rpc',
            'collection_query_whitelist' => ['version'],
        ],
        Controller\RestService::class => [
            'listener'                   => Model\RestServiceResource::class,
            'route_name'                 => 'api-tools/api/module/rest-service',
            'entity_class'               => Model\RestServiceEntity::class,
            'route_identifier_name'      => 'controller_service_name',
            'entity_http_methods'        => ['GET', 'PATCH', 'DELETE'],
            'collection_http_methods'    => ['GET', 'POST'],
            'collection_name'            => 'rest',
            'collection_query_whitelist' => ['version'],
        ],
    ],

    'api-tools-rpc' => [
        Controller\ApiToolsVersionController::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/api-tools-version',
        ],
        Controller\Authentication::class => [
            'http_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
            'route_name'   => 'api-tools/api/authentication',
        ],
        Controller\AuthenticationType::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/authentication-type',
        ],
        Controller\Authorization::class => [
            'http_methods' => ['GET', 'PATCH', 'PUT'],
            'route_name'   => 'api-tools/api/module/authorization',
        ],
        Controller\CacheEnabled::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/cache-enabled',
        ],
        Controller\Config::class => [
            'http_methods' => ['GET', 'PATCH'],
            'route_name'   => 'api-tools/api/config',
        ],
        Controller\Dashboard::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/dashboard',
        ],
        Controller\DbAutodiscovery::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/module/db-autodiscovery',
        ],
        Controller\Documentation::class => [
            'http_methods' => ['GET', 'PATCH', 'PUT', 'DELETE'],
            'route_name'   => 'api-tools/api/rest-service/rest-doc',
        ],
        Controller\Filters::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/filters',
        ],
        Controller\FsPermissions::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/fs-permissions',
        ],
        Controller\HttpBasicAuthentication::class => [
            'http_methods' => ['GET', 'POST', 'PATCH', 'DELETE'],
            'route_name'   => 'api-tools/api/authentication/http-basic',
        ],
        Controller\HttpDigestAuthentication::class => [
            'http_methods' => ['GET', 'POST', 'PATCH', 'DELETE'],
            'route_name'   => 'api-tools/api/authentication/http-digest',
        ],
        Controller\Hydrators::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/hydrators',
        ],
        Controller\InputFilter::class => [
            'http_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
            'route_name'   => 'api-tools/api/rpc-service/input-filter',
        ],
        Controller\ModuleConfig::class => [
            'http_methods' => ['GET', 'PATCH'],
            'route_name'   => 'api-tools/api/config/module',
        ],
        Controller\ModuleCreation::class => [
            'http_methods' => ['PUT'],
            'route_name'   => 'api-tools/api/module-enable',
        ],
        Controller\OAuth2Authentication::class => [
            'http_methods' => ['GET', 'POST', 'PATCH', 'DELETE'],
            'route_name'   => 'api-tools/api/authentication/oauth2',
        ],
        Controller\SettingsDashboard::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/settings-dashboard',
        ],
        Controller\Source::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/source',
        ],
        Controller\Validators::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/validators',
        ],
        Controller\Versioning::class => [
            'http_methods' => ['PATCH'],
            'route_name'   => 'api-tools/api/versioning',
        ],
        Controller\Strategy::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/strategy',
        ],
        Controller\Package::class => [
            'http_methods' => ['GET', 'POST'],
            'route_name'   => 'api-tools/api/package',
        ],
    ],

    /*
     * Metadata for scalar filter options.
     *
     * Each key in the map is a filter plugin name. The value is an array of
     * option key/type pairs. If more than one type is possible, the types are
     * OR'd.
     */
    'filter_metadata' => [
        'Laminas\I18n\Filter\Alnum' => [
            'allow_white_space' => 'bool',
            'locale' => 'string',
        ],
        'Laminas\I18n\Filter\Alpha' => [
            'allow_white_space' => 'bool',
            'locale' => 'string',
        ],
        'Laminas\Filter\BaseName' => [],
        'Laminas\Filter\Boolean' => [
            'casting' => 'bool',
            'type' => 'string',
        ],
        'Laminas\Filter\Callback' => [
            'callback' => 'string',
        ],
        'Laminas\Filter\Compress\Bz2' => [
            'archive' => 'string',
            'blocksize' => 'int',
        ],
        'Laminas\Filter\Compress\Gz' => [
            'archive' => 'string',
            'level' => 'int',
            'mode' => 'string',
        ],
        'Laminas\Filter\Compress\Llaminas' => [],
        'Laminas\Filter\Compress' => [
            'adapter' => 'string',
        ],
        'Laminas\Filter\Compress\Rar' => [
            'archive' => 'string',
            'callback' => 'string',
            'password' => 'string',
            'target' => 'string',
        ],
        'Laminas\Filter\Compress\Snappy' => [],
        'Laminas\Filter\Compress\Tar' => [
            'archive' => 'string',
            'target' => 'string',
            'mode' => 'string',
        ],
        'Laminas\Filter\Compress\Zip' => [
            'archive' => 'string',
            'target' => 'string',
        ],
        'Laminas\Filter\DateTimeFormatter' => [
            'format' => 'string',
        ],
        'Laminas\Filter\Decompress' => [
            'adapter' => 'string',
        ],
        'Laminas\Filter\Decrypt' => [
            'adapter' => 'string',
        ],
        'Laminas\Filter\Digits' => [],
        'Laminas\Filter\Dir' => [],
        'Laminas\Filter\Encrypt\BlockCipher' => [
            'algorithm' => 'string',
            'compression' => 'string',
            'hash' => 'string',
            'key' => 'string',
            'key_iteration' => 'int',
            'vector' => 'string',
        ],
        'Laminas\Filter\Encrypt\Openssl' => [
            'compression' => 'string',
            'package' => 'bool',
            'passphrase' => 'string',
        ],
        'Laminas\Filter\Encrypt' => [
            'adapter' => 'string',
        ],
        'Laminas\Filter\File\Decrypt' => [
            'adapter' => 'string',
            'filename' => 'string',
        ],
        'Laminas\Filter\File\Encrypt' => [
            'adapter' => 'string',
            'filename' => 'string',
        ],
        'Laminas\Filter\File\LowerCase' => [
            'encoding' => 'string',
        ],
        'Laminas\Filter\File\Rename' => [
            'overwrite' => 'bool',
            'randomize' => 'bool',
            'source' => 'string',
            'target' => 'string',
        ],
        'Laminas\Filter\File\RenameUpload' => [
            'overwrite' => 'bool',
            'randomize' => 'bool',
            'target' => 'string',
            'use_upload_extension' => 'bool',
            'use_upload_name' => 'bool',
        ],
        'Laminas\Filter\File\UpperCase' => [
            'encoding' => 'string',
        ],
        'Laminas\Filter\HtmlEntities' => [
            'charset' => 'string',
            'doublequote' => 'bool',
            'encoding' => 'string',
            'quotestyle' => 'int',
        ],
        'Laminas\Filter\Inflector' => [
            'throwTargetExceptionsOn' => 'bool',
            'targetReplacementIdentifier' => 'string',
            'target' => 'string',
        ],
        'Laminas\Filter\Int' => [],
        'Laminas\Filter\Null' => [
            'type' => 'int|string',
        ],
        'Laminas\I18n\Filter\NumberFormat' => [
            'locale' => 'string',
            'style' => 'int',
            'type' => 'int',
        ],
        'Laminas\I18n\Filter\NumberParse' => [
            'locale' => 'string',
            'style' => 'int',
            'type' => 'int',
        ],
        'Laminas\Filter\PregReplace' => [
            'pattern' => 'string',
            'replacement' => 'string',
        ],
        'Laminas\Filter\RealPath' => [
            'exists' => 'bool',
        ],
        'Laminas\Filter\StringToLower' => [
            'encoding' => 'string',
        ],
        'Laminas\Filter\StringToUpper' => [
            'encoding' => 'string',
        ],
        'Laminas\Filter\StringTrim' => [
            'charlist' => 'string',
        ],
        'Laminas\Filter\StripNewlines' => [],
        'Laminas\Filter\StripTags' => [
            'allowAttribs' => 'string',
            'allowTags' => 'string',
        ],
        'Laminas\Filter\ToInt' => [],
        'Laminas\Filter\ToNull' => [
            'type' => 'int|string',
        ],
        'Laminas\Filter\UriNormalize' => [
            'defaultscheme' => 'string',
            'enforcedscheme' => 'string',
        ],
        'Laminas\Filter\Word\CamelCaseToDash' => [],
        'Laminas\Filter\Word\CamelCaseToSeparator' => [
            'separator' => 'string',
        ],
        'Laminas\Filter\Word\CamelCaseToUnderscore' => [],
        'Laminas\Filter\Word\DashToCamelCase' => [],
        'Laminas\Filter\Word\DashToSeparator' => [
            'separator' => 'string',
        ],
        'Laminas\Filter\Word\DashToUnderscore' => [],
        'Laminas\Filter\Word\SeparatorToCamelCase' => [
            'separator' => 'string',
        ],
        'Laminas\Filter\Word\SeparatorToDash' => [
            'separator' => 'string',
        ],
        'Laminas\Filter\Word\SeparatorToSeparator' => [
            'searchseparator' => 'string',
            'replacementseparator' => 'string',
        ],
        'Laminas\Filter\Word\UnderscoreToCamelCase' => [],
        'Laminas\Filter\Word\UnderscoreToDash' => [],
        'Laminas\Filter\Word\UnderscoreToSeparator' => [
            'separator' => 'string',
        ],
    ],

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
    'validator_metadata' => [
        '__all__' => [
            'breakchainonfailure' => 'bool',
            'message' => 'string',
            'messagelength' => 'int',
            'valueobscured' => 'bool',
            'translatortextdomain' => 'string',
            'translatorenabled' => 'bool',
        ],
        'Laminas\Validator\Barcode\Codabar' => [],
        'Laminas\Validator\Barcode\Code128' => [],
        'Laminas\Validator\Barcode\Code25interleaved' => [],
        'Laminas\Validator\Barcode\Code25' => [],
        'Laminas\Validator\Barcode\Code39ext' => [],
        'Laminas\Validator\Barcode\Code39' => [],
        'Laminas\Validator\Barcode\Code93ext' => [],
        'Laminas\Validator\Barcode\Code93' => [],
        'Laminas\Validator\Barcode\Ean12' => [],
        'Laminas\Validator\Barcode\Ean13' => [],
        'Laminas\Validator\Barcode\Ean14' => [],
        'Laminas\Validator\Barcode\Ean18' => [],
        'Laminas\Validator\Barcode\Ean2' => [],
        'Laminas\Validator\Barcode\Ean5' => [],
        'Laminas\Validator\Barcode\Ean8' => [],
        'Laminas\Validator\Barcode\Gtin12' => [],
        'Laminas\Validator\Barcode\Gtin13' => [],
        'Laminas\Validator\Barcode\Gtin14' => [],
        'Laminas\Validator\Barcode\Identcode' => [],
        'Laminas\Validator\Barcode\Intelligentmail' => [],
        'Laminas\Validator\Barcode\Issn' => [],
        'Laminas\Validator\Barcode\Itf14' => [],
        'Laminas\Validator\Barcode\Leitcode' => [],
        'Laminas\Validator\Barcode' => [
            'adapter' => 'string', // this is the validator adapter name to use
            'useChecksum' => 'bool',
        ],
        'Laminas\Validator\Barcode\Planet' => [],
        'Laminas\Validator\Barcode\Postnet' => [],
        'Laminas\Validator\Barcode\Royalmail' => [],
        'Laminas\Validator\Barcode\Sscc' => [],
        'Laminas\Validator\Barcode\Upca' => [],
        'Laminas\Validator\Barcode\Upce' => [],
        'Laminas\Validator\Between' => [
            'inclusive' => 'bool',
            'max' => 'int',
            'min' => 'int',
        ],
        'Laminas\Validator\Bitwise' => [
            'control' => 'int',
            'operator' => 'string',
            'strict' => 'bool',
        ],
        'Laminas\Validator\Callback' => [
            'callback' => 'string',
        ],
        'Laminas\Validator\CreditCard' => [
            'type' => 'string',
            'service' => 'string',
        ],
        'Laminas\Validator\Csrf' => [
            'name' => 'string',
            'salt' => 'string',
            'timeout' => 'int',
        ],
        'Laminas\Validator\Date' => [
            'format' => 'string',
        ],
        'Laminas\Validator\DateStep' => [
            'format' => 'string',
            'basevalue' => 'string|int',
        ],
        'Laminas\Validator\Db\NoRecordExists' => [
            'table' => 'string',
            'schema' => 'string',
            'field' => 'string',
            'exclude' => 'string',
        ],
        'Laminas\Validator\Db\RecordExists' => [
            'table' => 'string',
            'schema' => 'string',
            'field' => 'string',
            'exclude' => 'string',
        ],
        'Laminas\ApiTools\ContentValidation\Validator\DbNoRecordExists' => [
            'adapter' => 'string',
            'table' => 'string',
            'schema' => 'string',
            'field' => 'string',
            'exclude' => 'string',
        ],
        'Laminas\ApiTools\ContentValidation\Validator\DbRecordExists' => [
            'adapter' => 'string',
            'table' => 'string',
            'schema' => 'string',
            'field' => 'string',
            'exclude' => 'string',
        ],
        'Laminas\Validator\Digits' => [],
        'Laminas\Validator\EmailAddress' => [
            'allow' => 'int',
            'useMxCheck' => 'bool',
            'useDeepMxCheck' => 'bool',
            'useDomainCheck' => 'bool',
        ],
        'Laminas\Validator\Explode' => [
            'valuedelimiter' => 'string',
            'breakonfirstfailure' => 'bool',
        ],
        'Laminas\Validator\File\Count' => [
            'max' => 'int',
            'min' => 'int',
        ],
        'Laminas\Validator\File\Crc32' => [
            'algorithm' => 'string',
            'hash' => 'string',
            'crc32' => 'string',
        ],
        'Laminas\Validator\File\ExcludeExtension' => [
            'case' => 'bool',
            'extension' => 'string',
        ],
        'Laminas\Validator\File\ExcludeMimeType' => [
            'disableMagicFile' => 'bool',
            'magicFile' => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType' => 'string',
        ],
        'Laminas\Validator\File\Exists' => [
            'directory' => 'string',
        ],
        'Laminas\Validator\File\Extension' => [
            'case' => 'bool',
            'extension' => 'string',
        ],
        'Laminas\Validator\File\FilesSize' => [
            'max' => 'int',
            'min' => 'int',
            'size' => 'int',
            'useByteString' => 'bool',
        ],
        'Laminas\Validator\File\Hash' => [
            'algorithm' => 'string',
            'hash' => 'string',
        ],
        'Laminas\Validator\File\ImageSize' => [
            'maxHeight' => 'int',
            'minHeight' => 'int',
            'maxWidth' => 'int',
            'minWidth' => 'int',
        ],
        'Laminas\Validator\File\IsCompressed' => [
            'disableMagicFile' => 'bool',
            'magicFile' => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType' => 'string',
        ],
        'Laminas\Validator\File\IsImage' => [
            'disableMagicFile' => 'bool',
            'magicFile' => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType' => 'string',
        ],
        'Laminas\Validator\File\Md5' => [
            'algorithm' => 'string',
            'hash' => 'string',
            'md5' => 'string',
        ],
        'Laminas\Validator\File\MimeType' => [
            'disableMagicFile' => 'bool',
            'magicFile' => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType' => 'string',
        ],
        'Laminas\Validator\File\NotExists' => [
            'directory' => 'string',
        ],
        'Laminas\Validator\File\Sha1' => [
            'algorithm' => 'string',
            'hash' => 'string',
            'sha1' => 'string',
        ],
        'Laminas\Validator\File\Size' => [
            'max' => 'int',
            'min' => 'int',
            'size' => 'int',
            'useByteString' => 'bool',
        ],
        'Laminas\Validator\File\UploadFile' => [],
        'Laminas\Validator\File\Upload' => [],
        'Laminas\Validator\File\WordCount' => [
            'max' => 'int',
            'min' => 'int',
        ],
        'Laminas\Validator\GreaterThan' => [
            'inclusive' => 'bool',
            'min' => 'int',
        ],
        'Laminas\Validator\Hex' => [],
        'Laminas\Validator\Hostname' => [
            'allow' => 'int',
            'useIdnCheck' => 'bool',
            'useTldCheck' => 'bool',
        ],
        'Laminas\Validator\Iban' => [
            'country_code' => 'string',
            'allow_non_sepa' => 'bool',
        ],
        'Laminas\Validator\Identical' => [
            'literal' => 'bool',
            'strict' => 'bool',
            'token' => 'string',
        ],
        'Laminas\Validator\InArray' => [
            'strict' => 'bool',
            'recursive' => 'bool',
        ],
        'Laminas\Validator\Ip' => [
            'allowipv4' => 'bool',
            'allowipv6' => 'bool',
            'allowipvfuture' => 'bool',
            'allowliteral' => 'bool',
        ],
        'Laminas\Validator\Isbn' => [
            'type' => 'string',
            'separator' => 'string',
        ],
        'Laminas\Validator\IsInstanceOf' => [
            'classname' => 'string',
        ],
        'Laminas\Validator\LessThan' => [
            'inclusive' => 'bool',
            'max' => 'int',
        ],
        'Laminas\Validator\NotEmpty' => [
            'type' => 'int',
        ],
        'Laminas\Validator\Regex' => [
            'pattern' => 'string',
        ],
        'Laminas\Validator\Sitemap\Changefreq' => [],
        'Laminas\Validator\Sitemap\Lastmod' => [],
        'Laminas\Validator\Sitemap\Loc' => [],
        'Laminas\Validator\Sitemap\Priority' => [],
        'Laminas\Validator\Step' => [
            'baseValue' => 'int|float',
            'step' => 'float',
        ],
        'Laminas\Validator\StringLength' => [
            'max' => 'int',
            'min' => 'int',
            'encoding' => 'string',
        ],
        'Laminas\Validator\Uri' => [
            'allowAbsolute' => 'bool',
            'allowRelative' => 'bool',
        ],
        'Laminas\Validator\Uuid' => [],
        'Laminas\I18n\Validator\Alnum' => [
            'allowwhitespace' => 'bool',
        ],
        'Laminas\I18n\Validator\Alpha' => [
            'allowwhitespace' => 'bool',
        ],
        'Laminas\I18n\Validator\DateTime' => [
            'calendar' => 'int',
            'datetype' => 'int',
            'pattern' => 'string',
            'timetype' => 'int',
            'timezone' => 'string',
            'locale' => 'string',
        ],
        'Laminas\I18n\Validator\Float' => [
            'locale' => 'string',
        ],
        'Laminas\I18n\Validator\Int' => [
            'locale' => 'string',
        ],
        'Laminas\I18n\Validator\IsFloat' => [
            'locale' => 'string',
        ],
        'Laminas\I18n\Validator\IsInt' => [
            'locale' => 'string',
        ],
        'Laminas\I18n\Validator\PhoneNumber' => [
            'country' => 'string',
            'allow_possible' => 'bool',
        ],
        'Laminas\I18n\Validator\PostCode' => [
            'locale' => 'string',
            'format' => 'string',
            'service' => 'string',
        ],
    ],

    'input_filters' => [
        'aliases' => [
            InputFilter\Authentication\BasicAuth::class  => InputFilter\Authentication\BasicInputFilter::class,
            InputFilter\Authentication\DigestAuth::class => InputFilter\Authentication\DigestInputFilter::class,
            InputFilter\Authentication\OAuth2::class     => InputFilter\Authentication\OAuth2InputFilter::class,
            InputFilter\Authorization::class             => InputFilter\AuthorizationInputFilter::class,
            InputFilter\ContentNegotiation::class        => InputFilter\ContentNegotiationInputFilter::class,
            InputFilter\CreateContentNegotiation::class  => InputFilter\CreateContentNegotiationInputFilter::class,
            InputFilter\DbAdapter::class                 => InputFilter\DbAdapterInputFilter::class,
            InputFilter\Documentation::class             => InputFilter\DocumentationInputFilter::class,
            InputFilter\Module::class                    => InputFilter\ModuleInputFilter::class,
            InputFilter\RestService\PATCH::class         => InputFilter\RestService\PatchInputFilter::class,
            InputFilter\RestService\POST::class          => InputFilter\RestService\PostInputFilter::class,
            InputFilter\RpcService\PATCH::class          => InputFilter\RpcService\PatchInputFilter::class,
            InputFilter\RpcService\POST::class           => InputFilter\RpcService\PostInputFilter::class,
            InputFilter\Version::class                   => InputFilter\VersionInputFilter::class,

            // Legacy Zend Framework aliases
            \ZF\Apigility\Admin\InputFilter\Authentication\BasicAuth::class => InputFilter\Authentication\BasicAuth::class,
            \ZF\Apigility\Admin\InputFilter\Authentication\DigestAuth::class => InputFilter\Authentication\DigestAuth::class,
            \ZF\Apigility\Admin\InputFilter\Authentication\OAuth2::class => InputFilter\Authentication\OAuth2::class,
            \ZF\Apigility\Admin\InputFilter\Authorization::class => InputFilter\Authorization::class,
            \ZF\Apigility\Admin\InputFilter\ContentNegotiation::class => InputFilter\ContentNegotiation::class,
            \ZF\Apigility\Admin\InputFilter\CreateContentNegotiation::class => InputFilter\CreateContentNegotiation::class,
            \ZF\Apigility\Admin\InputFilter\DbAdapter::class => InputFilter\DbAdapter::class,
            \ZF\Apigility\Admin\InputFilter\Documentation::class => InputFilter\Documentation::class,
            \ZF\Apigility\Admin\InputFilter\Module::class => InputFilter\Module::class,
            \ZF\Apigility\Admin\InputFilter\RestService\PATCH::class => InputFilter\RestService\PATCH::class,
            \ZF\Apigility\Admin\InputFilter\RestService\POST::class => InputFilter\RestService\POST::class,
            \ZF\Apigility\Admin\InputFilter\RpcService\PATCH::class => InputFilter\RpcService\PATCH::class,
            \ZF\Apigility\Admin\InputFilter\RpcService\POST::class => InputFilter\RpcService\POST::class,
            \ZF\Apigility\Admin\InputFilter\Version::class => InputFilter\Version::class,
            \ZF\Apigility\Admin\InputFilter\Authentication\BasicInputFilter::class => InputFilter\Authentication\BasicInputFilter::class,
            \ZF\Apigility\Admin\InputFilter\Authentication\DigestInputFilter::class => InputFilter\Authentication\DigestInputFilter::class,
            \ZF\Apigility\Admin\InputFilter\Authentication\OAuth2InputFilter::class => InputFilter\Authentication\OAuth2InputFilter::class,
            \ZF\Apigility\Admin\InputFilter\AuthorizationInputFilter::class => InputFilter\AuthorizationInputFilter::class,
            \ZF\Apigility\Admin\InputFilter\ContentNegotiationInputFilter::class => InputFilter\ContentNegotiationInputFilter::class,
            \ZF\Apigility\Admin\InputFilter\CreateContentNegotiationInputFilter::class => InputFilter\CreateContentNegotiationInputFilter::class,
            \ZF\Apigility\Admin\InputFilter\DbAdapterInputFilter::class => InputFilter\DbAdapterInputFilter::class,
            \ZF\Apigility\Admin\InputFilter\DocumentationInputFilter::class => InputFilter\DocumentationInputFilter::class,
            \ZF\Apigility\Admin\InputFilter\ModuleInputFilter::class => InputFilter\ModuleInputFilter::class,
            \ZF\Apigility\Admin\InputFilter\RestService\PatchInputFilter::class => InputFilter\RestService\PatchInputFilter::class,
            \ZF\Apigility\Admin\InputFilter\RestService\PostInputFilter::class => InputFilter\RestService\PostInputFilter::class,
            \ZF\Apigility\Admin\InputFilter\RpcService\PatchInputFilter::class => InputFilter\RpcService\PatchInputFilter::class,
            \ZF\Apigility\Admin\InputFilter\RpcService\PostInputFilter::class => InputFilter\RpcService\PostInputFilter::class,
            \ZF\Apigility\Admin\InputFilter\VersionInputFilter::class => InputFilter\VersionInputFilter::class,
            \ZF\Apigility\Admin\InputFilter\InputFilter::class => InputFilter\InputFilter::class,
        ],
        'factories' => [
            InputFilter\Authentication\BasicInputFilter::class     => InvokableFactory::class,
            InputFilter\Authentication\DigestInputFilter::class    => InvokableFactory::class,
            InputFilter\Authentication\OAuth2InputFilter::class    => InvokableFactory::class,
            InputFilter\AuthorizationInputFilter::class            => InvokableFactory::class,
            InputFilter\ContentNegotiationInputFilter::class       => InvokableFactory::class,
            InputFilter\CreateContentNegotiationInputFilter::class => InvokableFactory::class,
            InputFilter\DbAdapterInputFilter::class                => InvokableFactory::class,
            InputFilter\DocumentationInputFilter::class            => InvokableFactory::class,
            InputFilter\ModuleInputFilter::class                   => InvokableFactory::class,
            InputFilter\RestService\PatchInputFilter::class        => InvokableFactory::class,
            InputFilter\RestService\PostInputFilter::class         => InvokableFactory::class,
            InputFilter\RpcService\PatchInputFilter::class         => InvokableFactory::class,
            InputFilter\RpcService\PostInputFilter::class          => InvokableFactory::class,
            InputFilter\VersionInputFilter::class                  => InvokableFactory::class,

            InputFilter\InputFilter::class => InputFilter\Factory\InputFilterInputFilterFactory::class,
        ],
    ],

    'api-tools-content-validation' => [
        Controller\HttpBasicAuthentication::class => [
            'input_filter' => InputFilter\Authentication\BasicAuth::class,
        ],
        Controller\HttpDigestAuthentication::class => [
            'input_filter' => InputFilter\Authentication\DigestAuth::class,
        ],
        Controller\OAuth2Authentication::class => [
            'input_filter' => InputFilter\Authentication\OAuth2::class,
        ],
        Controller\DbAdapter::class => [
            'input_filter' => InputFilter\DbAdapter::class,
        ],
        Controller\ContentNegotiation::class => [
            'input_filter' => InputFilter\ContentNegotiation::class,
            'POST' => InputFilter\CreateContentNegotiation::class,
        ],
        Controller\Module::class => [
            'POST' => InputFilter\Module::class,
        ],
        Controller\Versioning::class => [
            'PATCH' => InputFilter\Version::class,
        ],
        Controller\RestService::class => [
            'POST' => InputFilter\RestService\POST::class, // for the collection
            'PATCH' => InputFilter\RestService\PATCH::class, // for the entity
        ],
        Controller\RpcService::class => [
            'POST' => InputFilter\RpcService\POST::class, // for the collection
            'PATCH' => InputFilter\RpcService\PATCH::class, // for the entity
        ],
        Controller\InputFilter::class => [
            'input_filter' => InputFilter\InputFilter::class,
        ],
        Controller\Documentation::class => [
            'input_filter' => InputFilter\Documentation::class,
        ],
        Controller\Authorization::class => [
            'input_filter' => InputFilter\Authorization::class,
        ],
    ],
];
