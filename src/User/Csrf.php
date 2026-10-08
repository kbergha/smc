<?php declare(strict_types=1);

namespace Smc\User;

final class Csrf
{
    private const string TOKEN_FORM_NAME = 'csrf_token';
    private const string TOKEN_SESSION_NAME = 'CSRF';

    /** Bytes of entropy. The token is hex, so twice this many characters. */
    private const int TOKEN_LENGTH = 32;

    public static function getTokenName(): string
    {
        return self::TOKEN_FORM_NAME;
    }

    public static function generateToken(): string
    {
        Session::assertSessionIsActive();

        $token = self::createToken();
        Session::setVariable(self::TOKEN_SESSION_NAME, $token);

        return $token;
    }

    public static function validateToken(string $token): bool
    {
        Session::assertSessionIsActive();

        $tokenFromSession = Session::getVariable(self::TOKEN_SESSION_NAME);
        if (!is_string($tokenFromSession) || $tokenFromSession === '') {
            return false;
        }

        if (hash_equals($tokenFromSession, $token) === true) {
            Session::unsetVariable(self::TOKEN_SESSION_NAME);
            return true;
        }

        // Failsafe
        return false;
    }

    public static function getTokenFromRequest(): ?string
    {
        $token = $_POST[self::getTokenName()] ?? null;
        return is_string($token) ? $token : null;
    }

    private static function createToken(): string
    {
        return bin2hex(random_bytes(self::TOKEN_LENGTH));
    }

}
