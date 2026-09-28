<?php declare(strict_types=1);

namespace Smc\User;

class Session
{
    /**
     * Start session. Yes.
     *
     * @return bool
     */
    public static function start(): bool
    {
        session_cache_limiter('nocache');

        return session_start([
            'cookie_lifetime' => 86400, // 1 day
            'cookie_httponly' => true,
            'cookie_secure' => true,
            'cookie_samesite' => 'Strict',
            'use_strict_mode' => true,
            'name' => 'SMC',
        ]);
    }

    public static function regenerateId(bool $deleteOldSession = false): bool
    {
        return session_regenerate_id($deleteOldSession);
    }

    public static function getVariable(string $name): mixed
    {
        return $_SESSION[$name] ?? null;
    }

    public static function setVariable(string $name, mixed $value): void
    {
        $_SESSION[$name] = $value;
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}
