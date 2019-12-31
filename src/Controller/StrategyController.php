<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\View\ApiProblemModel;
use Laminas\Hydrator\Strategy\StrategyInterface;
use Laminas\Mvc\Controller\AbstractActionController;

class StrategyController extends AbstractActionController
{
    /**
     * @param ContainerInterface
     */
    protected $serviceLocator;

    /**
     * @param ContainerInterface $serviceLocator
     */
    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @return ContainerInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function existsAction()
    {
        $container = $this->getServiceLocator();
        $strategyName = $this->params()->fromRoute('strategy_name', false);

        if (! $container->has($strategyName)) {
            return new ApiProblemModel(new ApiProblem(422, 'This service was not found in the service manager'));
        }

        if (! $container->get($strategyName) instanceof StrategyInterface) {
            return new ApiProblemModel(new ApiProblem(422, 'This service does not implement StrategyInterface'));
        }

        return ['exists' => true];
    }
}
