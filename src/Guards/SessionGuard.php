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
            'name' => "SMC"
        ]);

        // Fake it. We only care if it failes / is false
        $sessionRegenerated = true;

        // Regenerate session id 10% of the time.
        if (random_int(0, 99) <= 9) {
            $sessionRegenerated = session_regenerate_id(true);
        }

        if ($sessionStarted === false) {
            throw new GuardException('Session could not be started', 500);
        }

        if ($sessionRegenerated === false) {
            throw new GuardException('Session could not be regenerated', 500);
        }
    }
}
