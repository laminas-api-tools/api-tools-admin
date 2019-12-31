<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class AppController extends AbstractActionController
{
    public function appAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate('api-tools-ui');
        $viewModel->setTerminal(true);
        return $viewModel;
    }
}
