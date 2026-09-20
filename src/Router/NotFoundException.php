<?php declare(strict_types=1);

namespace Smc\Router;

use RuntimeException;

final class NotFoundException extends RuntimeException
{
    public function __construct(public readonly string $path)
    {
        parent::__construct("No route for {$path}");
    }
}
