<?php declare(strict_types=1);

namespace Smc\Guards;

final class GuardRunner
{
    /**
     * Runs every guard that applies to $action, wildcard guards first.
     * A guard named twice runs once, array_unique keeps the first occurrence
     *
     * @param  array<string, list<class-string<GuardInterface>>> $guardMap
     * @throws GuardException
     */
    public static function run(string $action, array $guardMap): void
    {
        $guardsToRun = array_values(array_unique([
            // A key may be one guard or a list of them.
            // Casting to: (array) makes the single string a one-element list, and leaves a list alone.
            ...(array) ($guardMap['*'] ?? []),
            ...(array) ($guardMap[$action] ?? []),
        ]));

        foreach ($guardsToRun as $guardClass) {
            $guardClass::guard();
        }
    }
}
