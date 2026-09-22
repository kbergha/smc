<?php declare(strict_types=1);

namespace Smc\Controllers;

class ErrorController implements ControllerInterface
{
    /**
     * Never reached through a route - the router dispatches this directly when
     * another controller fails - so it is exempt from the method check and this
     * list stays empty.
     */
    public static function allowedMethods(): array
    {
        return [];
    }

    /**
     * @noinspection PhpUnused
     */
    /**
     * @param list<string>|null $allowed Methods the resource accepts. Only a 405 has one.
     */
    public function show(int $code, string $path, ?string $message, ?array $allowed = null): void
    {
        // Move to some Header-class?
        http_response_code($code);

        // RFC 9110 requires a 405 to advertise the methods that are supported.
        // Skipped when empty, since "Allow:" with no value is not a valid header.
        if ($allowed !== null && $allowed !== []) {
            header('Allow: ' . implode(', ', $allowed));
        }

        echo "Sorry, something went wrong! {$code} - {$message}";
    }
}
