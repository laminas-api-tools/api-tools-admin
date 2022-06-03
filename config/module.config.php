<?php

// phpcs:disable Generic.Files.LineLength.TooLong


declare(strict_types=1);

namespace Laminas\ApiTools\Admin;

use Laminas\ApiTools\Admin\InputFilter\Authentication\BasicInputFilter;
use Laminas\ApiTools\Admin\InputFilter\Authentication\DigestInputFilter;
use Laminas\ApiTools\Admin\InputFilter\Authentication\OAuth2InputFilter;
use Laminas\ApiTools\Admin\InputFilter\AuthorizationInputFilter;
use Laminas\ApiTools\Admin\InputFilter\ContentNegotiationInputFilter;
use Laminas\ApiTools\Admin\InputFilter\CreateContentNegotiationInputFilter;
use Laminas\ApiTools\Admin\InputFilter\DbAdapterInputFilter;
use Laminas\ApiTools\Admin\InputFilter\DocumentationInputFilter;
use Laminas\ApiTools\Admin\InputFilter\Factory\InputFilterInputFilterFactory;
use Laminas\ApiTools\Admin\InputFilter\InputFilterInputFilter;
use Laminas\ApiTools\Admin\InputFilter\ModuleInputFilter;
use Laminas\ApiTools\Admin\InputFilter\RestService\PatchInputFilter as RestPatchInputFilter;
use Laminas\ApiTools\Admin\InputFilter\RestService\PostInputFilter as RestPostInputFilter;
use Laminas\ApiTools\Admin\InputFilter\RpcService\PatchInputFilter as RpcPatchInputFilter;
use Laminas\ApiTools\Admin\InputFilter\RpcService\PostInputFilter as RpcPostInputFilter;
use Laminas\ApiTools\Admin\InputFilter\VersionInputFilter;
use Laminas\Filter\BaseName;
use Laminas\Filter\Boolean;
use Laminas\Filter\Callback;
use Laminas\Filter\Compress;
use Laminas\Filter\Compress\Bz2;
use Laminas\Filter\Compress\Gz;
use Laminas\Filter\Compress\Rar;
use Laminas\Filter\Compress\Snappy;
use Laminas\Filter\Compress\Tar;
use Laminas\Filter\Compress\Zip;
use Laminas\Filter\DateTimeFormatter;
use Laminas\Filter\Decompress;
use Laminas\Filter\Decrypt;
use Laminas\Filter\Digits;
use Laminas\Filter\Dir;
use Laminas\Filter\Encrypt;
use Laminas\Filter\Encrypt\BlockCipher;
use Laminas\Filter\Encrypt\Openssl;
use Laminas\Filter\File\LowerCase;
use Laminas\Filter\File\Rename;
use Laminas\Filter\File\RenameUpload;
use Laminas\Filter\File\UpperCase;
use Laminas\Filter\HtmlEntities;
use Laminas\Filter\Inflector;
use Laminas\Filter\PregReplace;
use Laminas\Filter\RealPath;
use Laminas\Filter\StringToLower;
use Laminas\Filter\StringToUpper;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripNewlines;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToInt;
use Laminas\Filter\ToNull;
use Laminas\Filter\UriNormalize;
use Laminas\Filter\Word\CamelCaseToDash;
use Laminas\Filter\Word\CamelCaseToSeparator;
use Laminas\Filter\Word\CamelCaseToUnderscore;
use Laminas\Filter\Word\DashToCamelCase;
use Laminas\Filter\Word\DashToSeparator;
use Laminas\Filter\Word\DashToUnderscore;
use Laminas\Filter\Word\SeparatorToCamelCase;
use Laminas\Filter\Word\SeparatorToDash;
use Laminas\Filter\Word\SeparatorToSeparator;
use Laminas\Filter\Word\UnderscoreToCamelCase;
use Laminas\Filter\Word\UnderscoreToDash;
use Laminas\Filter\Word\UnderscoreToSeparator;
use Laminas\I18n\Filter\Alnum;
use Laminas\I18n\Filter\Alpha;
use Laminas\I18n\Filter\NumberFormat;
use Laminas\I18n\Filter\NumberParse;
use Laminas\I18n\Validator\DateTime;
use Laminas\I18n\Validator\IsFloat;
use Laminas\I18n\Validator\IsInt;
use Laminas\I18n\Validator\PhoneNumber;
use Laminas\I18n\Validator\PostCode;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Validator\Barcode;
use Laminas\Validator\Barcode\Codabar;
use Laminas\Validator\Barcode\Code128;
use Laminas\Validator\Barcode\Code25;
use Laminas\Validator\Barcode\Code25interleaved;
use Laminas\Validator\Barcode\Code39;
use Laminas\Validator\Barcode\Code39ext;
use Laminas\Validator\Barcode\Code93;
use Laminas\Validator\Barcode\Code93ext;
use Laminas\Validator\Barcode\Ean12;
use Laminas\Validator\Barcode\Ean13;
use Laminas\Validator\Barcode\Ean14;
use Laminas\Validator\Barcode\Ean18;
use Laminas\Validator\Barcode\Ean2;
use Laminas\Validator\Barcode\Ean5;
use Laminas\Validator\Barcode\Ean8;
use Laminas\Validator\Barcode\Gtin12;
use Laminas\Validator\Barcode\Gtin13;
use Laminas\Validator\Barcode\Gtin14;
use Laminas\Validator\Barcode\Identcode;
use Laminas\Validator\Barcode\Intelligentmail;
use Laminas\Validator\Barcode\Issn;
use Laminas\Validator\Barcode\Itf14;
use Laminas\Validator\Barcode\Leitcode;
use Laminas\Validator\Barcode\Planet;
use Laminas\Validator\Barcode\Postnet;
use Laminas\Validator\Barcode\Royalmail;
use Laminas\Validator\Barcode\Sscc;
use Laminas\Validator\Barcode\Upca;
use Laminas\Validator\Barcode\Upce;
use Laminas\Validator\Between;
use Laminas\Validator\Bitwise;
use Laminas\Validator\CreditCard;
use Laminas\Validator\Csrf;
use Laminas\Validator\Date;
use Laminas\Validator\DateStep;
use Laminas\Validator\Db\NoRecordExists;
use Laminas\Validator\Db\RecordExists;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\Explode;
use Laminas\Validator\File\Count;
use Laminas\Validator\File\Crc32;
use Laminas\Validator\File\ExcludeExtension;
use Laminas\Validator\File\ExcludeMimeType;
use Laminas\Validator\File\Exists;
use Laminas\Validator\File\Extension;
use Laminas\Validator\File\FilesSize;
use Laminas\Validator\File\Hash;
use Laminas\Validator\File\ImageSize;
use Laminas\Validator\File\IsCompressed;
use Laminas\Validator\File\IsImage;
use Laminas\Validator\File\Md5;
use Laminas\Validator\File\MimeType;
use Laminas\Validator\File\NotExists;
use Laminas\Validator\File\Sha1;
use Laminas\Validator\File\Size;
use Laminas\Validator\File\Upload;
use Laminas\Validator\File\UploadFile;
use Laminas\Validator\File\WordCount;
use Laminas\Validator\GreaterThan;
use Laminas\Validator\Hex;
use Laminas\Validator\Hostname;
use Laminas\Validator\Iban;
use Laminas\Validator\Identical;
use Laminas\Validator\InArray;
use Laminas\Validator\Ip;
use Laminas\Validator\Isbn;
use Laminas\Validator\IsInstanceOf;
use Laminas\Validator\LessThan;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\Regex;
use Laminas\Validator\Sitemap\Changefreq;
use Laminas\Validator\Sitemap\Lastmod;
use Laminas\Validator\Sitemap\Loc;
use Laminas\Validator\Sitemap\Priority;
use Laminas\Validator\Step;
use Laminas\Validator\StringLength;
use Laminas\Validator\Uri;
use Laminas\Validator\Uuid;

return [
    'api-tools-admin'               => [
        // path_spec defines whether modules should be created using PSR-0
        //     or PSR-4 module structure; the default is to use PSR-0.
        //     Valid values are:
        //     - Laminas\ApiTools\Admin\Model\ModulePathSpec::PSR_0 ("psr-0")
        //     - Laminas\ApiTools\Admin\Model\ModulePathSpec::PSR_4 ("psr-4")
        // 'path_spec' => 'psr-0',
    ],
    'service_manager'               => [
        // Legacy Zend Framework aliases
        'aliases'   => [
            'ZF\Apigility\Admin\Listener\CryptFilterListener'                           => Listener\CryptFilterListener::class,
            'ZF\Apigility\Admin\Listener\DisableHttpCacheListener'                      => Listener\DisableHttpCacheListener::class,
            'ZF\Apigility\Admin\Listener\EnableHalRenderCollectionsListener'            => Listener\EnableHalRenderCollectionsListener::class,
            'ZF\Apigility\Admin\Listener\InjectModuleResourceLinksListener'             => Listener\InjectModuleResourceLinksListener::class,
            'ZF\Apigility\Admin\Listener\NormalizeMatchedControllerServiceNameListener' => Listener\NormalizeMatchedControllerServiceNameListener::class,
            'ZF\Apigility\Admin\Listener\NormalizeMatchedInputFilterNameListener'       => Listener\NormalizeMatchedInputFilterNameListener::class,
            'ZF\Apigility\Admin\Model\AuthenticationModel'                              => Model\AuthenticationModel::class,
            'ZF\Apigility\Admin\Model\AuthorizationModelFactory'                        => Model\AuthorizationModelFactory::class,
            'ZF\Apigility\Admin\Model\ContentNegotiationModel'                          => Model\ContentNegotiationModel::class,
            'ZF\Apigility\Admin\Model\ContentNegotiationResource'                       => Model\ContentNegotiationResource::class,
            'ZF\Apigility\Admin\Model\DbAdapterModel'                                   => Model\DbAdapterModel::class,
            'ZF\Apigility\Admin\Model\DbAdapterResource'                                => Model\DbAdapterResource::class,
            'ZF\Apigility\Admin\Model\DbAutodiscoveryModel'                             => Model\DbAutodiscoveryModel::class,
            'ZF\Apigility\Admin\Model\DoctrineAdapterModel'                             => Model\DoctrineAdapterModel::class,
            'ZF\Apigility\Admin\Model\DoctrineAdapterResource'                          => Model\DoctrineAdapterResource::class,
            'ZF\Apigility\Admin\Model\DocumentationModel'                               => Model\DocumentationModel::class,
            'ZF\Apigility\Admin\Model\FiltersModel'                                     => Model\FiltersModel::class,
            'ZF\Apigility\Admin\Model\HydratorsModel'                                   => Model\HydratorsModel::class,
            'ZF\Apigility\Admin\Model\InputFilterModel'                                 => Model\InputFilterModel::class,
            'ZF\Apigility\Admin\Model\ModuleModel'                                      => Model\ModuleModel::class,
            'ZF\Apigility\Admin\Model\ModulePathSpec'                                   => Model\ModulePathSpec::class,
            'ZF\Apigility\Admin\Model\ModuleResource'                                   => Model\ModuleResource::class,
            'ZF\Apigility\Admin\Model\ModuleVersioningModelFactory'                     => Model\ModuleVersioningModelFactory::class,
            'ZF\Apigility\Admin\Model\RestServiceModelFactory'                          => Model\RestServiceModelFactory::class,
            'ZF\Apigility\Admin\Model\RestServiceResource'                              => Model\RestServiceResource::class,
            'ZF\Apigility\Admin\Model\RpcServiceModelFactory'                           => Model\RpcServiceModelFactory::class,
            'ZF\Apigility\Admin\Model\RpcServiceResource'                               => Model\RpcServiceResource::class,
            'ZF\Apigility\Admin\Model\ValidatorMetadataModel'                           => Model\ValidatorMetadataModel::class,
            'ZF\Apigility\Admin\Model\ValidatorsModel'                                  => Model\ValidatorsModel::class,
            'ZF\Apigility\Admin\Model\VersioningModelFactory'                           => Model\VersioningModelFactory::class,
        ],
        'factories' => [
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
        ],
    ],
    'controllers'                   => [
        'aliases'   => [
            Controller\App::class                                      => Controller\AppController::class,
            Controller\Authentication::class                           => Controller\AuthenticationController::class,
            Controller\Authorization::class                            => Controller\AuthorizationController::class,
            Controller\CacheEnabled::class                             => Controller\CacheEnabledController::class,
            Controller\Config::class                                   => Controller\ConfigController::class,
            Controller\FsPermissions::class                            => Controller\FsPermissionsController::class,
            Controller\HttpBasicAuthentication::class                  => Controller\Authentication::class,
            Controller\HttpDigestAuthentication::class                 => Controller\Authentication::class,
            Controller\ModuleConfig::class                             => Controller\ModuleConfigController::class,
            Controller\ModuleCreation::class                           => Controller\ModuleCreationController::class,
            Controller\OAuth2Authentication::class                     => Controller\Authentication::class,
            Controller\Source::class                                   => Controller\SourceController::class,
            Controller\Versioning::class                               => Controller\VersioningController::class,

            // Legacy Zend Framework aliases
            'ZF\Apigility\Admin\Controller\App'                        => Controller\App::class,
            'ZF\Apigility\Admin\Controller\Authentication'             => Controller\Authentication::class,
            'ZF\Apigility\Admin\Controller\Authorization'              => Controller\Authorization::class,
            'ZF\Apigility\Admin\Controller\CacheEnabled'               => Controller\CacheEnabled::class,
            'ZF\Apigility\Admin\Controller\Config'                     => Controller\Config::class,
            'ZF\Apigility\Admin\Controller\FsPermissions'              => Controller\FsPermissions::class,
            'ZF\Apigility\Admin\Controller\HttpBasicAuthentication'    => Controller\HttpBasicAuthentication::class,
            'ZF\Apigility\Admin\Controller\HttpDigestAuthentication'   => Controller\HttpDigestAuthentication::class,
            'ZF\Apigility\Admin\Controller\ModuleConfig'               => Controller\ModuleConfig::class,
            'ZF\Apigility\Admin\Controller\ModuleCreation'             => Controller\ModuleCreation::class,
            'ZF\Apigility\Admin\Controller\OAuth2Authentication'       => Controller\OAuth2Authentication::class,
            'ZF\Apigility\Admin\Controller\Source'                     => Controller\Source::class,
            'ZF\Apigility\Admin\Controller\Versioning'                 => Controller\Versioning::class,
            'ZF\Apigility\Admin\Controller\ApigilityVersionController' => Controller\ApiToolsVersionController::class,
            'ZF\Apigility\Admin\Controller\AppController'              => Controller\AppController::class,
            'ZF\Apigility\Admin\Controller\AuthenticationController'   => Controller\AuthenticationController::class,
            'ZF\Apigility\Admin\Controller\AuthenticationType'         => Controller\AuthenticationType::class,
            'ZF\Apigility\Admin\Controller\AuthorizationController'    => Controller\AuthorizationController::class,
            'ZF\Apigility\Admin\Controller\CacheEnabledController'     => Controller\CacheEnabledController::class,
            'ZF\Apigility\Admin\Controller\ConfigController'           => Controller\ConfigController::class,
            'ZF\Apigility\Admin\Controller\Dashboard'                  => Controller\Dashboard::class,
            'ZF\Apigility\Admin\Controller\DbAutodiscovery'            => Controller\DbAutodiscovery::class,
            'ZF\Apigility\Admin\Controller\Documentation'              => Controller\Documentation::class,
            'ZF\Apigility\Admin\Controller\Filters'                    => Controller\Filters::class,
            'ZF\Apigility\Admin\Controller\FsPermissionsController'    => Controller\FsPermissionsController::class,
            'ZF\Apigility\Admin\Controller\Hydrators'                  => Controller\Hydrators::class,
            'ZF\Apigility\Admin\Controller\InputFilter'                => Controller\InputFilter::class,
            'ZF\Apigility\Admin\Controller\ModuleConfigController'     => Controller\ModuleConfigController::class,
            'ZF\Apigility\Admin\Controller\ModuleCreationController'   => Controller\ModuleCreationController::class,
            'ZF\Apigility\Admin\Controller\SettingsDashboard'          => Controller\SettingsDashboard::class,
            'ZF\Apigility\Admin\Controller\SourceController'           => Controller\SourceController::class,
            'ZF\Apigility\Admin\Controller\Strategy'                   => Controller\Strategy::class,
            'ZF\Apigility\Admin\Controller\Validators'                 => Controller\Validators::class,
            'ZF\Apigility\Admin\Controller\VersioningController'       => Controller\VersioningController::class,
        ],
        'factories' => [
            Controller\ApiToolsVersionController::class => InvokableFactory::class,
            Controller\AppController::class             => InvokableFactory::class,
            Controller\AuthenticationController::class  => Controller\AuthenticationControllerFactory::class,
            Controller\AuthenticationType::class        => Controller\AuthenticationTypeControllerFactory::class,
            Controller\AuthorizationController::class   => Controller\AuthorizationControllerFactory::class,
            Controller\CacheEnabledController::class    => InvokableFactory::class,
            Controller\ConfigController::class          => Controller\ConfigControllerFactory::class,
            Controller\Dashboard::class                 => Controller\DashboardControllerFactory::class,
            Controller\DbAutodiscovery::class           => Controller\DbAutodiscoveryControllerFactory::class,
            Controller\Documentation::class             => Controller\DocumentationControllerFactory::class,
            Controller\Filters::class                   => Controller\FiltersControllerFactory::class,
            Controller\FsPermissionsController::class   => InvokableFactory::class,
            Controller\Hydrators::class                 => Controller\HydratorsControllerFactory::class,
            Controller\InputFilter::class               => Controller\InputFilterControllerFactory::class,
            Controller\ModuleConfigController::class    => Controller\ModuleConfigControllerFactory::class,
            Controller\ModuleCreationController::class  => Controller\ModuleCreationControllerFactory::class,
            Controller\SettingsDashboard::class         => Controller\DashboardControllerFactory::class,
            Controller\SourceController::class          => Controller\SourceControllerFactory::class,
            Controller\Strategy::class                  => Controller\StrategyControllerFactory::class,
            Controller\Validators::class                => Controller\ValidatorsControllerFactory::class,
            Controller\VersioningController::class      => Controller\VersioningControllerFactory::class,
        ],
    ],
    'router'                        => [
        'routes' => [
            'api-tools' => [
                'child_routes' => [
                    'ui'  => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/ui',
                            'defaults' => [
                                'controller' => Controller\App::class,
                                'action'     => 'app',
                            ],
                        ],
                    ],
                    'api' => [
                        'type'          => 'Literal',
                        'options'       => [
                            'route'    => '/api',
                            'defaults' => [
                                'is_api-tools_admin_api' => true,
                                'action'                 => false,
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes'  => [
                            'api-tools-version'   => [
                                'type'    => 'Literal',
                                'options' => [
                                    'route'    => '/api-tools-version',
                                    'defaults' => [
                                        'controller' => Controller\ApiToolsVersionController::class,
                                        'action'     => 'index',
                                    ],
                                ],
                            ],
                            'dashboard'           => [
                                'type'    => 'Literal',
                                'options' => [
                                    'route'    => '/dashboard',
                                    'defaults' => [
                                        'controller' => Controller\Dashboard::class,
                                        'action'     => 'dashboard',
                                    ],
                                ],
                            ],
                            'settings-dashboard'  => [
                                'type'    => 'Literal',
                                'options' => [
                                    'route'    => '/settings-dashboard',
                                    'defaults' => [
                                        'controller' => Controller\SettingsDashboard::class,
                                        'action'     => 'settingsDashboard',
                                    ],
                                ],
                            ],
                            'strategy'            => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/strategy/:strategy_name',
                                    'defaults' => [
                                        'controller' => Controller\Strategy::class,
                                        'action'     => 'exists',
                                    ],
                                ],
                            ],
                            'cache-enabled'       => [
                                'type'    => 'Literal',
                                'options' => [
                                    'route'    => '/cache-enabled',
                                    'defaults' => [
                                        'controller' => Controller\CacheEnabled::class,
                                        'action'     => 'cacheEnabled',
                                    ],
                                ],
                            ],
                            'fs-permissions'      => [
                                'type'    => 'Literal',
                                'options' => [
                                    'route'    => '/fs-permissions',
                                    'defaults' => [
                                        'controller' => Controller\FsPermissions::class,
                                        'action'     => 'fsPermissions',
                                    ],
                                ],
                            ],
                            'config'              => [
                                'type'          => 'Literal',
                                'options'       => [
                                    'route'    => '/config',
                                    'defaults' => [
                                        'controller' => Controller\Config::class,
                                        'action'     => 'process',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes'  => [
                                    'module' => [
                                        'type'    => 'Literal',
                                        'options' => [
                                            'route'    => '/module',
                                            'defaults' => [
                                                'controller' => Controller\ModuleConfig::class,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'source'              => [
                                'type'    => 'Literal',
                                'options' => [
                                    'route'    => '/source',
                                    'defaults' => [
                                        'controller' => Controller\Source::class,
                                        'action'     => 'source',
                                    ],
                                ],
                            ],
                            'filters'             => [
                                'type'    => 'Literal',
                                'options' => [
                                    'route'    => '/filters',
                                    'defaults' => [
                                        'controller' => Controller\Filters::class,
                                        'action'     => 'filters',
                                    ],
                                ],
                            ],
                            'hydrators'           => [
                                'type'    => 'Literal',
                                'options' => [
                                    'route'    => '/hydrators',
                                    'defaults' => [
                                        'controller' => Controller\Hydrators::class,
                                        'action'     => 'hydrators',
                                    ],
                                ],
                            ],
                            'validators'          => [
                                'type'    => 'Literal',
                                'options' => [
                                    'route'    => '/validators',
                                    'defaults' => [
                                        'controller' => Controller\Validators::class,
                                        'action'     => 'validators',
                                    ],
                                ],
                            ],
                            'module-enable'       => [
                                'type'    => 'Literal',
                                'options' => [
                                    'route'    => '/module.enable',
                                    'defaults' => [
                                        'controller' => Controller\ModuleCreation::class,
                                        'action'     => 'apiEnable',
                                    ],
                                ],
                            ],
                            'versioning'          => [
                                'type'    => 'Literal',
                                'options' => [
                                    'route'    => '/versioning',
                                    'defaults' => [
                                        'controller' => Controller\Versioning::class,
                                        'action'     => 'versioning',
                                    ],
                                ],
                            ],
                            'default-version'     => [
                                'type'    => 'Literal',
                                'options' => [
                                    'route'    => '/default-version',
                                    'defaults' => [
                                        'controller' => Controller\Versioning::class,
                                        'action'     => 'defaultVersion',
                                    ],
                                ],
                            ],
                            'module'              => [
                                'type'          => 'Segment',
                                'options'       => [
                                    'route'    => '/module[/:name]',
                                    'defaults' => [
                                        'controller' => Controller\Module::class,
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes'  => [
                                    'authentication'   => [
                                        'type'    => 'Literal',
                                        'options' => [
                                            'route'    => '/authentication',
                                            'defaults' => [
                                                'controller' => Controller\Authentication::class,
                                                'action'     => 'mapping',
                                            ],
                                        ],
                                    ],
                                    'authorization'    => [
                                        'type'    => 'Literal',
                                        'options' => [
                                            'route'    => '/authorization',
                                            'defaults' => [
                                                'controller' => Controller\Authorization::class,
                                                'action'     => 'authorization',
                                            ],
                                        ],
                                    ],
                                    'rpc-service'      => [
                                        'type'          => 'Segment',
                                        'options'       => [
                                            'route'    => '/rpc[/:controller_service_name]',
                                            'defaults' => [
                                                'controller'      => Controller\RpcService::class,
                                                'controller_type' => 'rpc',
                                            ],
                                        ],
                                        'may_terminate' => true,
                                        'child_routes'  => [
                                            'input-filter' => [
                                                'type'    => 'Segment',
                                                'options' => [
                                                    'route'    => '/input-filter[/:input_filter_name]',
                                                    'defaults' => [
                                                        'controller' => Controller\InputFilter::class,
                                                        'action'     => 'index',
                                                    ],
                                                ],
                                            ],
                                            'doc'          => [
                                                'type'    => 'Segment',
                                                'options' => [
                                                    'route'    => '/doc',
                                                    // [/:http_method[/:http_direction]]
                                                    'defaults' => [
                                                        'controller' => Controller\Documentation::class,
                                                        'action'     => 'index',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    'rest-service'     => [
                                        'type'          => 'Segment',
                                        'options'       => [
                                            'route'    => '/rest[/:controller_service_name]',
                                            'defaults' => [
                                                'controller'      => Controller\RestService::class,
                                                'controller_type' => 'rest',
                                            ],
                                        ],
                                        'may_terminate' => true,
                                        'child_routes'  => [
                                            'input-filter' => [
                                                'type'    => 'Segment',
                                                'options' => [
                                                    'route'    => '/input-filter[/:input_filter_name]',
                                                    'defaults' => [
                                                        'controller' => Controller\InputFilter::class,
                                                        'action'     => 'index',
                                                    ],
                                                ],
                                            ],
                                            'doc'          => [
                                                'type'    => 'Segment',
                                                'options' => [
                                                    'route'    => '/doc',
                                                    // [/:rest_resource_type[/:http_method[/:http_direction]]]
                                                    'defaults' => [
                                                        'controller' => Controller\Documentation::class,
                                                        'action'     => 'index',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    'db-autodiscovery' => [
                                        'type'    => 'Segment',
                                        'options' => [
                                            'route'    => '/:version/autodiscovery/:adapter_name',
                                            'defaults' => [
                                                'controller' => Controller\DbAutodiscovery::class,
                                                'action'     => 'discover',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'authentication'      => [
                                'type'          => 'Segment',
                                'options'       => [
                                    'route'    => '/authentication[/:authentication_adapter]',
                                    'defaults' => [
                                        'action'     => 'authentication',
                                        'controller' => Controller\Authentication::class,
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes'  => [
                                    'oauth2'      => [
                                        'type'    => 'Literal',
                                        'options' => [
                                            'route'    => '/oauth2',
                                            'defaults' => [
                                                'controller' => Controller\OAuth2Authentication::class,
                                            ],
                                        ],
                                    ],
                                    'http-basic'  => [
                                        'type'    => 'Literal',
                                        'options' => [
                                            'route'    => '/http-basic',
                                            'defaults' => [
                                                'controller' => Controller\HttpBasicAuthentication::class,
                                            ],
                                        ],
                                    ],
                                    'http-digest' => [
                                        'type'    => 'Literal',
                                        'options' => [
                                            'route'    => '/http-digest',
                                            'defaults' => [
                                                'controller' => Controller\HttpDigestAuthentication::class,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'db-adapter'          => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/db-adapter[/:adapter_name]',
                                    'defaults' => [
                                        'controller' => Controller\DbAdapter::class,
                                    ],
                                ],
                            ],
                            'doctrine-adapter'    => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/doctrine-adapter[/:adapter_name]',
                                    'defaults' => [
                                        'controller' => Controller\DoctrineAdapter::class,
                                    ],
                                ],
                            ],
                            'content-negotiation' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/content-negotiation[/:content_name]',
                                    'defaults' => [
                                        'controller' => Controller\ContentNegotiation::class,
                                    ],
                                ],
                            ],
                            'authentication-type' => [
                                'type'    => 'Literal',
                                'options' => [
                                    'route'    => '/auth-type',
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
        'controllers'            => [
            Controller\ApiToolsVersionController::class => 'Json',
            Controller\Authentication::class            => 'HalJson',
            Controller\AuthenticationType::class        => 'Json',
            Controller\Authorization::class             => 'HalJson',
            Controller\CacheEnabled::class              => 'Json',
            Controller\ContentNegotiation::class        => 'HalJson',
            Controller\Dashboard::class                 => 'HalJson',
            Controller\DbAdapter::class                 => 'HalJson',
            Controller\DbAutodiscovery::class           => 'Json',
            Controller\DoctrineAdapter::class           => 'HalJson',
            Controller\Documentation::class             => 'HalJson',
            Controller\Filters::class                   => 'Json',
            Controller\FsPermissions::class             => 'Json',
            Controller\HttpBasicAuthentication::class   => 'HalJson',
            Controller\HttpDigestAuthentication::class  => 'HalJson',
            Controller\Hydrators::class                 => 'Json',
            Controller\InputFilter::class               => 'HalJson',
            Controller\Module::class                    => 'HalJson',
            Controller\ModuleCreation::class            => 'HalJson',
            Controller\OAuth2Authentication::class      => 'HalJson',
            Controller\RestService::class               => 'HalJson',
            Controller\RpcService::class                => 'HalJson',
            Controller\SettingsDashboard::class         => 'HalJson',
            Controller\Source::class                    => 'Json',
            Controller\Strategy::class                  => 'Json',
            Controller\Validators::class                => 'Json',
            Controller\Versioning::class                => 'Json',
        ],
        'accept_whitelist'       => [
            Controller\ApiToolsVersionController::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Authentication::class            => [
                'application/json',
                'application/*+json',
            ],
            Controller\Authorization::class             => [
                'application/json',
                'application/*+json',
            ],
            Controller\CacheEnabled::class              => [
                'application/json',
                'application/*+json',
            ],
            Controller\ContentNegotiation::class        => [
                'application/json',
                'application/*+json',
            ],
            Controller\Dashboard::class                 => [
                'application/json',
                'application/*+json',
            ],
            Controller\DbAdapter::class                 => [
                'application/json',
                'application/*+json',
            ],
            Controller\DbAutodiscovery::class           => [
                'application/json',
                'application/*+json',
            ],
            Controller\DoctrineAdapter::class           => [
                'application/json',
                'application/*+json',
            ],
            Controller\Documentation::class             => [
                'application/json',
                'application/*+json',
            ],
            Controller\Filters::class                   => [
                'application/json',
                'application/*+json',
            ],
            Controller\FsPermissions::class             => [
                'application/json',
                'application/*+json',
            ],
            Controller\HttpBasicAuthentication::class   => [
                'application/json',
                'application/*+json',
            ],
            Controller\HttpDigestAuthentication::class  => [
                'application/json',
                'application/*+json',
            ],
            Controller\Hydrators::class                 => [
                'application/json',
                'application/*+json',
            ],
            Controller\InputFilter::class               => [
                'application/json',
                'application/*+json',
            ],
            Controller\Module::class                    => [
                'application/json',
                'application/*+json',
            ],
            Controller\ModuleCreation::class            => [
                'application/json',
                'application/*+json',
            ],
            Controller\OAuth2Authentication::class      => [
                'application/json',
                'application/*+json',
            ],
            Controller\SettingsDashboard::class         => [
                'application/json',
                'application/*+json',
            ],
            Controller\Source::class                    => [
                'application/json',
                'application/*+json',
            ],
            Controller\Strategy::class                  => [
                'application/json',
                'application/*+json',
            ],
            Controller\Validators::class                => [
                'application/json',
                'application/*+json',
            ],
            Controller\Versioning::class                => [
                'application/json',
                'application/*+json',
            ],
            Controller\RestService::class               => [
                'application/json',
                'application/*+json',
            ],
            Controller\RpcService::class                => [
                'application/json',
                'application/*+json',
            ],
        ],
        'content_type_whitelist' => [
            Controller\Authorization::class            => [
                'application/json',
                'application/*+json',
            ],
            Controller\CacheEnabled::class             => [
                'application/json',
                'application/*+json',
            ],
            Controller\ContentNegotiation::class       => [
                'application/json',
                'application/*+json',
            ],
            Controller\Dashboard::class                => [
                'application/json',
                'application/*+json',
            ],
            Controller\DbAdapter::class                => [
                'application/json',
                'application/*+json',
            ],
            Controller\DbAutodiscovery::class          => [
                'application/json',
                'application/*+json',
            ],
            Controller\DoctrineAdapter::class          => [
                'application/json',
                'application/*+json',
            ],
            Controller\Filters::class                  => [
                'application/json',
            ],
            Controller\FsPermissions::class            => [
                'application/json',
                'application/*+json',
            ],
            Controller\Hydrators::class                => [
                'application/json',
            ],
            Controller\HttpBasicAuthentication::class  => [
                'application/json',
                'application/*+json',
            ],
            Controller\HttpDigestAuthentication::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\InputFilter::class              => [
                'application/json',
                'application/*+json',
            ],
            Controller\Module::class                   => [
                'application/json',
                'application/*+json',
            ],
            Controller\ModuleCreation::class           => [
                'application/json',
            ],
            Controller\OAuth2Authentication::class     => [
                'application/json',
                'application/*+json',
            ],
            Controller\SettingsDashboard::class        => [
                'application/json',
                'application/*+json',
            ],
            Controller\Source::class                   => [
                'application/json',
            ],
            Controller\Strategy::class                 => [
                'application/json',
            ],
            Controller\Validators::class               => [
                'application/json',
            ],
            Controller\Versioning::class               => [
                'application/json',
            ],
            Controller\RestService::class              => [
                'application/json',
                'application/*+json',
            ],
            Controller\RpcService::class               => [
                'application/json',
                'application/*+json',
            ],
        ],
    ],
    'api-tools-hal'                 => [
        'metadata_map' => [
            Model\AuthenticationEntity::class         => [
                'hydrator' => 'ArraySerializable',
            ],
            Model\AuthorizationEntity::class          => [
                'hydrator' => 'ArraySerializable',
            ],
            Model\ContentNegotiationEntity::class     => [
                'hydrator'               => 'ArraySerializable',
                'route_identifier_name'  => 'content_name',
                'entity_identifier_name' => 'content_name',
                'route_name'             => 'api-tools/api/content-negotiation',
            ],
            Model\DbConnectedRestServiceEntity::class => [
                'hydrator'               => 'ArraySerializable',
                'route_identifier_name'  => 'controller_service_name',
                'entity_identifier_name' => 'controller_service_name',
                'route_name'             => 'api-tools/api/module/rest-service',
            ],
            Model\DbAdapterEntity::class              => [
                'hydrator'               => 'ArraySerializable',
                'route_identifier_name'  => 'adapter_name',
                'entity_identifier_name' => 'adapter_name',
                'route_name'             => 'api-tools/api/db-adapter',
            ],
            Model\DoctrineAdapterEntity::class        => [
                'hydrator'               => 'ArraySerializable',
                'route_identifier_name'  => 'adapter_name',
                'entity_identifier_name' => 'adapter_name',
                'route_name'             => 'api-tools/api/doctrine-adapter',
            ],
            Model\InputFilterCollection::class        => [
                'route_name'             => 'api-tools/api/module/rest-service/input-filter',
                'is_collection'          => true,
                'collection_name'        => 'input_filter',
                'route_identifier_name'  => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ],
            Model\InputFilterEntity::class            => [
                'hydrator'               => 'ArraySerializable',
                'route_identifier_name'  => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
                'route_name'             => 'api-tools/api/module/rest-service/input-filter',
            ],
            Model\ModuleEntity::class                 => [
                'hydrator'               => 'ArraySerializable',
                'route_identifier_name'  => 'name',
                'entity_identifier_name' => 'name',
                'route_name'             => 'api-tools/api/module',
            ],
            Model\RestInputFilterCollection::class    => [
                'route_name'             => 'api-tools/api/module/rest-service/input-filter',
                'is_collection'          => true,
                'collection_name'        => 'input_filter',
                'route_identifier_name'  => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ],
            Model\RestInputFilterEntity::class        => [
                'hydrator'               => 'ArraySerializable',
                'route_identifier_name'  => 'input_filter_name',
                'route_name'             => 'api-tools/api/module/rest-service/input-filter',
                'entity_identifier_name' => 'input_filter_name',
            ],
            Model\DocumentationEntity::class          => [
                'hydrator'               => 'ArraySerializable',
                'route_identifier_name'  => 'rest_documentation',
                'entity_identifier_name' => 'rest_documentation',
                'route_name'             => 'api-tools/api/module/rest-service/rest-doc',
            ],
            Model\RestServiceEntity::class            => [
                'hydrator'               => 'ArraySerializable',
                'route_identifier_name'  => 'controller_service_name',
                'entity_identifier_name' => 'controller_service_name',
                'route_name'             => 'api-tools/api/module/rest-service',
                'links'                  => [
                    [
                        'rel'   => 'input_filter',
                        'route' => [
                            'name' => 'api-tools/api/module/rest-service/input-filter',
                        ],
                    ],
                    [
                        'rel'   => 'documentation',
                        'route' => [
                            'name' => 'api-tools/api/module/rest-service/doc',
                        ],
                    ],
                ],
            ],
            Model\RpcInputFilterCollection::class     => [
                'route_name'             => 'api-tools/api/module/rpc-service/input-filter',
                'is_collection'          => true,
                'collection_name'        => 'input_filter',
                'route_identifier_name'  => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ],
            Model\RpcInputFilterEntity::class         => [
                'hydrator'               => 'ArraySerializable',
                'route_identifier_name'  => 'input_filter_name',
                'route_name'             => 'api-tools/api/module/rpc-service/input-filter',
                'entity_identifier_name' => 'input_filter_name',
            ],
            Model\RpcServiceEntity::class             => [
                'hydrator'               => 'ArraySerializable',
                'route_identifier_name'  => 'controller_service_name',
                'entity_identifier_name' => 'controller_service_name',
                'route_name'             => 'api-tools/api/module/rpc-service',
                'links'                  => [
                    [
                        'rel'   => 'input_filter',
                        'route' => [
                            'name' => 'api-tools/api/module/rpc-service/input-filter',
                        ],
                    ],
                    [
                        'rel'   => 'documentation',
                        'route' => [
                            'name' => 'api-tools/api/module/rpc-service/doc',
                        ],
                    ],
                ],
            ],
        ],
    ],
    'api-tools-rest'                => [
        Controller\ContentNegotiation::class => [
            'listener'                => Model\ContentNegotiationResource::class,
            'route_name'              => 'api-tools/api/content-negotiation',
            'route_identifier_name'   => 'content_name',
            'entity_class'            => Model\ContentNegotiationEntity::class,
            'entity_http_methods'     => ['GET', 'PATCH', 'DELETE'],
            'collection_http_methods' => ['GET', 'POST'],
            'collection_name'         => 'selectors',
        ],
        Controller\DbAdapter::class          => [
            'listener'                => Model\DbAdapterResource::class,
            'route_name'              => 'api-tools/api/db-adapter',
            'route_identifier_name'   => 'adapter_name',
            'entity_class'            => Model\DbAdapterEntity::class,
            'entity_http_methods'     => ['GET', 'PATCH', 'DELETE'],
            'collection_http_methods' => ['GET', 'POST'],
            'collection_name'         => 'db_adapter',
        ],
        Controller\DoctrineAdapter::class    => [
            'listener'                => Model\DoctrineAdapterResource::class,
            'route_name'              => 'api-tools/api/doctrine-adapter',
            'route_identifier_name'   => 'adapter_name',
            'entity_class'            => Model\DoctrineAdapterEntity::class,
            'entity_http_methods'     => ['GET', 'PATCH', 'DELETE'],
            'collection_http_methods' => ['GET'],
            'collection_name'         => 'doctrine_adapter',
        ],
        Controller\Module::class             => [
            'listener'                => Model\ModuleResource::class,
            'route_name'              => 'api-tools/api/module',
            'route_identifier_name'   => 'name',
            'entity_class'            => Model\ModuleEntity::class,
            'entity_http_methods'     => ['GET', 'DELETE'],
            'collection_http_methods' => ['GET', 'POST'],
            'collection_name'         => 'module',
        ],
        Controller\RpcService::class         => [
            'listener'                   => Model\RpcServiceResource::class,
            'route_name'                 => 'api-tools/api/module/rpc-service',
            'entity_class'               => Model\RpcServiceEntity::class,
            'route_identifier_name'      => 'controller_service_name',
            'entity_http_methods'        => ['GET', 'PATCH', 'DELETE'],
            'collection_http_methods'    => ['GET', 'POST'],
            'collection_name'            => 'rpc',
            'collection_query_whitelist' => ['version'],
        ],
        Controller\RestService::class        => [
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
    'api-tools-rpc'                 => [
        Controller\ApiToolsVersionController::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/api-tools-version',
        ],
        Controller\Authentication::class            => [
            'http_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
            'route_name'   => 'api-tools/api/authentication',
        ],
        Controller\AuthenticationType::class        => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/authentication-type',
        ],
        Controller\Authorization::class             => [
            'http_methods' => ['GET', 'PATCH', 'PUT'],
            'route_name'   => 'api-tools/api/module/authorization',
        ],
        Controller\CacheEnabled::class              => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/cache-enabled',
        ],
        Controller\Config::class                    => [
            'http_methods' => ['GET', 'PATCH'],
            'route_name'   => 'api-tools/api/config',
        ],
        Controller\Dashboard::class                 => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/dashboard',
        ],
        Controller\DbAutodiscovery::class           => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/module/db-autodiscovery',
        ],
        Controller\Documentation::class             => [
            'http_methods' => ['GET', 'PATCH', 'PUT', 'DELETE'],
            'route_name'   => 'api-tools/api/rest-service/rest-doc',
        ],
        Controller\Filters::class                   => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/filters',
        ],
        Controller\FsPermissions::class             => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/fs-permissions',
        ],
        Controller\HttpBasicAuthentication::class   => [
            'http_methods' => ['GET', 'POST', 'PATCH', 'DELETE'],
            'route_name'   => 'api-tools/api/authentication/http-basic',
        ],
        Controller\HttpDigestAuthentication::class  => [
            'http_methods' => ['GET', 'POST', 'PATCH', 'DELETE'],
            'route_name'   => 'api-tools/api/authentication/http-digest',
        ],
        Controller\Hydrators::class                 => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/hydrators',
        ],
        Controller\InputFilter::class               => [
            'http_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
            'route_name'   => 'api-tools/api/rpc-service/input-filter',
        ],
        Controller\ModuleConfig::class              => [
            'http_methods' => ['GET', 'PATCH'],
            'route_name'   => 'api-tools/api/config/module',
        ],
        Controller\ModuleCreation::class            => [
            'http_methods' => ['PUT'],
            'route_name'   => 'api-tools/api/module-enable',
        ],
        Controller\OAuth2Authentication::class      => [
            'http_methods' => ['GET', 'POST', 'PATCH', 'DELETE'],
            'route_name'   => 'api-tools/api/authentication/oauth2',
        ],
        Controller\SettingsDashboard::class         => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/settings-dashboard',
        ],
        Controller\Source::class                    => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/source',
        ],
        Controller\Validators::class                => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/validators',
        ],
        Controller\Versioning::class                => [
            'http_methods' => ['PATCH'],
            'route_name'   => 'api-tools/api/versioning',
        ],
        Controller\Strategy::class                  => [
            'http_methods' => ['GET'],
            'route_name'   => 'api-tools/api/strategy',
        ],
    ],

    /*
     * Metadata for scalar filter options.
     *
     * Each key in the map is a filter plugin name. The value is an array of
     * option key/type pairs. If more than one type is possible, the types are
     * OR'd.
     */
    'filter_metadata'               => [
        Alnum::class                        => [
            'allow_white_space' => 'bool',
            'locale'            => 'string',
        ],
        Alpha::class                        => [
            'allow_white_space' => 'bool',
            'locale'            => 'string',
        ],
        BaseName::class                     => [],
        Boolean::class                      => [
            'casting' => 'bool',
            'type'    => 'string',
        ],
        Callback::class                     => [
            'callback' => 'string',
        ],
        Bz2::class                          => [
            'archive'   => 'string',
            'blocksize' => 'int',
        ],
        Gz::class                           => [
            'archive' => 'string',
            'level'   => 'int',
            'mode'    => 'string',
        ],
        'Laminas\Filter\Compress\Llaminas'  => [],
        Compress::class                     => [
            'adapter' => 'string',
        ],
        Rar::class                          => [
            'archive'  => 'string',
            'callback' => 'string',
            'password' => 'string',
            'target'   => 'string',
        ],
        Snappy::class                       => [],
        Tar::class                          => [
            'archive' => 'string',
            'target'  => 'string',
            'mode'    => 'string',
        ],
        Zip::class                          => [
            'archive' => 'string',
            'target'  => 'string',
        ],
        DateTimeFormatter::class            => [
            'format' => 'string',
        ],
        Decompress::class                   => [
            'adapter' => 'string',
        ],
        Decrypt::class                      => [
            'adapter' => 'string',
        ],
        Digits::class                       => [],
        Dir::class                          => [],
        BlockCipher::class                  => [
            'algorithm'     => 'string',
            'compression'   => 'string',
            'hash'          => 'string',
            'key'           => 'string',
            'key_iteration' => 'int',
            'vector'        => 'string',
        ],
        Openssl::class                      => [
            'compression' => 'string',
            'package'     => 'bool',
            'passphrase'  => 'string',
        ],
        Encrypt::class                      => [
            'adapter' => 'string',
        ],
        \Laminas\Filter\File\Decrypt::class => [
            'adapter'  => 'string',
            'filename' => 'string',
        ],
        \Laminas\Filter\File\Encrypt::class => [
            'adapter'  => 'string',
            'filename' => 'string',
        ],
        LowerCase::class                    => [
            'encoding' => 'string',
        ],
        Rename::class                       => [
            'overwrite' => 'bool',
            'randomize' => 'bool',
            'source'    => 'string',
            'target'    => 'string',
        ],
        RenameUpload::class                 => [
            'overwrite'            => 'bool',
            'randomize'            => 'bool',
            'target'               => 'string',
            'use_upload_extension' => 'bool',
            'use_upload_name'      => 'bool',
        ],
        UpperCase::class                    => [
            'encoding' => 'string',
        ],
        HtmlEntities::class                 => [
            'charset'     => 'string',
            'doublequote' => 'bool',
            'encoding'    => 'string',
            'quotestyle'  => 'int',
        ],
        Inflector::class                    => [
            'throwTargetExceptionsOn'     => 'bool',
            'targetReplacementIdentifier' => 'string',
            'target'                      => 'string',
        ],
        NumberFormat::class                 => [
            'locale' => 'string',
            'style'  => 'int',
            'type'   => 'int',
        ],
        NumberParse::class                  => [
            'locale' => 'string',
            'style'  => 'int',
            'type'   => 'int',
        ],
        PregReplace::class                  => [
            'pattern'     => 'string',
            'replacement' => 'string',
        ],
        RealPath::class                     => [
            'exists' => 'bool',
        ],
        StringToLower::class                => [
            'encoding' => 'string',
        ],
        StringToUpper::class                => [
            'encoding' => 'string',
        ],
        StringTrim::class                   => [
            'charlist' => 'string',
        ],
        StripNewlines::class                => [],
        StripTags::class                    => [
            'allowAttribs' => 'string',
            'allowTags'    => 'string',
        ],
        ToInt::class                        => [],
        ToNull::class                       => [
            'type' => 'int|string',
        ],
        UriNormalize::class                 => [
            'defaultscheme'  => 'string',
            'enforcedscheme' => 'string',
        ],
        CamelCaseToDash::class              => [],
        CamelCaseToSeparator::class         => [
            'separator' => 'string',
        ],
        CamelCaseToUnderscore::class        => [],
        DashToCamelCase::class              => [],
        DashToSeparator::class              => [
            'separator' => 'string',
        ],
        DashToUnderscore::class             => [],
        SeparatorToCamelCase::class         => [
            'separator' => 'string',
        ],
        SeparatorToDash::class              => [
            'separator' => 'string',
        ],
        SeparatorToSeparator::class         => [
            'searchseparator'      => 'string',
            'replacementseparator' => 'string',
        ],
        UnderscoreToCamelCase::class        => [],
        UnderscoreToDash::class             => [],
        UnderscoreToSeparator::class        => [
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
    'validator_metadata'            => [
        '__all__'                                                       => [
            'breakchainonfailure'  => 'bool',
            'message'              => 'string',
            'messagelength'        => 'int',
            'valueobscured'        => 'bool',
            'translatortextdomain' => 'string',
            'translatorenabled'    => 'bool',
        ],
        Codabar::class                                                  => [],
        Code128::class                                                  => [],
        Code25interleaved::class                                        => [],
        Code25::class                                                   => [],
        Code39ext::class                                                => [],
        Code39::class                                                   => [],
        Code93ext::class                                                => [],
        Code93::class                                                   => [],
        Ean12::class                                                    => [],
        Ean13::class                                                    => [],
        Ean14::class                                                    => [],
        Ean18::class                                                    => [],
        Ean2::class                                                     => [],
        Ean5::class                                                     => [],
        Ean8::class                                                     => [],
        Gtin12::class                                                   => [],
        Gtin13::class                                                   => [],
        Gtin14::class                                                   => [],
        Identcode::class                                                => [],
        Intelligentmail::class                                          => [],
        Issn::class                                                     => [],
        Itf14::class                                                    => [],
        Leitcode::class                                                 => [],
        Barcode::class                                                  => [
            'adapter'     => 'string',
            // this is the validator adapter name to use
            'useChecksum' => 'bool',
        ],
        Planet::class                                                   => [],
        Postnet::class                                                  => [],
        Royalmail::class                                                => [],
        Sscc::class                                                     => [],
        Upca::class                                                     => [],
        Upce::class                                                     => [],
        Between::class                                                  => [
            'inclusive' => 'bool',
            'max'       => 'int',
            'min'       => 'int',
        ],
        Bitwise::class                                                  => [
            'control'  => 'int',
            'operator' => 'string',
            'strict'   => 'bool',
        ],
        \Laminas\Validator\Callback::class                              => [
            'callback' => 'string',
        ],
        CreditCard::class                                               => [
            'type'    => 'string',
            'service' => 'string',
        ],
        Csrf::class                                                     => [
            'name'    => 'string',
            'salt'    => 'string',
            'timeout' => 'int',
        ],
        Date::class                                                     => [
            'format' => 'string',
        ],
        DateStep::class                                                 => [
            'format'    => 'string',
            'basevalue' => 'string|int',
        ],
        NoRecordExists::class                                           => [
            'table'   => 'string',
            'schema'  => 'string',
            'field'   => 'string',
            'exclude' => 'string',
        ],
        RecordExists::class                                             => [
            'table'   => 'string',
            'schema'  => 'string',
            'field'   => 'string',
            'exclude' => 'string',
        ],
        'Laminas\ApiTools\ContentValidation\Validator\DbNoRecordExists' => [
            'adapter' => 'string',
            'table'   => 'string',
            'schema'  => 'string',
            'field'   => 'string',
            'exclude' => 'string',
        ],
        'Laminas\ApiTools\ContentValidation\Validator\DbRecordExists'   => [
            'adapter' => 'string',
            'table'   => 'string',
            'schema'  => 'string',
            'field'   => 'string',
            'exclude' => 'string',
        ],
        \Laminas\Validator\Digits::class                                => [],
        EmailAddress::class                                             => [
            'allow'          => 'int',
            'useMxCheck'     => 'bool',
            'useDeepMxCheck' => 'bool',
            'useDomainCheck' => 'bool',
        ],
        Explode::class                                                  => [
            'valuedelimiter'      => 'string',
            'breakonfirstfailure' => 'bool',
        ],
        Count::class                                                    => [
            'max' => 'int',
            'min' => 'int',
        ],
        Crc32::class                                                    => [
            'algorithm' => 'string',
            'hash'      => 'string',
            'crc32'     => 'string',
        ],
        ExcludeExtension::class                                         => [
            'case'      => 'bool',
            'extension' => 'string',
        ],
        ExcludeMimeType::class                                          => [
            'disableMagicFile'  => 'bool',
            'magicFile'         => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType'          => 'string',
        ],
        Exists::class                                                   => [
            'directory' => 'string',
        ],
        Extension::class                                                => [
            'case'      => 'bool',
            'extension' => 'string',
        ],
        FilesSize::class                                                => [
            'max'           => 'int',
            'min'           => 'int',
            'size'          => 'int',
            'useByteString' => 'bool',
        ],
        Hash::class                                                     => [
            'algorithm' => 'string',
            'hash'      => 'string',
        ],
        ImageSize::class                                                => [
            'maxHeight' => 'int',
            'minHeight' => 'int',
            'maxWidth'  => 'int',
            'minWidth'  => 'int',
        ],
        IsCompressed::class                                             => [
            'disableMagicFile'  => 'bool',
            'magicFile'         => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType'          => 'string',
        ],
        IsImage::class                                                  => [
            'disableMagicFile'  => 'bool',
            'magicFile'         => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType'          => 'string',
        ],
        Md5::class                                                      => [
            'algorithm' => 'string',
            'hash'      => 'string',
            'md5'       => 'string',
        ],
        MimeType::class                                                 => [
            'disableMagicFile'  => 'bool',
            'magicFile'         => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType'          => 'string',
        ],
        NotExists::class                                                => [
            'directory' => 'string',
        ],
        Sha1::class                                                     => [
            'algorithm' => 'string',
            'hash'      => 'string',
            'sha1'      => 'string',
        ],
        Size::class                                                     => [
            'max'           => 'int',
            'min'           => 'int',
            'size'          => 'int',
            'useByteString' => 'bool',
        ],
        UploadFile::class                                               => [],
        Upload::class                                                   => [],
        WordCount::class                                                => [
            'max' => 'int',
            'min' => 'int',
        ],
        GreaterThan::class                                              => [
            'inclusive' => 'bool',
            'min'       => 'int',
        ],
        Hex::class                                                      => [],
        Hostname::class                                                 => [
            'allow'       => 'int',
            'useIdnCheck' => 'bool',
            'useTldCheck' => 'bool',
        ],
        Iban::class                                                     => [
            'country_code'   => 'string',
            'allow_non_sepa' => 'bool',
        ],
        Identical::class                                                => [
            'literal' => 'bool',
            'strict'  => 'bool',
            'token'   => 'string',
        ],
        InArray::class                                                  => [
            'strict'    => 'bool',
            'recursive' => 'bool',
        ],
        Ip::class                                                       => [
            'allowipv4'      => 'bool',
            'allowipv6'      => 'bool',
            'allowipvfuture' => 'bool',
            'allowliteral'   => 'bool',
        ],
        Isbn::class                                                     => [
            'type'      => 'string',
            'separator' => 'string',
        ],
        IsInstanceOf::class                                             => [
            'classname' => 'string',
        ],
        LessThan::class                                                 => [
            'inclusive' => 'bool',
            'max'       => 'int',
        ],
        NotEmpty::class                                                 => [
            'type' => 'int',
        ],
        Regex::class                                                    => [
            'pattern' => 'string',
        ],
        Changefreq::class                                               => [],
        Lastmod::class                                                  => [],
        Loc::class                                                      => [],
        Priority::class                                                 => [],
        Step::class                                                     => [
            'baseValue' => 'int|float',
            'step'      => 'float',
        ],
        StringLength::class                                             => [
            'max'      => 'int',
            'min'      => 'int',
            'encoding' => 'string',
        ],
        Uri::class                                                      => [
            'allowAbsolute' => 'bool',
            'allowRelative' => 'bool',
        ],
        Uuid::class                                                     => [],
        \Laminas\I18n\Validator\Alnum::class                            => [
            'allowwhitespace' => 'bool',
        ],
        \Laminas\I18n\Validator\Alpha::class                            => [
            'allowwhitespace' => 'bool',
        ],
        DateTime::class                                                 => [
            'calendar' => 'int',
            'datetype' => 'int',
            'pattern'  => 'string',
            'timetype' => 'int',
            'timezone' => 'string',
            'locale'   => 'string',
        ],
        IsFloat::class                                                  => [
            'locale' => 'string',
        ],
        IsInt::class                                                    => [
            'locale' => 'string',
            'strict' => 'bool',
        ],
        PhoneNumber::class                                              => [
            'country'        => 'string',
            'allow_possible' => 'bool',
        ],
        PostCode::class                                                 => [
            'locale'  => 'string',
            'format'  => 'string',
            'service' => 'string',
        ],
    ],
    'input_filters'                 => [
        'aliases'   => [
            'Laminas\ApiTools\Admin\InputFilter\BasicAuth'                       => BasicInputFilter::class,
            'Laminas\ApiTools\Admin\InputFilter\DigestAuth'                      => DigestInputFilter::class,
            'Laminas\ApiTools\Admin\InputFilter\OAuth2'                          => OAuth2InputFilter::class,
            'Laminas\ApiTools\Admin\InputFilter\Authorization'                   => AuthorizationInputFilter::class,
            'Laminas\ApiTools\Admin\InputFilter\ContentNegotiation'              => ContentNegotiationInputFilter::class,
            'Laminas\ApiTools\Admin\InputFilter\CreateContentNegotiation'        => CreateContentNegotiationInputFilter::class,
            'Laminas\ApiTools\Admin\InputFilter\DbAdapter'                       => DbAdapterInputFilter::class,
            'Laminas\ApiTools\Admin\InputFilter\Documentation'                   => DocumentationInputFilter::class,
            'Laminas\ApiTools\Admin\InputFilter\Module'                          => ModuleInputFilter::class,
            'Laminas\ApiTools\Admin\InputFilter\RestService\PATCH'               => RestPatchInputFilter::class,
            'Laminas\ApiTools\Admin\InputFilter\RestService\POST'                => RestPostInputFilter::class,
            'Laminas\ApiTools\Admin\InputFilter\RpcService\PATCH'                => RpcPatchInputFilter::class,
            'Laminas\ApiTools\Admin\InputFilter\RpcService\POST'                 => RpcPostInputFilter::class,
            'Laminas\ApiTools\Admin\InputFilter\Version'                         => VersionInputFilter::class,

            // Legacy Zend Framework aliases
            'ZF\Apigility\Admin\InputFilter\Authentication\BasicAuth'            => BasicInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\Authentication\DigestAuth'           => DigestInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\Authentication\OAuth2'               => OAuth2InputFilter::class,
            'ZF\Apigility\Admin\InputFilter\Authorization'                       => AuthorizationInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\ContentNegotiation'                  => ContentNegotiationInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\CreateContentNegotiation'            => CreateContentNegotiationInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\DbAdapter'                           => DbAdapterInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\Documentation'                       => DocumentationInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\Module'                              => ModuleInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\RestService\PATCH'                   => RestPatchInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\RestService\POST'                    => RestPostInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\RpcService\PATCH'                    => RpcPatchInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\RpcService\POST'                     => RpcPatchInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\Version'                             => VersionInputFilter::class,

            // Legacy Zend Framework aliases v2
            'ZF\Apigility\Admin\InputFilter\Authentication\BasicInputFilter'     => BasicInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\Authentication\DigestInputFilter'    => DigestInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\Authentication\OAuth2InputFilter'    => OAuth2InputFilter::class,
            'ZF\Apigility\Admin\InputFilter\AuthorizationInputFilter'            => AuthorizationInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\ContentNegotiationInputFilter'       => ContentNegotiationInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\CreateContentNegotiationInputFilter' => CreateContentNegotiationInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\DbAdapterInputFilter'                => DbAdapterInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\DocumentationInputFilter'            => DocumentationInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\ModuleInputFilter'                   => ModuleInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\PatchInputFilter'                    => RestPatchInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\PostInputFilter'                     => RestPostInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\RpcService\PatchInputFilter'         => RpcPatchInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\RpcService\PostInputFilter'          => RpcPatchInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\VersionInputFilter'                  => VersionInputFilter::class,
            'ZF\Apigility\Admin\InputFilter\InputFilter'                         => InputFilterInputFilter::class,
        ],
        'factories' => [
            BasicInputFilter::class                    => InvokableFactory::class,
            DigestInputFilter::class                   => InvokableFactory::class,
            OAuth2InputFilter::class                   => InvokableFactory::class,
            AuthorizationInputFilter::class            => InvokableFactory::class,
            ContentNegotiationInputFilter::class       => InvokableFactory::class,
            CreateContentNegotiationInputFilter::class => InvokableFactory::class,
            DbAdapterInputFilter::class                => InvokableFactory::class,
            DocumentationInputFilter::class            => InvokableFactory::class,
            ModuleInputFilter::class                   => InvokableFactory::class,
            RestPatchInputFilter::class                => InvokableFactory::class,
            RestPostInputFilter::class                 => InvokableFactory::class,
            RpcPatchInputFilter::class                 => InvokableFactory::class,
            RpcPostInputFilter::class                  => InvokableFactory::class,
            VersionInputFilter::class                  => InvokableFactory::class,
            InputFilterInputFilter::class              => InputFilterInputFilterFactory::class,
        ],
    ],
    'api-tools-content-validation'  => [
        Controller\HttpBasicAuthentication::class  => [
            'input_filter' => BasicInputFilter::class,
        ],
        Controller\HttpDigestAuthentication::class => [
            'input_filter' => DigestInputFilter::class,
        ],
        Controller\OAuth2Authentication::class     => [
            'input_filter' => OAuth2InputFilter::class,
        ],
        Controller\DbAdapter::class                => [
            'input_filter' => DbAdapterInputFilter::class,
        ],
        Controller\ContentNegotiation::class       => [
            'input_filter' => ContentNegotiationInputFilter::class,
            'POST'         => CreateContentNegotiationInputFilter::class,
        ],
        Controller\Module::class                   => [
            'POST' => ModuleInputFilter::class,
        ],
        Controller\Versioning::class               => [
            'PATCH' => VersionInputFilter::class,
        ],
        Controller\RestService::class              => [
            'POST'  => RestPostInputFilter::class,
            // for the collection
            'PATCH' => RestPatchInputFilter::class,
            // for the entity
        ],
        Controller\RpcService::class               => [
            'POST'  => RpcPostInputFilter::class,
            // for the collection
            'PATCH' => RpcPatchInputFilter::class,
            // for the entity
        ],
        Controller\InputFilter::class              => [
            'input_filter' => InputFilterInputFilter::class,
        ],
        Controller\Documentation::class            => [
            'input_filter' => DocumentationInputFilter::class,
        ],
        Controller\Authorization::class            => [
            'input_filter' => AuthorizationInputFilter::class,
        ],
    ],
];
