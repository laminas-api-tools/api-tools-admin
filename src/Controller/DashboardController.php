<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\Admin\Model\AuthenticationEntity;
use Laminas\ApiTools\Admin\Model\AuthenticationModel;
use Laminas\ApiTools\Admin\Model\ContentNegotiationModel;
use Laminas\ApiTools\Admin\Model\DbAdapterModel;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ApiTools\Admin\Model\RestServiceModelFactory;
use Laminas\ApiTools\Admin\Model\RpcServiceModelFactory;
use Laminas\ApiTools\ContentNegotiation\ViewModel;
use Laminas\ApiTools\Hal\Collection;
use Laminas\ApiTools\Hal\Entity;
use Laminas\ApiTools\Hal\Link\Link;
use Laminas\Mvc\Controller\AbstractActionController;

class DashboardController extends AbstractActionController
{
    protected $authentication;

    protected $contentNegotiation;

    protected $dbAdapters;

    protected $doctrineAdapters;

    protected $modules;

    protected $restServicesFactory;

    protected $rpcServicesFactory;

    public function __construct(
        AuthenticationModel $authentication,
        ContentNegotiationModel $contentNegotiation,
        DbAdapterModel $dbAdapters,
        ModuleModel $modules,
        RestServiceModelFactory $restServicesFactory,
        RpcServiceModelFactory $rpcServicesFactory
    ) {
        $this->authentication      = $authentication;
        $this->contentNegotiation  = $contentNegotiation;
        $this->dbAdapters          = $dbAdapters;
        $this->modules             = $modules;
        $this->restServicesFactory = $restServicesFactory;
        $this->rpcServicesFactory  = $rpcServicesFactory;
    }

    public function dashboardAction()
    {
        $dbAdapters = new Collection($this->dbAdapters->fetchAll());
        $dbAdapters->setCollectionRoute('api-tools/api/db-adapter');

        $modules = $this->modules->getModules();
        $map     = function ($value) {
            return $value->serviceName;
        };
        foreach ($modules as $module) {
            $name    = $module->getName();
            $version = $module->getLatestVersion();

            $rest = $this->restServicesFactory->factory($name)->fetchAll($version);
            $rest = array_map($map, $rest);
            sort($rest);

            $rpc = $this->rpcServicesFactory->factory($name)->fetchAll($version);
            $rpc = array_map($map, $rpc);
            sort($rpc);

            $module->exchangeArray([
                'rest' => $rest,
                'rpc'  => $rpc,
            ]);
        }

        $modulesCollection = new Collection($modules);
        $modulesCollection->setCollectionRoute('api-tools/api/module');

        $dashboard = [
            'db_adapter' => $dbAdapters,
            'module'     => $modulesCollection,
        ];

        $entity = new Entity($dashboard, 'dashboard');
        $links  = $entity->getLinks();
        $links->add(Link::factory([
            'rel' => 'self',
            'route' => [
                'name' => 'api-tools/api/dashboard',
            ],
        ]));

        return new ViewModel(['payload' => $entity]);
    }

    public function settingsDashboardAction()
    {
        $authentication = $this->authentication->fetch();
        if ($authentication) {
            $authenticationEntity = $authentication;
            $authentication = new Entity($authentication, null);
            $authentication->getLinks()->add(Link::factory([
                'rel' => 'self',
                'route' => $this->getRouteForEntity($authenticationEntity),
            ]));
        }

        $dbAdapters = new Collection($this->dbAdapters->fetchAll());
        $dbAdapters->setCollectionRoute('api-tools/api/db-adapter');

        $contentNegotiation = new Collection($this->contentNegotiation->fetchAll());
        $contentNegotiation->setCollectionRoute('api-tools/api/content-negotiation');

        $dashboard = [
            'authentication'      => $authentication,
            'content_negotiation' => $contentNegotiation,
            'db_adapter'          => $dbAdapters,
        ];

        $entity = new Entity($dashboard, 'settings-dashboard');
        $links  = $entity->getLinks();
        $links->add(Link::factory([
            'rel' => 'self',
            'route' => [
                'name' => 'api-tools/api/settings-dashboard',
            ],
        ]));

        return new ViewModel(['payload' => $entity]);
    }

    /**
     * Determine the route to use for a given entity
     *
     * Copied from AuthenticationController
     *
     * @param  AuthenticationEntity $entity
     * @return string
     */
    protected function getRouteForEntity(AuthenticationEntity $entity)
    {
        $baseRoute = 'api-tools/api/authentication';

        if ($entity->isBasic()) {
            return $baseRoute . '/http-basic';
        }

        if ($entity->isDigest()) {
            return $baseRoute . '/http-digest';
        }

        if ($entity->isOAuth2()) {
            return $baseRoute . '/oauth2';
        }

        return $baseRoute;
    }
}
