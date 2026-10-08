<?php declare(strict_types=1);

namespace Smc\Guards;

use Smc\User\Session;
use Smc\User\SessionException;

class SessionGuard implements GuardInterface
{
    public static function guard(): void
    {
        if (Session::start() === false) {
            throw new GuardException('Session could not be started', 500);
        }

        try {
            Session::assertSessionIsActive();
        } catch (SessionException $e) {
            throw new GuardException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
