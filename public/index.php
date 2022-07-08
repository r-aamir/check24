<?php

use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/vendor/autoload.php';

error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED);

(new Src\Kernel('dev', true))
    ->handle(Request::createFromGlobals())
    ->send();
