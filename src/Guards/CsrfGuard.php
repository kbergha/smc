<?php declare(strict_types=1);

namespace Smc\Guards;

use Smc\User\Csrf;
use Smc\User\SessionException;

class CsrfGuard implements GuardInterface
{
    public static function guard(): void
    {
        // https://blog.ircmaxell.com/2013/02/preventing-csrf-attacks.html
        // https://github.com/slimphp/Slim-Csrf/blob/1.x/src/Guard.php#L215

        $csrfToken = Csrf::getTokenFromRequest();

        if (empty($csrfToken)) {
            throw new GuardException('CSRF token is empty', 403);
        }

        try {
            if (Csrf::validateToken($csrfToken) === false) {
                throw new GuardException('CSRF token invalid', 403);
            }
        } catch (SessionException $e) {
            throw new GuardException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
