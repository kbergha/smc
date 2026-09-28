<?php declare(strict_types=1);

namespace Smc\Guards;

use Smc\User\Session;

class SessionGuard implements GuardInterface
{
    public static function guard(): void
    {
        if (Session::start() === false) {
            throw new GuardException('Session could not be started', 500);
        }
    }
}
