<?php declare(strict_types=1);

namespace Smc\Http;

final class ErrorPage
{
    /**
     * Anything originating from the request is attacker-controlled and must go
     * through here before it reaches the response body.
     *
     * ENT_SUBSTITUTE matters: a request can contain invalid UTF-8, which would
     * otherwise make htmlspecialchars() return an empty string and blank the page.
     */
    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * @param string $detail Already-escaped HTML, or an empty string.
     */
    public static function render(int $status, string $title, string $detail = ''): string
    {
        $title = self::escape($title);

        return <<<HTML
            <!doctype html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>{$status} - {$title}</title>
            </head>
            <body>
                <h1>{$status} - {$title}</h1>
                {$detail}
                <p><a href="/">Back to the front page</a></p>
            </body>
            </html>
            HTML;
    }
}
