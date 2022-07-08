<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class AuthenticationEvent.
 * 
 * Authentication events are dispatched upon login, logout, and also
 *  when the session is refreshed via cookie hash.
 */
class AuthenticationEvent extends Event
{
}
