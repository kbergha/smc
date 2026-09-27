<?php declare(strict_types=1);

namespace Smc\Controllers;

use Smc\Guards\GuardException;

interface ControllerInterface
{
    /**
     * HTTP methods each action accepts, as action name => methods.
     *
     * Only explicit methods belong here. HEAD is implied by GET, and OPTIONS is
     * answered generically - the router derives both, so listing them by hand
     * would only be duplication waiting to go stale.
     *
     * An action missing from this list accepts nothing, and the router answers
     * 405. That is deliberate: a missing entry means either a new action nobody
     * declared or a route pointing at the wrong name, and both should surface.
     *
     * @return array<string, list<string>>
     */
    public static function allowedMethods(): array;

    /**
     * Run $action, having first run whatever must hold before it may run.
     *
     * This is the only entry point the router uses, so guarding is a property of dispatch(),
     * rather than something each action has to remember.
     *
     * @param  array<string, mixed>|list<mixed> $parameters Passed to the action as named or positional arguments.
     * @throws GuardException When a guard rejects the request.
     */
    public function dispatch(string $action, array $parameters = []): void;
}
