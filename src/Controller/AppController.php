<?php

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
