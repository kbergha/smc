<?php declare(strict_types=1);

namespace Smc\Controllers;

use Smc\Router\Header;

class ErrorController extends Controller
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
     * @param list<string>|null $allowed Methods the resource accepts. Only a 405 has one.
     *
     * @noinspection PhpUnused
     */
    public function show(int $code, string $path, ?string $message, ?array $allowed = null): void
    {
        Header::setStatusCode($code);

        // RFC 9110 requires a 405 to advertise the methods that are supported.
        // Skipped when empty, since "Allow:" with no value is not a valid header.
        if ($allowed !== null && $allowed !== []) {
            Header::setHeader('Allow', implode(', ', $allowed));
        }

        echo "Sorry, something went wrong! {$code} - {$message}";
    }
}
