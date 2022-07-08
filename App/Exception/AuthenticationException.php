<?php

namespace App\Exception;

use Exception;

class AuthenticationException extends Exception
{
    private string $loginPath;

    public function __construct(string $message = '', string $loginPath = 'login', int $code = 0)
    {
        $this->loginPath = $loginPath;
        parent::__construct($message, $code);
    }

    /**
     * @return string The login URI
     */
    public function getLoginPath() : string
    {
        return $this->loginPath;
    }
}
