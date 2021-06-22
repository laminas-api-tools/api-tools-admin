<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class AppController extends AbstractActionController
{
    /** @return ViewModel */
    public function appAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate('api-tools-ui');
        $viewModel->setTerminal(true);
        return $viewModel;
    }
}
