<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ApiTools\Configuration\Exception\InvalidArgumentException as InvalidArgumentConfiguration;
use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ApiTools\Configuration\ResourceFactory as ConfigResourceFactory;

use function array_intersect_key;
use function array_key_exists;
use function dirname;
use function file_exists;
use function sprintf;

class DocumentationModel
{
    public const TYPE_REST = 'rest';
    public const TYPE_RPC  = 'rpc';

    /** @var ConfigResourceFactory */
    protected $configFactory;

    /** @var ModuleUtils */
    protected $moduleUtils;

    public function __construct(ConfigResourceFactory $configFactory, ModuleUtils $moduleUtils)
    {
        $this->configFactory = $configFactory;
        $this->moduleUtils   = $moduleUtils;
    }

    /**
     * @param string $type
     * @throws InvalidArgumentConfiguration When an invalid $type is provided.
     * @psalm-param self::TYPE_* $type
     * @psalm-return array{
     *     description: null|string,
     *     collection: array{
     *         description: null|string,
     *         GET: array{
     *             identifier: null|string,
     *             description: null|string,
     *             request: null|string,
     *             response: null|string
     *         },
     *         POST: array{
     *             identifier: null|string,
     *             description: null|string,
     *             request: null|string,
     *             response: null|string
     *         },
     *         PUT: array{
     *             identifier: null|string,
     *             description: null|string,
     *             request: null|string,
     *             response: null|string
     *         },
     *         PATCH: array{
     *             identifier: null|string,
     *             description: null|string,
     *             request: null|string,
     *             response: null|string
     *         },
     *         DELETE: array{
     *             identifier: null|string,
     *             description: null|string,
     *             request: null|string,
     *             response: null|string
     *         }
     *     },
     *     entity: array{
     *         description: null|string,
     *         GET: array{
     *             identifier: null|string,
     *             description: null|string,
     *             request: null|string,
     *             response: null|string
     *         },
     *         POST: array{
     *             identifier: null|string,
     *             description: null|string,
     *             request: null|string,
     *             response: null|string
     *         },
     *         PUT: array{
     *             identifier: null|string,
     *             description: null|string,
     *             request: null|string,
     *             response: null|string
     *         },
     *         PATCH: array{
     *             identifier: null|string,
     *             description: null|string,
     *             request: null|string,
     *             response: null|string
     *         },
     *         DELETE: array{
     *             identifier: null|string,
     *             description: null|string,
     *             request: null|string,
     *             response: null|string
     *         }
     *     }
     * }|array{
     *     description: null|string,
     *     GET: array{
     *         identifier: null|string,
     *         description: null|string,
     *         request: null|string,
     *         response: null|string
     *     },
     *     POST: array{
     *         identifier: null|string,
     *         description: null|string,
     *         request: null|string,
     *         response: null|string
     *     },
     *     PUT: array{
     *         identifier: null|string,
     *         description: null|string,
     *         request: null|string,
     *         response: null|string
     *     },
     *     PATCH: array{
     *         identifier: null|string,
     *         description: null|string,
     *         request: null|string,
     *         response: null|string
     *     },
     *     DELETE: array{
     *         identifier: null|string,
     *         description: null|string,
     *         request: null|string,
     *         response: null|string
     *     }
     * }
     */
    public function getSchemaTemplate($type = self::TYPE_REST): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        switch ($type) {
            case self::TYPE_REST:
                return [
                    'collection'  => [
                        'description' => null,
                        'GET'         => ['identifier' => null, 'description' => null, 'request' => null, 'response' => null],
                        'POST'        => ['identifier' => null, 'description' => null, 'request' => null, 'response' => null],
                        'PUT'         => ['identifier' => null, 'description' => null, 'request' => null, 'response' => null],
                        'PATCH'       => ['identifier' => null, 'description' => null, 'request' => null, 'response' => null],
                        'DELETE'      => ['identifier' => null, 'description' => null, 'request' => null, 'response' => null],
                    ],
                    'entity'      => [
                        'description' => null,
                        'GET'         => ['identifier' => null, 'description' => null, 'request' => null, 'response' => null],
                        'POST'        => ['identifier' => null, 'description' => null, 'request' => null, 'response' => null],
                        'PUT'         => ['identifier' => null, 'description' => null, 'request' => null, 'response' => null],
                        'PATCH'       => ['identifier' => null, 'description' => null, 'request' => null, 'response' => null],
                        'DELETE'      => ['identifier' => null, 'description' => null, 'request' => null, 'response' => null],
                    ],
                    'description' => null,
                ];
            case self::TYPE_RPC:
                return [
                    'description' => null,
                    'GET'         => ['identifier' => null, 'description' => null, 'request' => null, 'response' => null],
                    'POST'        => ['identifier' => null, 'description' => null, 'request' => null, 'response' => null],
                    'PUT'         => ['identifier' => null, 'description' => null, 'request' => null, 'response' => null],
                    'PATCH'       => ['identifier' => null, 'description' => null, 'request' => null, 'response' => null],
                    'DELETE'      => ['identifier' => null, 'description' => null, 'request' => null, 'response' => null],
                ];
            default:
                throw new InvalidArgumentConfiguration(sprintf(
                    'Only schema types of "rest" or "rpc" are supported by %s',
                    __METHOD__
                ));
        }
        // phpcs:enable
    }

    /**
     * @param string $module
     * @param string $controllerServiceName
     * @return array
     */
    public function fetchDocumentation($module, $controllerServiceName)
    {
        $configResource = $this->getDocumentationConfigResource($module);
        $value          = $configResource->fetch(true);
        if (isset($value[$controllerServiceName])) {
            return $value[$controllerServiceName];
        }
        return [];
    }

    /**
     * @param string $module
     * @param string $controllerType
     * @param string $controllerServiceName
     * @param array|string $documentation
     * @param bool $replace
     * @return string|array<string|string>
     */
    public function storeDocumentation(
        $module,
        $controllerType,
        $controllerServiceName,
        $documentation,
        $replace = false
    ) {
        $configResource    = $this->getDocumentationConfigResource($module);
        $template          = [$controllerServiceName => $this->getSchemaTemplate($controllerType)];
        $templateFlat      = $configResource->traverseArray($template);
        $documentationFlat = $configResource->traverseArray([$controllerServiceName => $documentation]);

        $validDocumentationFlat = array_intersect_key($documentationFlat, $templateFlat);

        if ($replace) {
            $configResource->deleteKey($controllerServiceName);
        }

        $configResource->patch($validDocumentationFlat);
        $fullDoc = $configResource->fetch(true);
        return $fullDoc[$controllerServiceName];
    }

    /**
     * Check if the module exists
     *
     * @param  string $module
     * @return bool
     */
    public function moduleExists($module)
    {
        try {
            $this->configFactory->factory($module);
        } catch (InvalidArgumentConfiguration $e) {
            return false;
        }
        return true;
    }

    /**
     * Check if a module and controller exists
     *
     * @param  string $module
     * @param  string $controller
     * @return bool
     */
    public function controllerExists($module, $controller)
    {
        try {
            $configModule = $this->configFactory->factory($module);
        } catch (InvalidArgumentConfiguration $e) {
            return false;
        }

        $config = $configModule->fetch(true);

        if (
            isset($config['api-tools-rest'])
            && array_key_exists($controller, $config['api-tools-rest'])
        ) {
            return true;
        }

        if (
            isset($config['api-tools-rpc'])
            && array_key_exists($controller, $config['api-tools-rpc'])
        ) {
            return true;
        }

        return false;
    }

    protected function getDocumentationConfigResource(string $module): ConfigResource
    {
        $moduleConfigPath = $this->moduleUtils->getModuleConfigPath($module);
        $docConfigPath    = dirname($moduleConfigPath) . '/documentation.config.php';
        $docArray         = file_exists($docConfigPath) ? include $docConfigPath : [];
        return $this->configFactory->createConfigResource($docArray, $docConfigPath);
    }
}
