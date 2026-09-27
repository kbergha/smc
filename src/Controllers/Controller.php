<?php declare(strict_types=1);

namespace Smc\Controllers;

use Smc\Guards\GuardInterface;
use Smc\Guards\GuardRunner;

/**
 * Shared dispatch() for every controller.
 *
 * Guards run here rather than inside the actions themselves.
 */
abstract class Controller implements ControllerInterface
{
    /**
     * Guards that must pass before an action may run, as action name => guards,
     * where '*' means every action and runs first.
     * Each value is one guard class or a list of them.
     *
     * @return array<string, class-string<GuardInterface>|list<class-string<GuardInterface>>>
     */
    public static function requiredGuards(): array
    {
        return [];
    }

    /**
     * Run guards first, then call action with parameters.
     */
    final public function dispatch(string $action, array $parameters = []): void
    {
        GuardRunner::run($action, static::requiredGuards());
        $this->{$action}(...$parameters);
    }
}
