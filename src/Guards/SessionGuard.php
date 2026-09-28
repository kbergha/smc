<?php declare(strict_types=1);

namespace Smc\Guards;

class SessionGuard implements GuardInterface
{
    public static function guard(): void
    {
        // Todo: move stuff to session class.
        session_cache_limiter('nocache');

        $sessionStarted = session_start([
            'cookie_lifetime' => 86400, // 1 day
            'cookie_httponly' => true,
            'cookie_secure' => true,
            'cookie_samesite' => 'Strict',
            'name' => 'SMC',
        ]);

        if ($sessionStarted === false) {
            throw new GuardException('Session could not be started', 500);
        }

        // Todo: sjekke denne også?
        // session_status() !== PHP_SESSION_ACTIVE

    }
}
