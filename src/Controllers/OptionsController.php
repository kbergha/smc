<?php declare(strict_types=1);

namespace Smc\Controllers;

class OptionsController implements ControllerInterface
{
    /**
     * Never reached through a route - the router dispatches this directly for
     * any OPTIONS request - so this list stays empty.
     */
    public static function allowedMethods(): array
    {
        return [];
    }

    /**
     * @param list<string> $allowed Methods the resolved action accepts.
     *
     * @noinspection PhpUnused
     */
    public function show(array $allowed): void
    {
        // 204 No Content: the Allow header is the entire response, so nothing
        // may be echoed here.
        http_response_code(204);

        if ($allowed !== []) {
            header('Allow: ' . implode(', ', $allowed));
        }
    }
}
