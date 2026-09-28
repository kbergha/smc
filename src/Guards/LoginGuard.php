<?php declare(strict_types=1);

namespace Smc\Guards;

use Smc\User\User;

class LoginGuard implements GuardInterface
{
    public static function guard(): void
    {
        $user = new User();

        // todo: period or config
        // todo: own exception that does the redirect?
        if (!$user->isLoggedIn() || $user->loggedInForSeconds() >= 86400) {
            $user->logout();
            // todo: Move to some Header-class?
            http_response_code(302);
            header('Location: /');
            exit;
        }
    }
}
