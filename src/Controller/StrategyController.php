<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\View\ApiProblemModel;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Stdlib\Hydrator\Strategy\StrategyInterface;

class StrategyController extends AbstractActionController
{
    public function existsAction()
    {
        $strategy_name = $this->params()->fromRoute('strategy_name', false);
        if ($this->getServiceLocator()->has($strategy_name)) {
            if ($this->getServiceLocator()->get($strategy_name) instanceof StrategyInterface) {
                return ['exists' => true];
            } else {
                return new ApiProblemModel(new ApiProblem(422, 'This service does not implement StrategyInterface'));
            }
        } else {
            return new ApiProblemModel(new ApiProblem(422, 'This service was not found in the service manager'));
        }
    }
}
