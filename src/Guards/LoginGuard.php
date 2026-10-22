<?php declare(strict_types=1);

namespace Smc\Guards;

use Smc\Router\RedirectException;
use Smc\User\Session;
use Smc\User\User;

class LoginGuard implements GuardInterface
{
    public static function guard(): void
    {
        $user = new User();

        // todo: period or config
        if (!$user->isLoggedIn() || $user->loggedInForSeconds() >= 86400) {
            $user->logout();
            Session::setFlashMessage('Logged out due to inactivity / timeout');
            throw new RedirectException('/');
        }
    }
}
