<?php

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Exception;
use Laminas\ApiTools\Admin\InputFilter\Authentication;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ApiTools\MvcAuth\Authentication\HttpAdapter;
use Laminas\ApiTools\MvcAuth\Authentication\OAuth2Adapter;
use Laminas\ApiTools\OAuth2\Adapter\MongoAdapter;
use Laminas\ApiTools\OAuth2\Adapter\PdoAdapter;
use Laminas\ApiTools\Rest\Exception\CreationException;
use MongoClient;
use MongoConnectionException;
use MongoDB\Driver\Exception\InvalidArgumentException as MongoDbInvalidArgumentException;
use PDO;
use PDOException;

class AuthenticationModel
{
    const ADAPTER_HTTP   = HttpAdapter::class;
    const ADAPTER_OAUTH2 = OAuth2Adapter::class;

    /**
     * @var ConfigResource
     */
    protected $globalConfig;

    /**
     * @var ConfigResource
     */
    protected $localConfig;

    /**
     * @var ModuleModel
     */
    protected $modules;

    /**
     * @param ConfigResource $globalConfig
     * @param ConfigResource $localConfig
     * @param ModuleModel $modules
     */
    public function __construct(ConfigResource $globalConfig, ConfigResource $localConfig, ModuleModel $modules)
    {
        $this->globalConfig = $globalConfig;
        $this->localConfig  = $localConfig;
        $this->modules      = $modules;
    }

    /**
     * Create authentication configuration
     *
     * @param array $authenticationConfig
     * @return AuthenticationEntity
     * @throws \Laminas\ApiTools\Rest\Exception\CreationException
     */
    public function create(array $authenticationConfig)
    {
        if ($this->fetch() instanceof AuthenticationEntity) {
            throw new CreationException('Authentication already exists', 409);
        }

        $entity  = $this->createAuthenticationEntityFromConfig($authenticationConfig);

        if ($entity->isOAuth2()) {
            $data = $entity->getArrayCopy();
            $dsnType = isset($data['dsn_type']) ? $data['dsn_type'] : AuthenticationEntity::DSN_PDO;
            $this->validateDsn($data['dsn'], $data['username'], $data['password'], $dsnType);
        }

        $allData = $entity->getArrayCopy();
        unset($allData['type']);
        $global  = $this->removeSensitiveConfig($allData);
        $local   = array_udiff_assoc($allData, $global, sprintf('%s::arrayDiffRecursive', __CLASS__));
        switch (true) {
            case $entity->isBasic():
            case $entity->isDigest():
                $this->patchHttpAuthConfig($entity, $global, $local);
                break;
            case $entity->isOAuth2():
                $this->patchOAuth2Config($entity, $global, $local);
                break;
        }

        return $entity;
    }

    /**
     * Create authentication adapter for version 2
     *
     * Since Laminas API Tools 1.1
     *
     * @param array $adapter
     * @return array
     */
    public function createAuthenticationAdapter(array $adapter)
    {
        $config = $this->localConfig->fetch(true);

        $result = $this->checkAuthenticationAdapterData($adapter);

        if (isset($config['api-tools-mvc-auth']['authentication']['adapters'][$result['name']])) {
            throw new CreationException('Authentication already exists', 409);
        } else {
            $config = $this->globalConfig->fetch(true);
            if (isset($config['api-tools-mvc-auth']['authentication']['adapters'][$result['name']])) {
                throw new CreationException('Authentication already exists', 409);
            }
        }

        if (! $this->saveAuthenticationAdapter($result)) {
            throw new Exception\RuntimeException(
                'Error saving the authentication data in config file',
                500
            );
        }
        return $result;
    }

    /**
     * Update authentication adapter data
     *
     * Since Laminas API Tools 1.1
     *
     * @param string $name
     * @param array $adapter
     * @return array
     */
    public function updateAuthenticationAdapter($name, array $adapter)
    {
        $config = $this->localConfig->fetch(true);
        if (isset($adapter['name'])) {
            $adapter['name'] = $name;
        }

        if (! isset($config['api-tools-mvc-auth']['authentication']['adapters'][$name])) {
            $config = $this->globalConfig->fetch(true);
            if (! isset($config['api-tools-mvc-auth']['authentication']['adapters'][$name])) {
                throw new Exception\RuntimeException(
                    'The authentication adapter specified doesn\'t exist',
                    404
                );
            }
        }

        $result = $this->checkAuthenticationAdapterData($adapter);

        if (! $this->saveAuthenticationAdapter($result)) {
            throw new Exception\RuntimeException(
                'Error saving the authentication data in config file',
                500
            );
        }
        return $result;
    }

    /**
     * Check and return the input data for version 2
     *
     * @param  array $adapter
     * @return array
     * @throws Exception\InvalidArgumentException
     */
    protected function checkAuthenticationAdapterData(array $adapter)
    {
        if (! isset($adapter['type'])) {
            throw new Exception\InvalidArgumentException(
                'Authentication type is missing',
                422
            );
        }
        switch (strtolower($adapter['type'])) {
            case AuthenticationEntity::TYPE_BASIC:
                $filter = new Authentication\BasicInputFilter2();
                break;
            case AuthenticationEntity::TYPE_DIGEST:
                $filter = new Authentication\DigestInputFilter2();
                break;
            case AuthenticationEntity::TYPE_OAUTH2:
                if (! isset($adapter['oauth2_type'])) {
                    throw new Exception\InvalidArgumentException(
                        'OAuth2 type missing',
                        422
                    );
                }
                switch (strtolower($adapter['oauth2_type'])) {
                    case strtolower(AuthenticationEntity::DSN_PDO):
                        $filter = new Authentication\OAuth2PdoInputFilter2();
                        break;
                    case strtolower(AuthenticationEntity::DSN_MONGO):
                        $filter = new Authentication\OAuth2MongoInputFilter2();
                        break;
                    default:
                        throw new Exception\InvalidArgumentException(
                            'Invalid OAuth2 type specified',
                            422
                        );
                }
                break;
            default:
                throw new Exception\InvalidArgumentException(
                    'Authentication type not valid',
                    422
                );
        }
        $filter->init();
        $filter->setData($adapter);

        if (! $filter->isValid()) {
            $msg = $filter->getMessages();
            $field = key($msg);
            throw new Exception\InvalidArgumentException(
                $msg[$field][0],
                422
            );
        }

        $result = $filter->getValues();
        if (AuthenticationEntity::TYPE_OAUTH2 === $result['type']) {
            $username = isset($result['oauth2_username']) ? $result['oauth2_username'] : null;
            $password = isset($result['oauth2_password']) ? $result['oauth2_password'] : null;
            $this->validateDsn($result['oauth2_dsn'], $username, $password, $result['oauth2_type']);
        }
        return $result;
    }

    /**
     * Remove the authentication adapter specified
     *
     * Since Laminas API Tools 1.1
     *
     * @param  string $name
     * @return bool
     */
    public function removeAuthenticationAdapter($name)
    {
        $config = $this->localConfig->fetch(true);
        $key    = 'api-tools-mvc-auth.authentication.adapters.' . $name;

        if (! isset($config['api-tools-mvc-auth']['authentication']['adapters'][$name])) {
            $config = $this->globalConfig->fetch(true);
            if (! isset($config['api-tools-mvc-auth']['authentication']['adapters'][$name])) {
                throw new Exception\RuntimeException(
                    'The authentication adapter specified doesn\'t exist',
                    404
                );
            }
            $this->globalConfig->deleteKey($key);
        } else {
            $this->localConfig->deleteKey($key);
        }

        $adapter = $config['api-tools-mvc-auth']['authentication']['adapters'][$name];
        if (self::ADAPTER_OAUTH2 === $adapter['adapter']) {
            return $this->removeOAuth2Route($adapter['storage']['route']);
        }
        return true;
    }

    /**
     * Update authentication configuration
     *
     * @param  array $authenticationConfig
     * @return AuthenticationEntity
     */
    public function update(array $authenticationConfig)
    {
        $current = $this->fetch();
        if (! $current instanceof AuthenticationEntity) {
            return $this->create($authenticationConfig);
        }

        $current->exchangeArray($authenticationConfig);

        if ($current->isOAuth2()) {
            $data = $current->getArrayCopy();
            $dsnType = isset($data['dsn_type']) ? $data['dsn_type'] : AuthenticationEntity::DSN_PDO;
            $this->validateDsn($data['dsn'], $data['username'], $data['password'], $dsnType);
        }

        $allData = $current->getArrayCopy();
        unset($allData['type']);

        $global  = $this->removeSensitiveConfig($allData);
        $local   = array_udiff_assoc($allData, $global, sprintf('%s::arrayDiffRecursive', __CLASS__));
        switch (true) {
            case $current->isBasic():
            case $current->isDigest():
                $this->patchHttpAuthConfig($current, $global, $local);
                break;
            case $current->isOAuth2():
                $this->patchOAuth2Config($current, $global, $local);
                break;
        }

        return $current;
    }

    /**
     * Remove authentication
     *
     * @return true
     */
    public function remove()
    {
        $configKeys = [
            'api-tools-mvc-auth.authentication.http',
            'api-tools-oauth2.db',
            'api-tools-oauth2.mongo',
            'api-tools-oauth2.storage',
            'router.routes.oauth',
        ];
        foreach ($configKeys as $key) {
            $this->globalConfig->deleteKey($key);
            $this->localConfig->deleteKey($key);
        }
        return true;
    }

    /**
     * Fetch configuration details for authentication
     *
     * @return AuthenticationEntity|false
     */
    public function fetch()
    {
        $config = $this->globalConfig->fetch(true);
        if (isset($config['api-tools-mvc-auth']['authentication']['http'])) {
            $config = $this->fetchHttpAuthConfiguration($config);
        } else {
            $config = $this->fetchOAuth2Configuration($config);
        }

        if (! $config) {
            return false;
        }

        return $this->createAuthenticationEntityFromConfig($config);
    }

    /**
     * Fetch configuration details for specific auth adapter name
     *
     * Used since Laminas API Tools 1.1
     *
     * @param  string $name
     * @return array|false
     */
    public function fetchAuthenticationAdapter($name)
    {
        $config = $this->localConfig->fetch(true);
        if (! isset($config['api-tools-mvc-auth']['authentication']['adapters'][$name])) {
            $config = $this->globalConfig->fetch(true);
            if (! isset($config['api-tools-mvc-auth']['authentication']['adapters'][$name])) {
                return false;
            }
        }
        return $this->loadAuthenticationAdapterFromConfig($name, $config);
    }

    /**
     * Fetch configuration details for auth adapters
     *
     * Used since Laminas API Tools 1.1
     *
     * @return array
     */
    public function fetchAllAuthenticationAdapter()
    {
        $result = [];
        $config = $this->localConfig->fetch(true);

        if (! isset($config['api-tools-mvc-auth']['authentication']['adapters'])) {
            $config = $this->globalConfig->fetch(true);
            if (! isset($config['api-tools-mvc-auth']['authentication']['adapters'])) {
                return $result;
            }
        }

        foreach ($config['api-tools-mvc-auth']['authentication']['adapters'] as $name => $adapter) {
            $result[] = $this->loadAuthenticationAdapterFromConfig($name, $config);
        }
        return $result;
    }

    /**
     * Get the authentication map specified by $module and $version
     *
     * Used since Laminas API Tools 1.1
     *
     * @param string $module
     * @param int|false $version
     * @return string|false
     */
    public function getAuthenticationMap($module, $version = false)
    {
        $name = $module;
        if (false !== $version) {
            $name .= '\V'. (int) $version;
        }

        $config = $this->globalConfig->fetch(true);
        if (! isset($config['api-tools-mvc-auth']['authentication']['map'][$name])) {
            $config = $this->localConfig->fetch(true);
            if (! isset($config['api-tools-mvc-auth']['authentication']['map'][$name])) {
                return false;
            }
        }

        return $config['api-tools-mvc-auth']['authentication']['map'][$name];
    }

    /**
     * Save the authentication Map for a specific $module and $version
     *
     * Used since Laminas API Tools 1.1
     *
     * @param  string $auth
     * @param  string $module
     * @param  int $version
     * @return bool
     * @throws Exception\InvalidArgumentException
     */
    public function saveAuthenticationMap($auth, $module, $version = null)
    {
        $name = $module;
        if (null !== $version) {
            $name .= '\V' . (int) $version;
        }
        $key = 'api-tools-mvc-auth.authentication.map.' . $name;
        $config = $this->localConfig->fetch(true);
        if (! isset($config['api-tools-mvc-auth']['authentication']['adapters'][$auth])) {
            throw new Exception\InvalidArgumentException(
                'The authentication adapter specified doesn\'t exist',
                422
            );
        }
        $this->globalConfig->patchKey($key, $auth);
        $this->localConfig->deleteKey($key);
        return true;
    }

    /**
     * Remove the authentication Map for a specific $module and $version
     *
     * Used since Laminas API Tools 1.1
     *
     * @param  string $module
     * @param  int $version
     * @return bool
     */
    public function removeAuthenticationMap($module, $version = null)
    {
        $name = $module;
        if (null !== $version) {
            $name .= '\V' . (int) $version;
        }
        $key = 'api-tools-mvc-auth.authentication.map.' . $name;
        $this->globalConfig->deleteKey($key);
        $this->localConfig->deleteKey($key);
        return true;
    }

    /**
     * Create an AuthenticationEntity based on the configuration given
     *
     * @param  array $config
     * @return AuthenticationEntity
     */
    protected function createAuthenticationEntityFromConfig(array $config)
    {
        switch (true) {
            case (isset($config['accept_schemes'])):
                $type   = array_shift($config['accept_schemes']);
                $realm  = isset($config['realm']) ? $config['realm'] : 'api';
                return new AuthenticationEntity($type, $realm, $config);
            case (isset($config['dsn']) || isset($config['dsn_type'])):
                return new AuthenticationEntity(AuthenticationEntity::TYPE_OAUTH2, $config);
        }
    }

    /**
     * Remove sensitive information from the configuration
     *
     * Currently only "htpasswd" and "htdigest" entries are stripped.
     *
     * @param  array $config
     * @return array
     */
    protected function removeSensitiveConfig(array $config)
    {
        foreach (array_keys($config) as $key) {
            switch ($key) {
                case 'dsn_type':
                case 'dsn':
                case 'database':
                case 'htdigest':
                case 'htpasswd':
                case 'password':
                case 'username':
                    unset($config[$key]);
                    break;
            }
        }
        return $config;
    }

    /**
     * Perform a recursive array diff
     *
     * Necessary starting in PHP 5.4; see https://bugs.php.net/bug.php?id=60278
     *
     * @param  mixed $a
     * @param  mixed $b
     * @return int
     */
    public static function arrayDiffRecursive($a, $b)
    {
        if (is_array($a) && is_array($b)) {
            return array_diff_uassoc($a, $b, sprintf('%s::arrayDiffRecursive', __CLASS__));
        }
        if ($a === $b) {
            return 0;
        }
        return ($a > $b) ? 1 : -1;
    }

    /**
     * Fetch HTTP Authentication configuration
     *
     * @param array $config
     * @return array|false
     */
    protected function fetchHttpAuthConfiguration(array $config)
    {
        if (! isset($config['api-tools-mvc-auth']['authentication']['http']['accept_schemes'])
            || ! is_array($config['api-tools-mvc-auth']['authentication']['http']['accept_schemes'])
        ) {
            return false;
        }

        $config = $config['api-tools-mvc-auth']['authentication']['http'];

        $localConfig = $this->localConfig->fetch(true);
        if (isset($localConfig['api-tools-mvc-auth']['authentication']['http'])
            && is_array($localConfig['api-tools-mvc-auth']['authentication']['http'])
        ) {
            $config = array_merge($config, $localConfig['api-tools-mvc-auth']['authentication']['http']);
        }

        return $config;
    }

    /**
     * Fetch all OAuth2 configuration from global and local files
     *
     * @param array $config
     * @return array|false
     */
    protected function fetchOAuth2Configuration(array $config)
    {
        $oauth2Config = [
            'route_match' => '/oauth',
        ];

        if (isset($config['router']['routes']['oauth']['options']['route'])) {
            $oauth2Config['route_match'] = $config['router']['routes']['oauth']['options']['route'];
        }

        $localConfig = $this->localConfig->fetch(true);
        if (isset($localConfig['api-tools-oauth2']['db'])
            && is_array($localConfig['api-tools-oauth2']['db'])
        ) {
            return array_merge($oauth2Config, $localConfig['api-tools-oauth2']['db']);
        }

        if (isset($localConfig['api-tools-oauth2']['mongo'])
            && is_array($localConfig['api-tools-oauth2']['mongo'])
        ) {
            return array_merge($oauth2Config, $localConfig['api-tools-oauth2']['mongo']);
        }

        return false;
    }

    /**
     * Patch the HTTP Authentication configuration
     *
     * @param AuthenticationEntity $entity
     * @param array $global
     * @param array $local
     */
    protected function patchHttpAuthConfig(AuthenticationEntity $entity, array $global, array $local)
    {
        $key = 'api-tools-mvc-auth.authentication.http';
        $this->globalConfig->patchKey($key, $global);
        $this->localConfig->patchKey($key, $local);
    }

    /**
     * Patch the OAuth2 configuration
     *
     * @param AuthenticationEntity $entity
     * @param array $global
     * @param array $local
     * @return void
     */
    protected function patchOAuth2Config(AuthenticationEntity $entity, array $global, array $local)
    {
        if (isset($global['route_match']) && $global['route_match']) {
            $this->globalConfig->patchKey('router.routes.oauth.options.route', $global['route_match']);
        }

        switch ($entity->getDsnType()) {
            case AuthenticationEntity::DSN_MONGO:
                $toSet = [
                    'storage' => MongoAdapter::class,
                    'mongo'   => $local,
                ];
                break;
            case AuthenticationEntity::DSN_PDO:
            default:
                $toSet = [
                    'storage' => PdoAdapter::class,
                    'db'      => $local,
                ];
                break;
        }

        $key = 'api-tools-oauth2';
        $this->localConfig->patchKey($key, $toSet);
    }

    /**
     * Validate a DSN
     *
     * @param  string $dsnType
     * @param  string $dsn
     * @param  string $username
     * @param  string $password
     * @throws Exception\InvalidArgumentException on invalid DSN
     * @return bool
     */
    protected function validateDsn($dsn, $username = null, $password = null, $dsnType = AuthenticationEntity::DSN_PDO)
    {
        $method = sprintf('create%sDSN', strtolower($dsnType));

        try {
            $this->$method($dsn, $username, $password);
            return true;
        } catch (MongoConnectionException $mongoException) {
        } catch (PDOException $pdoException) {
        } catch (MongoDbInvalidArgumentException $exception) {
        }

        throw new Exception\InvalidArgumentException(
            sprintf('Invalid DSN "%s" provided', $dsn),
            422
        );
    }

    /**
     * @param  $dsn
     * @return MongoClient
     */
    protected function createMongoDSN($dsn)
    {
        return new MongoClient($dsn);
    }

    /**
     * @param  $dsn
     * @param  $username
     * @param  $password
     * @return PDO
     */
    protected function createPdoDSN($dsn, $username, $password)
    {
        return new PDO($dsn, $username, $password);
    }

    /**
     * Add a new authentication adapter in local config
     *
     * @param array $adapter
     * @return true
     */
    protected function saveAuthenticationAdapter(array $adapter)
    {
        $key = 'api-tools-mvc-auth.authentication.adapters.' . $adapter['name'];
        switch ($adapter['type']) {
            case AuthenticationEntity::TYPE_BASIC:
                $config = [
                    'adapter' => self::ADAPTER_HTTP,
                    'options' => [
                        'accept_schemes' => [AuthenticationEntity::TYPE_BASIC],
                        'realm'          => $adapter['realm'],
                        'htpasswd'       => $adapter['htpasswd'],
                    ],
                ];
                break;
            case AuthenticationEntity::TYPE_DIGEST:
                $config = [
                    'adapter' => self::ADAPTER_HTTP,
                    'options' => [
                        'accept_schemes' => [AuthenticationEntity::TYPE_DIGEST],
                        'realm'          => $adapter['realm'],
                        'digest_domains' => $adapter['digest_domains'],
                        'nonce_timeout'  => $adapter['nonce_timeout'],
                        'htdigest'       => $adapter['htdigest'],
                    ],
                ];
                break;
            case AuthenticationEntity::TYPE_OAUTH2:
                switch (strtolower($adapter['oauth2_type'])) {
                    case strtolower(AuthenticationEntity::DSN_PDO):
                        $config = [
                            'adapter' => self::ADAPTER_OAUTH2,
                            'storage' => [
                                'adapter' => strtolower(AuthenticationEntity::DSN_PDO),
                                'dsn'     => $adapter['oauth2_dsn'],
                                'route'   => $adapter['oauth2_route'],
                            ],
                        ];
                        if (isset($adapter['oauth2_username'])) {
                            $config['storage']['username'] = $adapter['oauth2_username'];
                        }
                        if (isset($adapter['oauth2_password'])) {
                            $config['storage']['password'] = $adapter['oauth2_password'];
                        }
                        break;
                    case strtolower(AuthenticationEntity::DSN_MONGO):
                        $config = [
                            'adapter' => self::ADAPTER_OAUTH2,
                            'storage' => [
                                'adapter'  => strtolower(AuthenticationEntity::DSN_MONGO),
                                'dsn'      => $adapter['oauth2_dsn'],
                                'database' => $adapter['oauth2_database'],
                                'route'    => $adapter['oauth2_route'],
                            ],
                        ];
                        if (isset($adapter['oauth2_locator_name'])) {
                            $config['storage']['locator_name'] = $adapter['oauth2_locator_name'];
                        }
                        break;
                }
                if (isset($adapter['oauth2_options'])) {
                    $config['storage']['options'] = $adapter['oauth2_options'];
                }
                $this->updateOAuth2Route($adapter['oauth2_route']);
                break;
        }

        $this->localConfig->patchKey($key, $config);
        $this->globalConfig->deleteKey($key);
        return true;
    }

    /**
     * Return the OAuth2 urls as array from the regex string
     *
     * Since Laminas API Tools 1.1
     *
     * @param  array $config
     * @return array
     */
    public function fromOAuth2RegexToArray($config)
    {
        if (! isset($config['router']['routes']['oauth']['options']['regex'])) {
            return [];
        }
        $regex = $config['router']['routes']['oauth']['options']['regex'];
        return explode('|', substr($regex, 11, strlen($regex) - 13));
    }

    /**
     * Update the OAuth2 route
     *
     * Since Laminas API Tools 1.1
     *
     * @param  string $url
     * @return void
     */
    protected function updateOAuth2Route($url)
    {
        $config = $this->globalConfig->fetch(true);

        $routes = $this->fromOAuth2RegexToArray($config);
        if (! in_array($url, $routes)) {
            $routes[] = $url;
        }

        usort($routes, function ($a, $b) {
            return strlen($b) - strlen($a);
        });

        $options = [
            'spec'  => '%oauth%',
            'regex' => '(?P<oauth>(' . implode('|', $routes) . '))',
        ];
        $this->globalConfig->patchKey('router.routes.oauth.options', $options);
        $this->globalConfig->patchKey('router.routes.oauth.type', 'regex');
    }

    /**
     * Remove a url from OAuth2 route
     *
     * Since Laminas API Tools 1.1
     *
     * @param  string $url
     * @return bool
     */
    protected function removeOAuth2Route($url)
    {
        $config = $this->globalConfig->fetch(true);

        if (! isset($config['router']['routes']['oauth']['options']['regex'])) {
            return false;
        }

        $routes = $this->fromOAuth2RegexToArray($config);
        $index = array_search($url, $routes);
        if (false === $index) {
            return false;
        }

        unset($routes[$index]);

        if (count($routes) > 0) {
            usort($routes, function ($a, $b) {
                return strlen($b) - strlen($a);
            });
            $options = [
                'spec'  => '%oauth%',
                'regex' => '(?P<oauth>(' . implode('|', $routes) . '))',
            ];
            $this->globalConfig->patchKey('router.routes.oauth.options', $options);
            $this->globalConfig->patchKey('router.routes.oauth.type', 'regex');
            return true;
        }

        $this->globalConfig->deleteKey('router.routes.oauth');
        return true;
    }

    /**
     * Load authentication data from configuration version 2
     * Since Laminas API Tools 1.1
     *
     * @param  string $name
     * @param  array $config
     * @return array
     */
    protected function loadAuthenticationAdapterFromConfig($name, array $config)
    {
        $result = [];
        if (isset($config['api-tools-mvc-auth']['authentication']['adapters'][$name])) {
            $adapter = $config['api-tools-mvc-auth']['authentication']['adapters'][$name];
            $result['name'] = $name;
            switch ($adapter['adapter']) {
                case self::ADAPTER_HTTP:
                    $result['type'] = array_shift($adapter['options']['accept_schemes']);
                    switch ($result['type']) {
                        case AuthenticationEntity::TYPE_BASIC:
                            $result['realm']    = $adapter['options']['realm'];
                            $result['htpasswd'] = $adapter['options']['htpasswd'];
                            break;
                        case AuthenticationEntity::TYPE_DIGEST:
                            $result['realm']          = $adapter['options']['realm'];
                            $result['digest_domains'] = $adapter['options']['digest_domains'];
                            $result['nonce_timeout']  = $adapter['options']['nonce_timeout'];
                            $result['htdigest']       = $adapter['options']['htdigest'];
                            break;
                    }
                    break;
                case self::ADAPTER_OAUTH2:
                    $result['type'] = 'oauth2';
                    $result['oauth2_type'] = isset($adapter['storage']['adapter'])
                        ? $adapter['storage']['adapter']
                        : null;
                    $result['oauth2_dsn'] = isset($adapter['storage']['dsn'])
                        ? $adapter['storage']['dsn']
                        : null;
                    $result['oauth2_route'] = isset($adapter['storage']['route'])
                        ? $adapter['storage']['route']
                        : null;
                    if (isset($adapter['storage']['options'])) {
                        $result['oauth2_options'] = $adapter['storage']['options'];
                    }
                    switch (strtolower($result['oauth2_type'])) {
                        case strtolower(AuthenticationEntity::DSN_PDO):
                            $result['oauth2_username'] = isset($adapter['storage']['username'])
                                ? $adapter['storage']['username']
                                : null;
                            $result['oauth2_password'] = isset($adapter['storage']['password'])
                                ? $adapter['storage']['password']
                                : null;
                            break;
                        case strtolower(AuthenticationEntity::DSN_MONGO):
                            $result['oauth2_database'] = $adapter['storage']['database'];
                            if (isset($adapter['storage']['locator_name'])) {
                                $result['oauth2_locator_name'] = $adapter['storage']['locator_name'];
                            }
                            break;
                        default:
                            $result['oauth2_type'] = 'custom';
                    }
                    break;
                default:
                    $result['type'] = 'custom';
                    $result['route'] = isset($adapter['storage']['route']) ? $adapter['storage']['route'] : null;
            }
        }
        return $result;
    }

    /**
     * Remove authentication
     *
     * @return true
     */
    public function removeOldAuthentication()
    {
        $configKeys = [
            'api-tools-mvc-auth.authentication.http',
            'api-tools-oauth2.db',
            'api-tools-oauth2.mongo',
            'api-tools-oauth2.storage',
        ];
        foreach ($configKeys as $key) {
            $this->globalConfig->deleteKey($key);
            $this->localConfig->deleteKey($key);
        }
        return true;
    }

    /**
     * This function transform the old authentication system to the new one
     * based on APIs defined. It reads the old configuration and generates an
     * authentication mapping for each API and version.
     *
     * @return bool|string Boolean false if nothing was performed; string
     *     adapter name otherwise.
     */
    public function transformAuthPerApis()
    {
        $oldAuth = $this->fetch();

        if (! $oldAuth) {
            return false;
        }

        $oldAuth = $oldAuth->getArrayCopy();
        switch ($oldAuth['type']) {
            case 'http_basic':
                $adapter = [
                    'name'     => 'http_basic',
                    'type'     => AuthenticationEntity::TYPE_BASIC,
                    'realm'    => $oldAuth['realm'],
                    'htpasswd' => $oldAuth['htpasswd'],
                ];
                break;
            case 'http_digest':
                $adapter = [
                    'name'           => 'http_digest',
                    'type'           => AuthenticationEntity::TYPE_DIGEST,
                    'realm'          => $oldAuth['realm'],
                    'htdigest'       => $oldAuth['htdigest'],
                    'digest_domains' => $oldAuth['digest_domains'],
                    'nonce_timeout'  => $oldAuth['nonce_timeout'],
                ];
                break;
            case AuthenticationEntity::TYPE_OAUTH2:
                $adapter = [
                    'type'         => AuthenticationEntity::TYPE_OAUTH2,
                    'oauth2_type'  => $oldAuth['dsn_type'],
                    'oauth2_dsn'   => $oldAuth['dsn'],
                    'oauth2_route' => $oldAuth['route_match'],
                ];
                switch (strtolower($oldAuth['dsn_type'])) {
                    case strtolower(AuthenticationEntity::DSN_PDO):
                        $adapter['name']            = 'oauth2_pdo';
                        $adapter['oauth2_username'] = $oldAuth['username'];
                        $adapter['oauth2_password'] = $oldAuth['password'];
                        break;
                    case strtolower(AuthenticationEntity::DSN_MONGO):
                        $adapter['name']            = 'oauth2_mongo';
                        $adapter['oauth2_database'] = $oldAuth['database'];
                        break;
                }
                break;
        }

        // Save the authentication adapter
        $this->saveAuthenticationAdapter($adapter);

        // Create the authentication map for each API
        $modules = $this->modules->getModules();
        foreach ($modules as $module) {
            foreach ($module->getVersions() as $version) {
                $this->saveAuthenticationMap($adapter['name'], $module->getName(), $version);
            }
        }

        // Remove the old configuration
        $this->removeOldAuthentication();

        return $adapter['name'];
    }
}
