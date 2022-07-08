<?php

namespace App\Controller;

interface ControllerInterface
{
    /**
     * A module name, such as secure, guest, auth,
     *  might be useful for multi-domain apps etc...
     *
     * @return string The module name
     */
    public function getControllerModule() : string;
}
