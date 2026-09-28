<?php declare(strict_types=1);

namespace Smc\Router;

class Header
{
    public static function setHeader(string $name, string $value, bool $replaceExisting = true): void
    {
        header($name.': '.trim($value), $replaceExisting);
    }

    public static function setStatusCode(int $statusCode): void
    {
        http_response_code($statusCode);
    }

    public static function setNoCache(): void
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    }
}
