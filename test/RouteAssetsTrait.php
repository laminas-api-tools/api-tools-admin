<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin;

use Laminas\Mvc\Router\Http\TreeRouteStack as V2TreeRouteStack;
use Laminas\Mvc\Router\RouteMatch as V2RouteMatch;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\Router\RouteMatch;

trait RouteAssetsTrait
{
    /**
     * @param array $params
     * @return RouteMatch|V2RouteMatch
     */
    public function createRouteMatch(array $params = [])
    {
        $class = $this->getRouteMatchClass();
        return new $class($params);
    }

    /**
     * @param string Name of route match class currently available.
     */
    public function getRouteMatchClass()
    {
        return class_exists(V2RouteMatch::class) ? V2RouteMatch::class : RouteMatch::class;
    }

    public function createRouter(array $config = [])
    {
        $class = class_exists(V2TreeRouteStack::class) ? V2TreeRouteStack::class : TreeRouteStack::class;
        $config['routes']['api-tools']['type'] = 'literal';
        $config['routes']['api-tools']['options'] = ['route' => '/api-tools'];
        return $class::factory($config);
    }

    /**
     * @param RouteMatch|V2RouteMatch|null
     * @return bool
     */
    public function isRouteMatch($routeMatch)
    {
        return ($routeMatch instanceof RouteMatch || $routeMatch instanceof V2RouteMatch);
    }
}
