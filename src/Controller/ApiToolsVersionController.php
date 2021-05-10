<?php

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

class ApiToolsVersionController extends AbstractActionController
{
    public function indexAction()
    {
        return new JsonModel([
            'version' => defined('ApiTools\VERSION') ? \ApiTools\VERSION : '@dev',
        ]);
    }
}
