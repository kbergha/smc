<?php declare(strict_types=1);

namespace Smc\Controllers;

interface ControllerInterface
{
    /**
     * Routes this controller answers to, as HTTP method => path => action.
     *
     * The action is the name of a public, non-static method on this class.
     * Paths are normalised by the router, so '/all', 'all' and '/all/' are equivalent.
     *
     * @return array<string, array<string, string>>
     */
    public static function routes(): array;
}
