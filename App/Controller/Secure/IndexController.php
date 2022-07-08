<?php

namespace App\Controller\Secure;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends BaseController
{

    public function getControllerModule() : string
    {
        return 'secure';
    }

    public function indexAction() : Response
    {
        exit('you are logged in');
    }
}
