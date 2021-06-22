<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\View\ApiProblemModel;
use Laminas\Hydrator\Strategy\StrategyInterface;
use Laminas\Mvc\Controller\AbstractActionController;

class StrategyController extends AbstractActionController
{
    /** @var ContainerInterface */
    protected $serviceLocator;

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

    /** @return ApiProblemModel|array<string, bool> */
    public function existsAction()
    {
        $container    = $this->getServiceLocator();
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
