<?php

namespace App\Exception;

use Exception;
use RuntimeException;

class RedirectException extends RuntimeException
{
    private string $url;

    public function __construct(string $url, string $message = '', int $code = 0, ?Exception $error = null)
    {
        $this->url = $url;

        parent::__construct($message, $code, $error);
    }

    public function getUrl()
    {
        return $this->url;
    }
}
