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

    'service_manager' => array(
        'factories' => array(
            'Laminas\ApiTools\Admin\Model\HydratorsModel' => 'Laminas\ApiTools\Admin\Model\HydratorsModelFactory',
            'Laminas\ApiTools\Admin\Model\ValidatorMetadataModel' => 'Laminas\ApiTools\Admin\Model\ValidatorMetadataModelFactory',
            'Laminas\ApiTools\Admin\Model\ValidatorsModel' => 'Laminas\ApiTools\Admin\Model\ValidatorsModelFactory',
            'Laminas\ApiTools\Admin\Model\InputFilterModel' => 'Laminas\ApiTools\Admin\Model\InputFilterModelFactory',
        ),
    ),

    'controllers' => array(
        'invokables' => array(
            'Laminas\ApiTools\Admin\Controller\App' => 'Laminas\ApiTools\Admin\Controller\AppController',
        ),
        'factories' => array(
            'Laminas\ApiTools\Admin\Controller\Hydrators' => 'Laminas\ApiTools\Admin\Controller\HydratorsControllerFactory',
            'Laminas\ApiTools\Admin\Controller\Validators' => 'Laminas\ApiTools\Admin\Controller\ValidatorsControllerFactory',
            'Laminas\ApiTools\Admin\Controller\InputFilter' => 'Laminas\ApiTools\Admin\Controller\InputFilterControllerFactory',
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
                                        'action'     => 'source',
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
                                            ),
                                        ),
                                        'may_terminate' => true,
                                        'child_routes' => array(
                                            'rpc_input_filter' => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                    'route' => '/inputfilter[/:input_filter_name]',
                                                    'defaults' => array(
                                                        'controller' => 'Laminas\ApiTools\Admin\Controller\InputFilter',
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
                                            ),
                                        ),
                                        'may_terminate' => true,
                                        'child_routes' => array(
                                            'rest_input_filter' => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                    'route' => '/inputfilter[/:input_filter_name]',
                                                    'defaults' => array(
                                                        'controller' => 'Laminas\ApiTools\Admin\Controller\InputFilter',
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
            'Laminas\ApiTools\Admin\Controller\Hydrators'      => 'Json',
            'Laminas\ApiTools\Admin\Controller\InputFilter'    => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\ModuleCreation' => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\Module'         => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\RestService'    => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\RpcService'     => 'HalJson',
            'Laminas\ApiTools\Admin\Controller\Source'         => 'Json',
            'Laminas\ApiTools\Admin\Controller\Validators'     => 'Json',
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
            'Laminas\ApiTools\Admin\Model\InputFilterCollection' => array(
                'route_name'      => 'api-tools-admin/api/module/rest-service/rest_input_filter',
                'is_collection'   => true,
                'collection_name' => 'input_filter',
            ),
            'Laminas\ApiTools\Admin\Model\InputFilterEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'input_filter_name',
                'route_name'      => 'api-tools-admin/api/module/rest-service/rest_input_filter',
            ),
            'Laminas\ApiTools\Admin\Model\ModuleEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'name',
                'route_name'      => 'api-tools-admin/api/module',
            ),
            'Laminas\ApiTools\Admin\Model\RestInputFilterCollection' => array(
                'route_name'      => 'api-tools-admin/api/module/rest-service/rest_input_filter',
                'is_collection'   => true,
                'collection_name' => 'input_filter',
            ),
            'Laminas\ApiTools\Admin\Model\RestInputFilterEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'input_filter_name',
                'route_name'      => 'api-tools-admin/api/module/rest-service/rest_input_filter',
            ),
            'Laminas\ApiTools\Admin\Model\RestServiceEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'controller_service_name',
                'route_name'      => 'api-tools-admin/api/module/rest-service',
                'links'           => array(
                    array(
                        'rel' => 'input_filter',
                        'route' => array(
                            'name' => 'api-tools-admin/api/module/rest-service/rest_input_filter'
                        ),
                    )
                ),
            ),
            'Laminas\ApiTools\Admin\Model\RpcInputFilterCollection' => array(
                'route_name'      => 'api-tools-admin/api/module/rpc-service/rpc_input_filter',
                'is_collection'   => true,
                'collection_name' => 'input_filter',
            ),
            'Laminas\ApiTools\Admin\Model\RpcInputFilterEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'input_filter_name',
                'route_name'      => 'api-tools-admin/api/module/rpc-service/rpc_input_filter',
            ),
            'Laminas\ApiTools\Admin\Model\RpcServiceEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'controller_service_name',
                'route_name'      => 'api-tools-admin/api/module/rpc-service',
                'links'           => array(
                    array(
                        'rel' => 'input_filter',
                        'route' => array(
                            'name' => 'api-tools-admin/api/module/rpc-service/rpc_input_filter'
                        ),
                    )
                ),
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
        'Laminas\ApiTools\Admin\Controller\Hydrators' => array(
            'http_methods' => array('GET'),
            'route_name'   => 'api-tools-admin/api/hydrators',
        ),
        'Laminas\ApiTools\Admin\Controller\InputFilter' => array(
            'http_methods' => array('GET', 'POST', 'PUT', 'DELETE'),
            'route_name'   => 'api-tools-admin/api/rpc-service/rpc_input_filter',
        ),
        'Laminas\ApiTools\Admin\Controller\ModuleCreation' => array(
            'http_methods' => array('PUT'),
            'route_name'   => 'api-tools-admin/api/module-enable',
        ),
        'Laminas\ApiTools\Admin\Controller\Source' => array(
            'http_methods' => array('GET'),
            'route_name'   => 'api-tools-admin/api/source',
        ),
        'Laminas\ApiTools\Admin\Controller\Validators' => array(
            'http_methods' => array('GET'),
            'route_name'   => 'api-tools-admin/api/validators',
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
            'break_chain_on_failure' => 'bool',
            'message' => 'string',
            'messagelength' => 'int',
            'valueobscured' => 'bool',
            'translatortextdomain' => 'string',
            'translatorenabled' => 'bool',
        ),
        'barcodecodabar' => array(),
        'barcodecode128' => array(),
        'barcodecode25interleaved' => array(),
        'barcodecode25' => array(),
        'barcodecode39ext' => array(),
        'barcodecode39' => array(),
        'barcodecode93ext' => array(),
        'barcodecode93' => array(),
        'barcodeean12' => array(),
        'barcodeean13' => array(),
        'barcodeean14' => array(),
        'barcodeean18' => array(),
        'barcodeean2' => array(),
        'barcodeean5' => array(),
        'barcodeean8' => array(),
        'barcodegtin12' => array(),
        'barcodegtin13' => array(),
        'barcodegtin14' => array(),
        'barcodeidentcode' => array(),
        'barcodeintelligentmail' => array(),
        'barcodeissn' => array(),
        'barcodeitf14' => array(),
        'barcodeleitcode' => array(),
        'barcode' => array(
            'adapter' => 'string', // this is the validator adapter name to use
            'useChecksum' => 'bool',
        ),
        'barcodeplanet' => array(),
        'barcodepostnet' => array(),
        'barcoderoyalmail' => array(),
        'barcodesscc' => array(),
        'barcodeupca' => array(),
        'barcodeupce' => array(),
        'between' => array(
            'inclusive' => 'bool',
            'max' => 'int',
            'min' => 'int',
        ),
        'bitwise' => array(
            'control' => 'int',
            'operator' => 'string',
            'strict' => 'bool',
        ),
        'callback' => array(
            'callback' => 'string',
        ),
        'creditcard' => array(
            'type' => 'string',
            'service' => 'string',
        ),
        'csrf' => array(
            'name' => 'string',
            'salt' => 'string',
            'timeout' => 'int',
        ),
        'date' => array(
            'format' => 'string',
        ),
        'datestep' => array(
            'format' => 'string',
            'basevalue' => 'string|int',
        ),
        'dbnorecordexists' => array(
            'table' => 'string',
            'schema' => 'string',
            'field' => 'string',
            'exclude' => 'string',
        ),
        'dbrecordexists' => array(
            'table' => 'string',
            'schema' => 'string',
            'field' => 'string',
            'exclude' => 'string',
        ),
        'digits' => array(),
        'emailaddress' => array(
            'allow' => 'int',
            'useMxCheck' => 'bool',
            'useDeepMxCheck' => 'bool',
            'useDomainCheck' => 'bool',
        ),
        'explode' => array(
            'valuedelimiter' => 'string',
            'breakonfirstfailure' => 'bool',
        ),
        'filecount' => array(
            'max' => 'int',
            'min' => 'int',
        ),
        'filecrc32' => array(
            'algorithm' => 'string',
            'hash' => 'string',
            'crc32' => 'string',
        ),
        'fileexcludeextension' => array(
            'case' => 'bool',
            'extension' => 'string',
        ),
        'fileexcludemimetype' => array(
            'disableMagicFile' => 'bool',
            'magicFile' => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType' => 'string',
        ),
        'fileexists' => array(
            'directory' => 'string',
        ),
        'fileextension' => array(
            'case' => 'bool',
            'extension' => 'string',
        ),
        'filefilessize' => array(
            'max' => 'int',
            'min' => 'int',
            'size' => 'int',
            'useByteString' => 'bool',
        ),
        'filehash' => array(
            'algorithm' => 'string',
            'hash' => 'string',
        ),
        'fileimagesize' => array(
            'maxHeight' => 'int',
            'minHeight' => 'int',
            'maxWidth' => 'int',
            'minWidth' => 'int',
        ),
        'fileiscompressed' => array(
            'disableMagicFile' => 'bool',
            'magicFile' => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType' => 'string',
        ),
        'fileisimage' => array(
            'disableMagicFile' => 'bool',
            'magicFile' => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType' => 'string',
        ),
        'filemd5' => array(
            'algorithm' => 'string',
            'hash' => 'string',
            'md5' => 'string',
        ),
        'filemimetype' => array(
            'disableMagicFile' => 'bool',
            'magicFile' => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType' => 'string',
        ),
        'filenotexists' => array(
            'directory' => 'string',
        ),
        'filesha1' => array(
            'algorithm' => 'string',
            'hash' => 'string',
            'sha1' => 'string',
        ),
        'filesize' => array(
            'max' => 'int',
            'min' => 'int',
            'size' => 'int',
            'useByteString' => 'bool',
        ),
        'fileuploadfile' => array(),
        'fileupload' => array(),
        'filewordcount' => array(
            'max' => 'int',
            'min' => 'int',
        ),
        'greaterthan' => array(
            'inclusive' => 'bool',
            'min' => 'int',
        ),
        'hex' => array(),
        'hostname' => array(
            'allow' => 'int',
            'useIdnCheck' => 'bool',
            'useTldCheck' => 'bool',
        ),
        'iban' => array(
            'country_code' => 'string',
            'allow_non_sepa' => 'bool',
        ),
        'identical' => array(
            'literal' => 'bool',
            'strict' => 'bool',
            'token' => 'string',
        ),
        'inarray' => array(
            'strict' => 'bool',
            'recursive' => 'bool',
        ),
        'ip' => array(
            'allowipv4' => 'bool',
            'allowipv6' => 'bool',
            'allowipvfuture' => 'bool',
            'allowliteral' => 'bool',
        ),
        'isbn' => array(
            'type' => 'string',
            'separator' => 'string',
        ),
        'isinstanceof' => array(
            'classname' => 'string',
        ),
        'lessthan' => array(
            'inclusive' => 'bool',
            'max' => 'int',
        ),
        'notempty' => array(
            'type' => 'int',
        ),
        'regex' => array(
            'pattern' => 'string',
        ),
        'sitemapchangefreq' => array(),
        'sitemaplastmod' => array(),
        'sitemaploc' => array(),
        'sitemappriority' => array(),
        'step' => array(
            'baseValue' => 'int|float',
            'step' => 'float',
        ),
        'stringlength' => array(
            'max' => 'int',
            'min' => 'int',
            'encoding' => 'string',
        ),
        'uri' => array(
            'allowAbsolute' => 'bool',
            'allowRelative' => 'bool',
        ),
        'alnum' => array(
            'allowwhitespace' => 'bool',
        ),
        'alpha' => array(
            'allowwhitespace' => 'bool',
        ),
        'datetime' => array(
            'calendar' => 'int',
            'datetype' => 'int',
            'pattern' => 'string',
            'timetype' => 'int',
            'timezone' => 'string',
            'locale' => 'string',
        ),
        'float' => array(
            'locale' => 'string',
        ),
        'int' => array(
            'locale' => 'string',
        ),
        'phonenumber' => array(
            'country' => 'string',
            'allow_possible' => 'bool',
        ),
        'postcode' => array(
            'locale' => 'string',
            'format' => 'string',
            'service' => 'string',
        ),
    ),
);
